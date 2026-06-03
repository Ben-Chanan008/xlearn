<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CourseSectionsContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    static string $password = 'Password12@';
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::factory()->admin()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@xlearn.com',
            'password' => Hash::make(self::$password),
        ]);

        $instructors = User::factory(10)
            ->instructor()
            ->hasCourses(25)
            ->create();

        $defaultStudent = User::factory(30)
            ->student()
            ->create();

        // Course::all()->each(function (Course $course) {
        //     $sections = $course->courseSections()->saveMany(
        //         CourseSection::factory(4)->make()
        //     );

        //     foreach ($sections as $section) {
        //         CourseSectionsContent::factory(3)->for($section)->create();
        //     }
        // });
    }
}