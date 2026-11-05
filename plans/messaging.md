# Messaging and instructor ratings

## Context

Today a student can only **reply** to a message the instructor already sent. The student page ([MessageController.php:14](app/Http/Controllers/MessageController.php#L14)) loads only messages *to* the student, the latest 5, `active` only, so students never see what they sent. There's also no record of how fast an instructor replies.

Goals:
- A student can start a conversation with their course's instructor, not just reply.
- A student sees the whole conversation, both sides, including older messages.
- Instructors get a rating, and not replying within 24 hours brings it down. The rubric is below.

Each step ships on its own and gets its own commit. Auth stays in `StoreMessageRequest`.

---

## Open questions

Each has a recommendation. Steps that depend on one say so.

| # | Question | Recommendation | Affects |
|---|----------|----------------|---------|
| 1 | Who can a student message? | Only the instructor of a course they're enrolled in, inside that course. No student-to-student messages. Today `StoreMessageRequest` allows them, because it only checks that both people belong to the course. | 1 |
| 2 | `CheckInactiveMessages` marks messages `inactive` after 3 weeks, and every page hides them. Should past messages still show? | The conversation shows every message whatever its `status`. "Past replies" should mean all of them. `status` keeps its other uses (unread counts). | 2 |
| 3 | When does the reply clock start and stop? | It starts at the student's **first unanswered** message in the conversation. Further messages before a reply don't start new clocks, so three messages in a row count as one wait, not three late replies. It stops at the instructor's next message to that student in that course. | 3 |
| 4 | What goes into the rating? | Student star reviews (1–5) at 70% and responsiveness at 30%, with a cap: under 80% of replies within 24 hours means the rating can't go above 4.0. If you want responsiveness only, skip step 5. | 4, 5 |
| 5 | Where is the rating stored? | Columns on `users`, refreshed hourly by a scheduled job. Listing pages show many instructors, and computing on every page view would add a query for each one. | 4 |
| 6 | Calendar hours or working hours? | Calendar hours, weekends included. Students are waiting on weekends too, and it's much simpler. | 4 |
| 7 | Existing messages? | No backfill. The clock columns start empty, so ratings count from deploy onwards. | 3 |
| 8 | Where does the rubric code live? | `app/Support/InstructorRating.php`, a plain class with static methods. There is no services folder yet. | 4 |

---

## Rubric

### Responsiveness (0–5)

Each student message that started a clock (question 3) is scored by how long the instructor took to reply:

| Reply time | Points |
|---|---|
| Within 2 hours | 5 |
| Within 8 hours | 4 |
| Within 24 hours | 3 |
| Within 48 hours | 1 |
| Over 48 hours, or still unanswered after 48 hours | 0 |

- **Unanswered and under 24 hours old:** not scored yet, because the instructor still has time.
- **Unanswered and over 24 hours old:** scored as if the reply came now. The rating drops while the student is still waiting, not only once the reply arrives.
- **Window:** the last 90 days.
- **Minimum:** at least 3 scored messages. Below that, responsiveness is "Not enough data".
- **Responsiveness** is the average of the points.
- **On-time rate:** the share of scored messages answered within 24 hours.

### Overall rating (1.0–5.0)

```
overall = stars × 0.7 + responsiveness × 0.3
if on_time_rate < 80%: overall = min(overall, 4.0)
```

- **No reviews yet:** overall = responsiveness, with the same cap.
- **No responsiveness data:** overall = stars.
- **Neither:** "New instructor".
- Shown to one decimal.

**Example:** stars 4.8, responsiveness 2.5 → 4.8 × 0.7 + 2.5 × 0.3 = 4.11. On-time rate is 60%, so it's capped at **4.0**.

---

## Step 1: Fix who can send what (backend only)

**Problems today:**
- The controller saves `sender_id` from the request body ([MessageController.php:78](app/Http/Controllers/MessageController.php#L78)), so anyone can send a message as someone else.
- Student-to-student messages are allowed (question 1).
- `content` is `max:255`, but the column is `text`.

**`StoreMessageRequest`:**

```php
public function authorize(): bool
{
    $course = Course::find($this->input('course_id'));

    if (! $course)
        return false;

    $senderId = $this->user()->id;
    $receiverId = (int) $this->input('receiver_id');

    // Messages only go between the course's instructor and one of its students, never student to student
    if ($course->instructor_id == $senderId)
        return $this->isEnrolled($receiverId, $course);

    return $course->instructor_id == $receiverId && $this->isEnrolled($senderId, $course);
}

private function isEnrolled($userId, Course $course): bool
{
    return $course->students()->where('student_id', $userId)->exists();
}

public function rules(): array
{
    return [
        'course_id' => 'required|integer|exists:courses,id',
        'receiver_id' => 'required|integer|exists:users,id',
        'content' => 'required|string|max:2000',
    ];
}
```

**`MessageController::__invoke`:** `'sender_id' => $request->user()->id`.

**Views:** remove `sender_id` from the fetch body in `student/messages.blade.php` and `instructor/manage-students.blade.php`.

**Tests** (`tests/Feature/MessageSendTest.php`):
- student → own instructor: 200
- instructor → enrolled student: 200
- student → another student: 403
- student → instructor of a course they're not in: 403
- a forged `sender_id` in the body is ignored, and the row has the real sender

---

## Step 2: Student conversation page

### Backend

Add a scope to `Message` so the student page and the instructor's `conversation()` share one query:

```php
use Illuminate\Database\Eloquent\Builder;

/**
 * Messages either way between two people
 */
public function scopeBetween(Builder $query, int $userId, int $otherId): void
{
    $query->where(fn ($q) => $q
        ->where(fn ($q) => $q->where('sender_id', $userId)->where('receiver_id', $otherId))
        ->orWhere(fn ($q) => $q->where('sender_id', $otherId)->where('receiver_id', $userId)));
}
```

**`MessageController::showMessages`:**

```php
public function showMessages(Course $course)
{
    // Every message both ways, whatever its status, so the student sees their whole history
    $messages = Message::where('course_id', $course->id)
        ->between(Auth::id(), $course->instructor_id)
        ->oldest()
        ->get();

    // Opening the page counts as reading the instructor's messages
    Message::where('course_id', $course->id)
        ->where('sender_id', $course->instructor_id)
        ->where('receiver_id', Auth::id())
        ->where('is_read', false)
        ->update(['is_read' => true]);

    return view('student.messages', compact('course', 'messages'));
}
```

**`conversation()`:** swap its inline `where(function ...)` for `->between($instructorId, $student->id)`. Also drop `where('status', 'active')` there, to match question 2.

### Page (`student/messages.blade.php`)

- **Thread:** the inbox cards become a chat thread, oldest first, scrolled to the bottom. Your messages sit on the right and the instructor's on the left. The bubble styles are the same as `addBubble()` in `manage-students`.
- **Composer:** one box at the bottom that's always visible and sends to `$course->instructor_id`. It replaces the per-message Reply buttons and the All/Recent filter. Search stays.
- **On send:** the new bubble is appended without a reload, and the box clears only once the server saves the message.

```blade
<div class="max-h-[60vh] space-y-3 overflow-y-auto" data-thread x-init="$el.scrollTop = $el.scrollHeight">
    @forelse($messages as $message)
        @php($mine = $message->sender_id === auth()->id())
        <div @class(['w-fit max-w-[80%] rounded-2xl px-4 py-2 text-sm', 'ml-auto bg-primary text-white' => $mine, 'bg-white text-gray-700 ring-1 ring-gray-100' => ! $mine])>
            <p class="whitespace-pre-line">{{ $message->content }}</p>
            <time @class(['mt-1 block text-[11px]', 'text-white/60' => $mine, 'text-gray-400' => ! $mine]) datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->diffForHumans() }}</time>
        </div>
    @empty
        <p data-thread-empty class="py-10 text-center text-sm text-gray-400">No messages yet. Ask your instructor anything below.</p>
    @endforelse
</div>

<form
    x-data="{ content: '', sending: false }"
    @submit.prevent="sending = true; sendMessage(content).then(sent => { sending = false; if (sent) content = ''; })"
    class="mt-4 flex gap-2"
>
    <textarea x-model="content" rows="2" maxlength="2000" required class="w-full rounded-2xl border border-gray-200 bg-white px-3 py-3 text-sm" placeholder="Message {{ $course->owner->fullName() }}..."></textarea>
    <button type="submit" :disabled="sending || !content.trim()" class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-white disabled:opacity-50">
        <i class="fas fa-paper-plane"></i>
    </button>
</form>
```

**Script:** `sendReply(receiverId, content)` becomes `sendMessage(content)` (receiver is always `{{ $course->instructor_id }}`). On success it appends a bubble with `textContent`, never `innerHTML`, removes `[data-thread-empty]`, and scrolls the thread to the bottom.

**Tests:**
- the page shows the student's own messages and the instructor's, in order
- it shows `inactive` messages
- it doesn't show another student's messages
- opening it marks the instructor's messages read

---

## Step 3: Track reply times (no visible change)

**Migration** `add_reply_tracking_to_messages_table`:

```php
Schema::table('messages', function (Blueprint $table) {
    // True on the student message that started the instructor's reply clock
    $table->boolean('awaits_reply')->default(false);
    $table->timestamp('replied_at')->nullable();
    $table->index(['course_id', 'sender_id', 'awaits_reply']);
});
```

**`Message`:**
- Add both columns to `$fillable`.
- Cast `replied_at` to `datetime` and `awaits_reply` to `boolean`.

**`MessageController::__invoke`**, inside a `DB::transaction`:

```php
$course = Course::find($validated['course_id']);
$senderId = $request->user()->id;
$fromInstructor = $course->instructor_id == $senderId;

// The open wait in this conversation, if the student is already waiting on a reply
$openWait = fn () => Message::where('course_id', $course->id)
    ->where('sender_id', $fromInstructor ? $validated['receiver_id'] : $senderId)
    ->where('awaits_reply', true)
    ->whereNull('replied_at');

Message::create([
    'sender_id' => $senderId,
    'receiver_id' => $validated['receiver_id'],
    'course_id' => $course->id,
    'content' => $validated['content'],
    // Only the first unanswered message starts the clock (question 3)
    'awaits_reply' => ! $fromInstructor && ! $openWait()->exists(),
]);

// Any instructor message to the student stops the clock
if ($fromInstructor)
    $openWait()->update(['replied_at' => now()]);
```

**Tests:**
- first student message: `awaits_reply` true
- second message before a reply: false
- instructor reply sets `replied_at` on the first message only
- the next student message after a reply starts a new clock
- instructor-first messages never start a clock

---

## Step 4: Rating calculation

### Migration `add_ratings_to_users_table`

All columns are nullable, and null means not enough data.

```php
Schema::table('users', function (Blueprint $table) {
    $table->decimal('rating', 2, 1)->nullable();
    $table->decimal('response_score', 3, 2)->nullable();
    // Percent of scored messages answered within 24 hours
    $table->unsignedTinyInteger('on_time_rate')->nullable();
    $table->timestamp('rating_updated_at')->nullable();
});
```

### `app/Support/InstructorRating.php`

```php
<?php

namespace App\Support;

use App\Models\Message;
use App\Models\User;

/**
 * The rubric in plans/messaging.md
 */
class InstructorRating
{
    const WINDOW_DAYS = 90;
    const MIN_SAMPLES = 3;
    const STARS_WEIGHT = 0.7;
    // Below this share of replies within 24 hours, the rating can't reach the top band
    const ON_TIME_TARGET = 80;
    const LATE_CAP = 4.0;

    /**
     * Points for one message that started a clock; null while it's unanswered and under 24 hours old
     */
    public static function points(Message $message): ?int
    {
        $hours = $message->created_at->diffInMinutes($message->replied_at ?? now()) / 60;

        if (! $message->replied_at && $hours < 24)
            return null;

        return match (true) {
            $hours <= 2 => 5,
            $hours <= 8 => 4,
            $hours <= 24 => 3,
            $hours <= 48 => 1,
            default => 0,
        };
    }

    /**
     * Writes rating, response_score and on_time_rate onto the instructor
     */
    public static function refresh(User $instructor): void
    {
        $points = Message::where('receiver_id', $instructor->id)
            ->where('awaits_reply', true)
            ->where('created_at', '>=', now()->subDays(self::WINDOW_DAYS))
            ->get()
            ->map(fn ($message) => self::points($message))
            ->reject(fn ($points) => $points === null);

        $enough = $points->count() >= self::MIN_SAMPLES;
        $response = $enough ? $points->avg() : null;
        // 3 or more points means it was answered within 24 hours
        $onTime = $enough ? (int) round($points->filter(fn ($p) => $p >= 3)->count() / $points->count() * 100) : null;

        // Step 5; null until reviews exist
        $stars = $instructor->reviewsReceived()->avg('stars');

        $rating = match (true) {
            $stars !== null && $response !== null => $stars * self::STARS_WEIGHT + $response * (1 - self::STARS_WEIGHT),
            default => $stars ?? $response,
        };

        if ($rating !== null && $onTime !== null && $onTime < self::ON_TIME_TARGET)
            $rating = min($rating, self::LATE_CAP);

        $instructor->update([
            // Never below 1.0, the bottom of the star scale
            'rating' => $rating === null ? null : round(max($rating, 1), 1),
            'response_score' => $response === null ? null : round($response, 2),
            'on_time_rate' => $onTime,
            'rating_updated_at' => now(),
        ]);
    }
}
```

If you skip step 5, delete the `$stars` line and use `$rating = $response`.

**`User`:** add the four columns to `$fillable` and casts. Columns are added with `update()`, so they must be fillable.

### Job `app/Jobs/RecalculateInstructorRatings.php`

```php
public function handle(): void
{
    User::where('role', 'instructor')->each(fn ($instructor) => InstructorRating::refresh($instructor));
}
```

**Schedule** in `bootstrap/app.php`: `$schedule->job(new RecalculateInstructorRatings())->hourly();`

**Tests** (`tests/Unit/InstructorRatingTest.php` plus one feature test), using `$this->travelTo()`:
- each band boundary: 2h → 5, 2h01 → 4, 24h → 3, 47h → 1, 49h → 0
- unanswered at 23h: not scored; unanswered at 30h: scored 1
- fewer than 3 samples: `response_score` null
- the cap: high stars with on-time rate under 80% → 4.0

---

## Step 5: Student star reviews (skip if responsiveness only)

**Migration** `create_instructor_reviews_table`:
- `course_id`, `student_id`, `instructor_id` (FKs, cascade)
- `stars` (tinyint, 1–5), `comment` (nullable text), timestamps
- `unique(['course_id', 'student_id'])`: one review per course, and editing replaces it

**Model:** `InstructorReview`. Add `User::reviewsReceived()`, a `hasMany` on `instructor_id`.

**Route:** `POST student/courses/{course}/review` → `InstructorReviewController@store`, `can('enrolled', 'course')`.

**Request:** `StoreInstructorReviewRequest` with `stars: required|integer|between:1,5` and `comment: nullable|string|max:1000`. The controller uses `updateOrCreate` on `course_id` + `student_id`, then calls `InstructorRating::refresh($course->owner)` so the change shows immediately.

**Page:** a 5-star picker plus an optional comment in the "Your instructor" card on `student/messages.blade.php`, pre-filled if the student already reviewed.

**Tests:**
- an enrolled student can review
- a second review replaces the first
- a non-enrolled student gets 403
- stars outside 1–5 are rejected

---

## Step 6: Show ratings

- **Student course page** (`dashboard/show.blade.php`) and the learn page instructor card:
  - stars plus `rating`, or "New instructor"
  - a line from `response_score`, e.g. "Usually replies within a few hours" (≥ 4), "within a day" (≥ 3), "Replies can be slow" (< 3)
- **Student messages sidebar:** the same reply-time line, so students know what to expect.
- **Instructor dashboard:** their rating, response score and on-time rate, plus a list of conversations waiting over 24 hours. That's `awaits_reply` true and `replied_at` null, older than 24h, with links into manage-students.
- **Manage students:** a "Waiting 1d+" badge on students with an overdue message, next to the unread dot.

**Tests:**
- the badge and the waiting list show only overdue conversations for this instructor
- the course page shows "New instructor" when `rating` is null

---

## Verification (end to end)

1. `php artisan test`: the new tests pass and the existing section tests still pass.
2. `php artisan migrate`, then in the browser:
   - As a student with no messages, send one from the messages page. It appears on the right without a reload.
   - As the instructor, open the conversation in manage-students. The student's message is there and unread clears. Reply.
   - As the student, reload. Both messages show, in order.
3. Rating check:
   - In tinker, set a student message's `created_at` back 30 hours with no reply.
   - Run `php artisan schedule:test`, or call `InstructorRating::refresh()`.
   - Check that `response_score` drops and the "Waiting 1d+" badge appears.

---

## Out of scope (noted for later)

- **Real-time delivery** (polling or websockets). Today you reload to see new messages.
- **Email or in-app notifications** for new messages.
- **"No reply needed":** a student's "Thanks!" starts the clock. A button for the instructor that sets `replied_at` without sending a message would fix that, but it can be abused, so it needs its own decision.
- **Attachments** in messages.
- **Unread count for students** in the nav.
