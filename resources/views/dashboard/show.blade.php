<x-layout>
    <div class="mb-8">
        <a href="{{ route('dashboard') }}" class="text-primary hover:underline flex items-center space-x-2">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Course Info --}}
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 {{ $course['color'] }} rounded-2xl flex items-center justify-center text-3xl">
                        <i class="fab {{ $course['icon'] }}"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">{{ $course['title'] }}</h1>
                        <p class="text-gray-500">Mastery Course</p>
                    </div>
                </div>

                <div class="prose max-w-none text-gray-600 mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Course Description</h2>
                    <p>{{ $course['description'] }}</p>
                </div>

                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">What you'll learn</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($course['syllabus'] as $item)
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-check-circle text-tea-green mt-1"></i>
                                <span class="text-gray-600">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Students Table --}}
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Enrolled Students</h2>
                    <span class="bg-blue-100 text-blue-600 px-4 py-1 rounded-full text-sm font-bold">
                        {{ number_format($course['enrolled_count'] )}} Total
                    </span>
                </div>

                @if(count($course['students']) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-sm border-b border-gray-100">
                                    <th class="pb-4 font-semibold">Student Name</th>
                                    <th class="pb-4 font-semibold">Email</th>
                                    <th class="pb-4 font-semibold">Enrollment Date</th>
                                    <th class="pb-4 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($course['students'] as $student)
                                    <tr class="text-gray-600">
                                        <td class="py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-400 text-xs"></i>
                                                </div>
                                                <span class="font-medium">{{ $student['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4">{{ $student['email'] }}</td>
                                        <td class="py-4">{{ $student['enrolled_at'] }}</td>
                                        <td class="py-4">
                                            <span class="px-2 py-1 bg-green-100 text-green-600 rounded-lg text-xs font-bold">Active</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No students enrolled yet in this preview.</p>
                @endif
            </div>
        </div>

        {{-- Right Column: Leader Info & Actions --}}
        <div class="space-y-8">
            {{-- Enrollment Card --}}
            <div class="bg-primary rounded-3xl p-8 shadow-lg text-white">
                <h3 class="text-2xl font-bold mb-4">Start Learning Today</h3>
                <p class="mb-6 opacity-90">Join {{ number_format($course['enrolled_count']) }} other students and master {{ $course['title'] }}.</p>
                <button class="w-full bg-white text-primary font-bold py-4 rounded-2xl hover:bg-gray-100 transition duration-300 flex items-center justify-center space-x-2">
                    <span>Enroll in Course</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
                <p class="text-center text-xs mt-4 opacity-75">30-Day Money Back Guarantee</p>
            </div>

            {{-- Course Leader Card --}}
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Course Leader</h3>
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 {{ $course['leader']['color'] }} rounded-2xl flex items-center justify-center text-2xl">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">{{ $course['leader']['name'] }}</h4>
                        <p class="text-sm text-primary font-semibold">Expert Instructor</p>
                    </div>
                </div>
                <div class="flex items-center mb-4 p-2 bg-gray-50 rounded-xl">
                    <i class="fas fa-medal mr-2"></i>
                    <span class="text-xs font-bold text-gray-700">Honorary Member</span>
                </div>
                <p class="text-gray-600 text-sm mb-6 leading-relaxed">
                    {{ $course['leader']['bio'] }}
                </p>
                <div class="space-y-3 border-t border-gray-50 pt-6">
                    <div class="flex items-center text-sm text-gray-500 space-x-2">
                        <i class="fas fa-envelope w-6 text-primary"></i>
                        <span>{{ $course['leader']['email'] }}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-500 space-x-2">
                        <i class="fas fa-phone w-6 text-primary"></i>
                        <span>{{ $course['leader']['phone'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Enquiry Form --}}
            <div class="bg-white rounded-3xl p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Have Questions?</h3>
                <form action="#" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Subject</label>
                        <input type="text" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="I'd like to know more about...">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-golden mb-1">Message</label>
                        <textarea class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 h-32" placeholder="Tell us what's on your mind..."></textarea>
                    </div>
                    <button type="submit" class="w-full bg-golden text-white font-bold py-3 rounded-xl hover:bg-gray-700 transition duration-300">
                        Send Enquiry
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layout>
