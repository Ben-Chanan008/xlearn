<x-layout :footer="false">
    <div class="flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 min-h-[calc(100vh-140px)]">
        <div class="w-full max-w-2xl p-10 bg-white rounded-3xl shadow-xl border border-gray-100">
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-tea-green rounded-full mb-4">
                    <i class="fas fa-user-plus text-primary text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Create Account</h1>
                <p class="text-gray-500 mt-2">Join us and start your learning journey today</p>
            </div>

            <form action="{{ route('register.post') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- First Name --}}
                    <div>
                        <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                                   class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                                   placeholder="John">
                        </div>
                        @error('first_name') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Last Name --}}
                    <div>
                        <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                                   class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                                   placeholder="Doe">
                        </div>
                        @error('last_name') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                   class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                                   placeholder="john@example.com">
                        </div>
                        @error('email') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" name="password" id="password" required
                                   class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                                   placeholder="••••••••">
                        </div>
                        @error('password') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Date of Birth --}}
                    <div>
                        <label for="date_of_birth" class="block text-sm font-semibold text-gray-700 mb-2">Date of Birth</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" required
                                   class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white">
                        </div>
                        @error('date_of_birth') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label for="gender" class="block text-sm font-semibold text-gray-700 mb-2">Gender</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <i class="fas fa-venus-mars"></i>
                            </span>
                            <select name="gender" id="gender" required
                                    class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white appearance-none">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Male</option>
                                <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        @error('gender') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                                   class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                                   placeholder="+1 (555) 000-0000">
                        </div>
                        @error('phone') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Province --}}
                    <div>
                        <label for="province" class="block text-sm font-semibold text-gray-700 mb-2">Province</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 pointer-events-none">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <select name="province" id="province" required
                                    class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white appearance-none">
                                <option value="" disabled selected>Select Province</option>
                                <option value="AB" {{ old('province') == 'AB' ? 'selected' : '' }}>Alberta</option>
                                <option value="BC" {{ old('province') == 'BC' ? 'selected' : '' }}>British Columbia</option>
                                <option value="MB" {{ old('province') == 'MB' ? 'selected' : '' }}>Manitoba</option>
                                <option value="NB" {{ old('province') == 'NB' ? 'selected' : '' }}>New Brunswick</option>
                                <option value="NL" {{ old('province') == 'NL' ? 'selected' : '' }}>Newfoundland and Labrador</option>
                                <option value="NS" {{ old('province') == 'NS' ? 'selected' : '' }}>Nova Scotia</option>
                                <option value="ON" {{ old('province') == 'ON' ? 'selected' : '' }}>Ontario</option>
                                <option value="PE" {{ old('province') == 'PE' ? 'selected' : '' }}>Prince Edward Island</option>
                                <option value="QC" {{ old('province') == 'QC' ? 'selected' : '' }}>Quebec</option>
                                <option value="SK" {{ old('province') == 'SK' ? 'selected' : '' }}>Saskatchewan</option>
                                <option value="NT" {{ old('province') == 'NT' ? 'selected' : '' }}>Northwest Territories</option>
                                <option value="NU" {{ old('province') == 'NU' ? 'selected' : '' }}>Nunavut</option>
                                <option value="YT" {{ old('province') == 'YT' ? 'selected' : '' }}>Yukon</option>
                            </select>
                        </div>
                        @error('province') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Address --}}
                <div>
                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                    <div class="relative">
                        <span class="absolute top-3.5 left-0 flex items-start pl-4 text-gray-400">
                            <i class="fas fa-home"></i>
                        </span>
                        <textarea name="address" id="address" rows="3" required
                                  class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                                  placeholder="Enter your full address">{{ old('address') }}</textarea>
                    </div>
                    @error('address') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="w-full bg-primary text-white font-bold py-4 rounded-2xl hover:opacity-90 transition duration-300 shadow-lg flex items-center justify-center space-x-2">
                    <span>Create Account</span>
                    <i class="fas fa-user-plus text-sm"></i>
                </button>
            </form>

            <div class="mt-10 text-center">
                <p class="text-sm text-gray-500">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-bold text-primary hover:underline transition duration-200">Sign in here</a>
                </p>
            </div>
        </div>
    </div>
</x-layout>
