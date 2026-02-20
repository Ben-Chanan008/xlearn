<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('dashboard.index');
    }

    public function show($id)
    {
        $courses = [
            1 => [
                'id' => 1,
                'title' => 'Advanced Laravel Development',
                'leader' => [
                    'name' => 'Sarah Johnson',
                    'email' => 'sarah.j@xlearn.com',
                    'phone' => '+1 (555) 123-4567',
                    'bio' => 'A senior developer with 10+ years of experience in building enterprise-level applications with Laravel and Vue.js.',
                    'color' => 'bg-red-100 text-red-600'
                ],
                'enrolled_count' => 1243,
                'icon' => 'fa-laravel',
                'color' => 'bg-red-100 text-red-600',
                'description' => 'Master the art of building scalable and maintainable applications with Laravel. This course covers everything from architectural patterns to advanced testing techniques.',
                'syllabus' => [
                    'Architectural Patterns in Laravel',
                    'Advanced Eloquent & Database Optimization',
                    'Testing Strategies (TDD/BDD)',
                    'Custom Packages & Extensions',
                    'Scaling Laravel Applications'
                ],
                'students' => [
                    ['name' => 'Alice Freeman', 'email' => 'alice@example.com', 'enrolled_at' => '2024-01-15'],
                    ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'enrolled_at' => '2024-01-20'],
                    ['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'enrolled_at' => '2024-02-01'],
                ]
            ],
            2 => [
                'id' => 2,
                'title' => 'UI/UX Design Masterclass',
                'leader' => [
                    'name' => 'Michael Chen',
                    'email' => 'm.chen@xlearn.com',
                    'phone' => '+1 (555) 987-6543',
                    'bio' => 'Creative lead who has worked with Fortune 500 companies to create intuitive and engaging digital experiences.',
                    'color' => 'bg-blue-100 text-blue-600'
                ],
                'enrolled_count' => 856,
                'icon' => 'fa-uikit',
                'color' => 'bg-blue-100 text-blue-600',
                'description' => 'Learn the principles of modern UI/UX design. From wireframing to high-fidelity prototyping, master the tools and techniques used by industry professionals.',
                'syllabus' => [
                    'Introduction to Design Thinking',
                    'User Research & Persona Building',
                    'Wireframing & Prototyping in Figma',
                    'Visual Design Systems',
                    'Usability Testing & Iteration'
                ],
                'students' => [
                    ['name' => 'David Miller', 'email' => 'david@example.com', 'enrolled_at' => '2024-01-10'],
                    ['name' => 'Eva Green', 'email' => 'eva@example.com', 'enrolled_at' => '2024-01-25'],
                ]
            ],
            3 => [
                'id' => 3,
                'title' => 'Modern JavaScript Patterns',
                'leader' => [
                    'name' => 'Elena Rodriguez',
                    'email' => 'elena.r@xlearn.com',
                    'phone' => '+1 (555) 246-8135',
                    'bio' => 'Open source contributor and expert in modern JavaScript patterns, focusing on performance and scalability.',
                    'color' => 'bg-yellow-100 text-yellow-600'
                ],
                'enrolled_count' => 2105,
                'icon' => 'fa-js',
                'color' => 'bg-yellow-100 text-yellow-600',
                'description' => 'Deep dive into modern JavaScript. Understand closures, prototypes, asynchronous patterns, and the latest ESNext features.',
                'syllabus' => [
                    'Functional Programming in JS',
                    'Asynchronous JS: Promises & Async/Await',
                    'Design Patterns (Singleton, Observer, etc.)',
                    'Performance Optimization',
                    'Module Bundling & Tooling'
                ],
                'students' => [
                    ['name' => 'Frank Wright', 'email' => 'frank@example.com', 'enrolled_at' => '2024-02-05'],
                ]
            ],
            4 => [
                'id' => 4,
                'title' => 'Tailwind CSS for Pros',
                'leader' => [
                    'name' => 'David Wilson',
                    'email' => 'd.wilson@xlearn.com',
                    'phone' => '+1 (555) 369-1470',
                    'bio' => 'Full-stack developer with a passion for clean UI. David has been using Tailwind since its early alpha days.',
                    'color' => 'bg-cyan-100 text-cyan-600'
                ],
                'enrolled_count' => 1532,
                'icon' => 'fa-css3-alt',
                'color' => 'bg-cyan-100 text-cyan-600',
                'description' => 'Take your Tailwind CSS skills to the professional level. Learn about customization, plugins, and building complex layouts with ease.',
                'syllabus' => [
                    'Utility-First Workflow Optimization',
                    'Advanced Grid & Flexbox Layouts',
                    'Customizing Tailwind Configuration',
                    'Building Accessible Components',
                    'PurgeCSS & Production Optimization'
                ],
                'students' => []
            ],
            5 => [
                'id' => 5,
                'title' => 'Vue.js Enterprise Solutions',
                'leader' => [
                    'name' => 'Aisha Kamau',
                    'email' => 'aisha.k@xlearn.com',
                    'phone' => '+1 (555) 753-9514',
                    'bio' => 'Specializes in large-scale frontend architectures and has helped numerous startups scale their Vue.js applications.',
                    'color' => 'bg-green-100 text-green-600'
                ],
                'enrolled_count' => 642,
                'icon' => 'fa-vuejs',
                'color' => 'bg-green-100 text-green-600',
                'description' => 'Build robust, enterprise-ready applications with Vue.js 3 and the Composition API. Focus on state management, routing, and testing.',
                'syllabus' => [
                    'Composition API in Depth',
                    'State Management with Pinia',
                    'Vue Router Advanced Techniques',
                    'Unit Testing with Vitest',
                    'Server-Side Rendering (SSR) with Nuxt'
                ],
                'students' => []
            ],
            6 => [
                'id' => 6,
                'title' => 'Digital Marketing Essentials',
                'leader' => [
                    'name' => 'James Thompson',
                    'email' => 'j.thompson@xlearn.com',
                    'phone' => '+1 (555) 852-9630',
                    'bio' => 'Digital marketing strategist with over 15 years in the industry, helping brands grow their online presence.',
                    'color' => 'bg-purple-100 text-purple-600'
                ],
                'enrolled_count' => 3421,
                'icon' => 'fa-bullhorn',
                'color' => 'bg-purple-100 text-purple-600',
                'description' => 'Understand the core pillars of digital marketing: SEO, SEM, Social Media, and Content Strategy. Learn how to drive traffic and convert leads.',
                'syllabus' => [
                    'SEO Fundamentals & Keyword Research',
                    'Pay-Per-Click (PPC) Advertising',
                    'Social Media Marketing Strategies',
                    'Email Marketing & Automation',
                    'Analytics & Data-Driven Decisions'
                ],
                'students' => []
            ],
        ];

        $course = $courses[$id] ?? abort(404);

        return view('dashboard.show', compact('course'));
    }

    public function myCourses()
    {
//        $enrolledCourses = [];
        $enrolledCourses = [
            [
                'id' => 1,
                'title' => 'Advanced Laravel Development',
                'leader' => 'Sarah Johnson',
                'icon' => 'fa-laravel',
                'color' => 'bg-red-100 text-red-600',
                'progress' => 75,
                'status' => 'In Progress',
                'last_accessed' => '2 hours ago'
            ],
            [
                'id' => 2,
                'title' => 'UI/UX Design Masterclass',
                'leader' => 'Michael Chen',
                'icon' => 'fa-uikit',
                'color' => 'bg-blue-100 text-blue-600',
                'progress' => 100,
                'status' => 'Completed',
                'last_accessed' => '1 day ago'
            ],
            [
                'id' => 3,
                'title' => 'Modern JavaScript Patterns',
                'leader' => 'Elena Rodriguez',
                'icon' => 'fa-js',
                'color' => 'bg-yellow-100 text-yellow-600',
                'progress' => 15,
                'status' => 'In Progress',
                'last_accessed' => '3 days ago'
            ],
        ];

        // motivational messages
        $messages = [
            "Keep up the great work! You're making progress every day.",
            "Success is the sum of small efforts, repeated day in and day out.",
            "The more that you read, the more things you will know. The more that you learn, the more places you'll go.",
            "Your education is a dress rehearsal for a life that is yours to lead.",
            "Learning never exhausts the mind.",
        ];

        $motivationalMessage = $messages[array_rand($messages)];

        // Calculate average progress for the statistics section
        $averageProgress = count($enrolledCourses) > 0
            ? collect($enrolledCourses)->avg('progress')
            : 0;

        $completedCount = collect($enrolledCourses)->where('progress', 100)->count();
        $inProgressCount = collect($enrolledCourses)->where('progress', '<', 100)->count();

        return view('dashboard.my-courses', compact('enrolledCourses', 'motivationalMessage', 'averageProgress', 'completedCount', 'inProgressCount'));
    }
}
