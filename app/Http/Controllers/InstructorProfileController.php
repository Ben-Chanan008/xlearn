<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInstructorProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstructorProfileController extends Controller
{
    public function show(Request $request, User $user)
    {
        // Only instructors have a profile page
        abort_unless($user->isInstructor(), 403);

        $user->load('instructorProfile');

        $courses = $user->courses()->published()->withCount('students')->latest()->get();

        // [5 => 80, 4 => 30, ...]; stars nobody gave are missing
        $starCounts = $user->reviewsReceived()->pluck('stars')->countBy();

        // ?reviews=2 rather than ?page=2, since only the reviews are paginated; "Show more" fetches the next one
        $reviews = $user->reviewsReceived()->with(['student', 'course'])->latest()->paginate(5, pageName: 'reviews');

        $stats = [
            'courses' => $courses->count(),
            'students' => $courses->sum('students_count'),
            'reviews' => $starCounts->sum(),
            // Average of the stars alone; $user->rating also mixes in reply times
            'average_stars' => $starCounts->sum() ? $starCounts->map(fn ($count, $stars) => $count * $stars)->sum() / $starCounts->sum() : null,
        ];

        // Same check as UpdateInstructorProfileRequest::authorize(), for showing the edit controls
        $canEdit = $request->user()->is($user);

        return view('instructor.profile', compact('user', 'courses', 'starCounts', 'reviews', 'stats', 'canEdit'));
    }

    public function update(UpdateInstructorProfileRequest $request, User $user)
    {
        // The page always sends every field, so a missing one means it was cleared
        $profile = $user->instructorProfile()->updateOrCreate([], [
            'headline' => $request->validated('headline'),
            'bio' => $request->validated('bio'),
            'expertise' => $request->validated('expertise') ?: null,
            'years_experience' => $request->validated('years_experience'),
            'website' => $request->validated('website'),
            'linkedin' => $request->validated('linkedin'),
        ]);

        if ($request->hasFile('avatar')) {
            $oldAvatar = $user->avatar;
            $user->update(['avatar' => $request->file('avatar')->store('avatars', 'public')]);

            if ($oldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }

        return response()->json([
            'message' => 'Profile saved',
            'profile' => $profile->only(['headline', 'bio', 'expertise', 'years_experience', 'website', 'linkedin']),
            'avatar_url' => $user->avatarUrl(),
            'completion' => $user->fresh()->profileCompletion(),
        ]);
    }

    public function deleteProfile(Request $request, User $user)
    {
        abort_unless($request->user()->is($user), 403);

        $user->delete();

        return response()->json([
            'message' => 'Profile deleted',
            'redirect_url' => route('home'),
        ]);
    }
}