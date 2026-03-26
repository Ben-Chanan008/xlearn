<x-layout :footer="true">
    <div class="py-12 px-4 sm:px-6 lg:px-16">
        <div class="mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-2">
                {{auth()->user()->isInstructor() ? 'Instructor' : 'Student'}} Dashboard
            </h1>
            <p class="text-gray-600">Welcome back, {{ auth()->user()->first_name }}! Here's what's happening with your learning journey.</p>
        </div>

        {{-- Tabs Section --}}
        <x-tabs />

        {{-- Content Area - Home Tab (Course List) --}}
        <div id="home-content">
            <div class="flex items-center justify-between mb-8">
                <x-header>All Courses</x-header>
                <div class="flex space-x-2">
                    @can('create', \App\Models\Course::class)
                        <a href="{{route('courses.create')}}" class="bg-primary text-white font-bold p-3 rounded-xl hover:cursor-pointer">
                            <i class="fas fa-book"></i> Create Course
                        </a>
                    @endcan
                    <button class="p-2 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-th-large text-gray-500"></i>
                    </button>
                    <button class="p-2 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-list text-gray-500"></i>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                @forelse($courses as $course)
                    <a href="{{ route('courses.show', $course->slug) }}" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition duration-300 flex flex-col group">
                        <div class="p-8 grow">
                            <div class="flex items-start justify-between mb-6">
                                <div class="w-14 h-14 flex items-center justify-center text-2xl group-hover:scale-110 transition duration-300">
                                    <img
                                        src="{{$course->thumbnail ? asset('storage/'. $course->thumbnail) : asset('images/logo.png')}}"
                                        alt="{{$course->slug}}"
                                        class="rounded-2xl"
                                    >
                                    <i class="fab "></i>
                                </div>
                                @if($course->created_at->gt(now()->subDays(7)))
                                    <span class="bg-tea-green text-primary text-xs font-bold px-3 py-1 rounded-full">New</span>
                                @endif
                            </div>

                            <h3 class="text-xl font-bold text-gray-800 mb-2 leading-tight group-hover:text-primary transition duration-300">{{ $course->name }}</h3>

                            <div class="space-y-3 mb-6">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-user-tie w-5 text-gray-400"></i>
                                    <span class="ml-2">By <span class="font-semibold text-gray-700">{{ $course->owner->fullName() }}</span></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-users w-5 text-gray-400"></i>
                                    <span class="ml-2"><span class="font-semibold text-gray-700">0</span> students enrolled</span>
                                </div>
                                <div class="flex space-x-1">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                    <p class="text-gray-700 text-sm">Created: {{ ($course->created_at->diffForHumans())  }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="px-8 pb-8 pt-0 mt-auto">
                            <div class="w-full bg-primary text-white font-bold py-3.5 rounded-2xl hover:opacity-90 transition duration-300 shadow-lg flex items-center justify-center space-x-2">
                                <span>View Details</span>
                                <i class="fas fa-arrow-right text-xs"></i>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center bg-white rounded-3xl border border-dashed border-gray-300">
                        <i class="fas fa-book-open text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900">No courses available yet</h3>
                        <p class="text-gray-500">Check back later for new learning opportunities.</p>
                    </div>
                @endforelse
            </div>
            <div class="">
                {{ $courses->links()  }}
            </div>
            {{-- Course Leaders Section --}}
            <div class="mt-16">
                <div class="flex items-center justify-between mb-8">
                    <x-header>Meet Our Course Leaders</x-header>
                </div>

                @php
                    $leaders = [
                        [
                            'name' => 'Sarah Johnson',
                            'achievement' => 'Top Rated Laravel Instructor',
                            'medal' => 'fa-medal text-yellow-500',
                            'summary' => 'A senior developer with 10+ years of experience in building enterprise-level applications with Laravel and Vue.js.',
                            'color' => 'bg-red-50'
                        ],
                        [
                            'name' => 'Michael Chen',
                            'achievement' => 'Award Winning UI/UX Designer',
                            'medal' => 'fa-award text-blue-500',
                            'summary' => 'Creative lead who has worked with Fortune 500 companies to create intuitive and engaging digital experiences.',
                            'color' => 'bg-blue-50'
                        ],
                        [
                            'name' => 'Elena Rodriguez',
                            'achievement' => 'JavaScript Pioneer',
                            'medal' => 'fa-trophy text-orange-400',
                            'summary' => 'Open source contributor and expert in modern JavaScript patterns, focusing on performance and scalability.',
                            'color' => 'bg-yellow-50'
                        ]
                    ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($leaders as $leader)
                        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition duration-300 relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 w-24 h-24 {{ $leader['color'] }} rounded-full opacity-50 group-hover:scale-110 transition duration-500"></div>

                            <div class="relative">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mr-4">
                                        <i class="fas fa-user-tie text-gray-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-800">{{ $leader['name'] }}</h4>
                                        <p class="text-xs font-semibold text-primary uppercase tracking-wider">{{ $leader['achievement'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center mb-4 p-2 bg-gray-50 rounded-xl">
                                    <i class="fas {{ $leader['medal'] }} mr-2"></i>
                                    <span class="text-xs font-bold text-gray-700">Honorary Member</span>
                                </div>

                                <p class="text-sm text-gray-600 leading-relaxed">
                                    {{ $leader['summary'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Reviews Section --}}
            <div class="mt-24">
                <div class="flex items-center justify-between mb-8">
                    <x-header>Student Reviews</x-header>
                </div>

                @php
                    $reviews = [
                        [
                            'user' => 'Emily Watson',
                            'course' => 'Advanced Laravel Development',
                            'rating' => 5,
                            'comment' => 'The most comprehensive Laravel course I have ever taken. The instructor explains complex concepts with ease.',
                            'date' => '2 days ago'
                        ],
                        [
                            'user' => 'Liam Smith',
                            'course' => 'UI/UX Design Masterclass',
                            'rating' => 4,
                            'comment' => 'Great content and practical examples. I learned a lot about modern design principles and tools.',
                            'date' => '1 week ago'
                        ],
                        [
                            'user' => 'Sophia Garcia',
                            'course' => 'Modern JavaScript Patterns',
                            'rating' => 5,
                            'comment' => 'Absolutely loved the section on asynchronous patterns. It cleared up so much confusion for me!',
                            'date' => '3 days ago'
                        ]
                    ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($reviews as $review)
                        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition duration-300">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-tea-green rounded-full flex items-center justify-center mr-3">
                                        <span class="text-primary font-bold text-xs">{{ substr($review['user'], 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-800">{{ $review['user'] }}</h4>
                                        <p class="text-[10px] text-gray-500">{{ $review['date'] }}</p>
                                    </div>
                                </div>
                                <div class="flex text-yellow-400 text-xs">
                                    @for($i = 0; $i < 5; $i++)
                                        <i class="fas fa-star {{ $i < $review['rating'] ? '' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-xs font-semibold text-primary mb-2 italic">"{{ $review['course'] }}"</p>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                {{ $review['comment'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- FAQ Section --}}
            <div class="mt-24 mb-16">
                <div class="flex flex-col items-center text-center mb-12">
                    <x-header>Frequently Asked Questions</x-header>
                    <p class="text-gray-600 max-w-2xl mt-2">Have questions? We're here to help. Find answers to the most common queries about our courses and platform.</p>
                </div>

                @php
                    $faqs = [
                        [
                            'question' => 'How do I access my certificates?',
                            'answer' => 'Once you complete all the lessons in a course and pass the final assessment, your certificate will be automatically generated. You can find all your certificates in the "Certificates" tab of your dashboard.'
                        ],
                        [
                            'question' => 'Can I learn at my own pace?',
                            'answer' => 'Yes, absolutely! All our courses are self-paced. Once you enroll, you have lifetime access to the materials, so you can learn whenever it fits your schedule.'
                        ],
                        [
                            'question' => 'Do you offer a refund policy?',
                            'answer' => 'We offer a 30-day money-back guarantee if you are not satisfied with the course content, provided you have not completed more than 20% of the course.'
                        ],
                        [
                            'question' => 'How can I contact my course instructor?',
                            'answer' => 'Each course has a dedicated Q&A section where you can post your questions. Our instructors and community members are very active and usually respond within 24 hours.'
                        ]
                    ];
                @endphp

                <div class="max-w-3xl mx-auto space-y-4" x-data="{ activeFaq: null }">
                    @foreach($faqs as $index => $faq)
                        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-md transition duration-300">
                            <button
                                @click="activeFaq === {{ $index }} ? activeFaq = null : activeFaq = {{ $index }}"
                                class="w-full px-8 py-6 text-left flex items-center justify-between focus:outline-none"
                            >
                                <span class="font-bold text-gray-800">{{ $faq['question'] }}</span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300" :class="activeFaq === {{ $index }} ? 'rotate-180' : ''"></i>
                            </button>
                            <div
                                x-show="activeFaq === {{ $index }}"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 max-h-0"
                                x-transition:enter-end="opacity-100 max-h-40"
                                class="px-8 pb-6 text-sm text-gray-600 leading-relaxed"
                                style="display: none;"
                            >
                                {{ $faq['answer'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layout>
