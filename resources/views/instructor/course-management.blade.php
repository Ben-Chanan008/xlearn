@props(['course'])

<div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
    {{-- Header / Summary Section --}}
    <div class="p-8 bg-gray-50/50 border-b border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-1">{{ $course->name }}</h2>
                <p class="text-gray-500">Course Management & Student Progress</p>
            </div>

            <div class="flex flex-wrap gap-4">
                {{-- Stats Cards --}}
                <div class="bg-white px-6 py-3 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-3">
                    <div class="w-10 h-10 bg-tea-green rounded-xl flex items-center justify-center text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Students</span>
                        <span class="text-lg font-bold text-gray-800">{{ $course->students->count() }}</span>
                    </div>
                </div>

                <div class="bg-white px-6 py-3 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Total Earnings</span>
                        <span class="text-lg font-bold text-primary">${{ number_format($course->students->count() * (float)$course->price, 2) }}</span>
                    </div>
                </div>

                {{-- Action: Close Course --}}
                <form action="#" method="POST" onsubmit="return confirm('Are you sure you want to close this course listing? New students will not be able to enroll.')">
                    @csrf
                    <button type="submit" class="h-full px-6 py-3 bg-red-50 text-red-600 font-bold rounded-2xl hover:bg-red-100 transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-times-circle"></i>
                        <span>Close Course</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Students Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-400 text-xs font-bold uppercase tracking-widest">
                    <th class="px-8 py-5 border-b border-gray-100">Student Name</th>
                    <th class="px-8 py-5 border-b border-gray-100">Progress</th>
                    <th class="px-8 py-5 border-b border-gray-100 text-center">Status</th>
                    <th class="px-8 py-5 border-b border-gray-100 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($course->students as $student)
                    <tr class="hover:bg-gray-50/50 transition duration-200">
                        {{-- Name & Email --}}
                        <td class="px-8 py-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-bold">
                                    {{ $student->initials() }}
                                </div>
                                <div>
                                    <span class="block font-bold text-gray-800">{{ $student->fullName() }}</span>
                                    <span class="text-sm text-gray-500">{{ $student->email }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Progress Bar --}}
                        <td class="px-8 py-6 w-1/3">
                            <div class="flex items-center space-x-3">
                                <div class="flex-grow h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary transition-all duration-500" style="width: {{ $student->pivot->course_progress }}%"></div>
                                </div>
                                <span class="text-sm font-bold text-gray-700 min-w-[3rem] text-right">
                                    {{ $student->pivot->course_progress }}%
                                </span>
                            </div>
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-8 py-6 text-center">
                            @if($student->pivot->course_progress == 100)
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-full uppercase tracking-wider">
                                    Completed
                                </span>
                            @elseif($student->pivot->course_progress > 0)
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full uppercase tracking-wider">
                                    In Progress
                                </span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-500 text-[10px] font-bold rounded-full uppercase tracking-wider">
                                    Not Started
                                </span>
                            @endif
                        </td>

                        {{-- Actions: Certificate --}}
                        <td class="px-8 py-6 text-right">
                            @if($student->pivot->course_progress == 100)
                                <button class="inline-flex items-center px-4 py-2 bg-tea-green text-primary font-bold text-sm rounded-xl hover:shadow-md transition duration-300">
                                    <i class="fas fa-award mr-2"></i>
                                    Issue Certificate
                                </button>
                            @else
                                <button disabled class="inline-flex items-center px-4 py-2 bg-gray-50 text-gray-300 font-bold text-sm rounded-xl cursor-not-allowed">
                                    <i class="fas fa-lock mr-2"></i>
                                    Issue Certificate
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-user-slash text-4xl text-gray-200 mb-4"></i>
                                <p class="text-gray-500 font-semibold">No students enrolled in this course yet.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
