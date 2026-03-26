<x-layout :footer="false">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Back to Courses -->
        <a href="{{ route('my-courses') }}" class="inline-flex items-center text-sm font-bold text-primary mb-6 hover:translate-x-[-4px] transition-transform">
            <i class="fas fa-arrow-left mr-2"></i> Back to My Courses
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Course Content & Assignments -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Course Header Card -->
                <div class="bg-white rounded-4xl p-8 shadow-sm border border-gray-100 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4">
                        <span class="inline-flex items-center px-4 py-2 rounded-2xl bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider">
                            <i class="fas fa-users mr-2"></i> {{ $studentCount }} Students Enrolled
                        </span>
                    </div>

                    <h1 class="text-3xl font-black text-gray-900 mb-4">{{ $course->name }}</h1>

                    <div class="flex items-center space-x-4 mb-6">
                        <div class="flex items-center px-4 py-2 bg-gray-50 rounded-2xl border border-gray-100">
                            <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary mr-3">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter leading-none">Instructor</p>
                                <p class="font-bold text-gray-900">{{ $course->owner->fullName() }}</p>
                            </div>
                        </div>

                        <div class="flex items-center px-4 py-2 {{ $instructorOnline ? 'bg-tea-green/30 text-primary' : 'bg-gray-100 text-gray-400' }} rounded-2xl border border-transparent">
                            <span class="relative flex h-2 w-2 mr-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $instructorOnline ? 'bg-primary' : 'bg-gray-400' }} opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 {{ $instructorOnline ? 'bg-primary' : 'bg-gray-400' }}"></span>
                            </span>
                            <span class="text-xs font-bold uppercase tracking-wider">{{ $instructorOnline ? 'Online' : 'Offline' }}</span>
                        </div>
                    </div>

                    <!-- Progress Bar (Certificate focus) -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-end">
                            <p class="text-sm font-bold text-gray-700 uppercase tracking-widest">Course Progress</p>
                            <p class="text-2xl font-black text-primary">{{ round($progress) }}%</p>
                        </div>
                        <div class="h-4 bg-gray-100 rounded-full overflow-hidden border border-gray-50">
                            <div class="h-full bg-linear-to-r from-primary to-tea-green transition-all duration-1000" style="width: {{ $progress }}%"></div>
                        </div>
                        <p class="text-xs font-medium text-gray-500">
                            @if($progress == 100)
                                <i class="fas fa-certificate text-primary mr-1"></i> You've earned your certificate!
                            @else
                                Keep going! Only {{ 100 - round($progress) }}% more to reach your certificate.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Course Content Sections (Mocked for now) -->
                <div class="space-y-4">
                    <h3 class="text-xl font-bold text-gray-900 px-2">Course Modules</h3>

                    @php
                        $modules = [
                            ['title' => 'Introduction to the Course', 'duration' => '15 mins', 'completed' => true],
                            ['title' => 'Foundations and Core Concepts', 'duration' => '45 mins', 'completed' => true],
                            ['title' => 'Practical Applications Phase 1', 'duration' => '1 hour', 'completed' => $progress > 50],
                            ['title' => 'Advanced Techniques and Best Practices', 'duration' => '2 hours', 'completed' => $progress == 100],
                        ];
                    @endphp

                    @foreach($modules as $index => $module)
                        <div class="bg-white rounded-3xl p-5 border border-gray-100 flex items-center justify-between group hover:border-primary/30 transition-colors">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-2xl {{ $module['completed'] ? 'bg-primary text-white' : 'bg-gray-50 text-gray-400' }} flex items-center justify-center font-bold">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">{{ $module['title'] }}</h4>
                                    <p class="text-xs text-gray-400 font-medium"><i class="far fa-clock mr-1"></i> {{ $module['duration'] }}</p>
                                </div>
                            </div>
                            @if($module['completed'])
                                <div class="w-8 h-8 rounded-full bg-tea-green flex items-center justify-center text-primary">
                                    <i class="fas fa-check text-xs"></i>
                                </div>
                            @else
                                <button class="px-4 py-2 bg-gray-50 text-gray-600 text-xs font-bold rounded-xl hover:bg-primary hover:text-white transition-colors">
                                    Start
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Assignment Section -->
                <div class="bg-white rounded-4xl p-8 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Assignments</h3>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-[10px] font-bold rounded-full uppercase tracking-widest">1 Pending</span>
                    </div>

                    <div class="p-6 rounded-3xl bg-gray-50 border-2 border-dashed border-gray-200 text-center space-y-4">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm">
                            <i class="fas fa-file-upload text-2xl text-primary/50"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Submit Your Project</h4>
                            <p class="text-sm text-gray-500 max-w-xs mx-auto">Upload your final course project here. Accepted formats: PDF, ZIP (Max 50MB)</p>
                        </div>
                        <label class="inline-block px-8 py-3 bg-primary text-white font-bold rounded-2xl cursor-pointer hover:shadow-lg transition duration-300">
                            Choose File
                            <input type="file" class="hidden">
                        </label>
                    </div>
                </div>
            </div>

            <!-- Right Column: Instructor Interaction -->
            <div class="lg:col-span-1">
                <div class="sticky top-8 space-y-6">
                    <!-- Instructor Message Box -->
                    <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden flex flex-col h-[600px]">
                        <div class="p-6 bg-primary text-white">
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-xl font-bold">
                                        {{ $course->owner->initials() }}
                                    </div>
                                    @if($instructorOnline)
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-tea-green border-2 border-primary rounded-full"></div>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold leading-none mb-1">{{ $course->owner->fullName() }}</h4>
                                    <p class="text-[10px] uppercase font-bold tracking-widest opacity-70">
                                        {{ $instructorOnline ? 'Active Now' : 'Last seen 2h ago' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grow p-6 overflow-y-auto space-y-4 bg-gray-50/50">
                            <!-- Mock Messages -->
                            <div class="flex flex-col">
                                <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[85%] border border-gray-100">
                                    Hello! How are you finding the course so far? If you have any questions about the modules, feel free to ask here.
                                </div>grow
                                <span class="text-[10px] text-gray-400 mt-1 ml-1 font-bold">10:45 AM</span>
                            </div>

                            <div class="flex flex-col items-end">
                                <div class="bg-primary text-white p-4 rounded-2xl rounded-tr-none shadow-sm text-sm max-w-[85%]">
                                    Hi instructor! I'm stuck on Module 2, the core concepts are a bit tricky.
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 mr-1 font-bold">11:02 AM</span>
                            </div>

                            <div class="flex flex-col">
                                <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 max-w-[85%] border border-gray-100">
                                    No problem! Let me know which specific part is tricky and I'll help you out.
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 ml-1 font-bold">11:05 AM</span>
                            </div>
                        </div>

                        <div class="p-4 border-t border-gray-100">
                            <form class="flex items-center space-x-2">
                                <input type="text" placeholder="Type your message..." class="grow bg-gray-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20">
                                <button type="button" class="w-12 h-12 bg-primary text-white rounded-2xl flex items-center justify-center hover:opacity-90 transition shadow-lg shadow-primary/20">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Additional Info / Help -->
                    <div class="bg-tea-green/30 p-6 rounded-4xl border border-primary/10">
                        <h4 class="font-bold text-primary mb-2 flex items-center">
                            <i class="fas fa-lightbulb mr-2"></i> Learning Tip
                        </h4>
                        <p class="text-xs text-primary/80 leading-relaxed font-medium">
                            Try to complete one module per day to keep your momentum high. Consistency is key to mastering these new skills!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
