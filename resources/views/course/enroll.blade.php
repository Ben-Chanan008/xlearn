<x-layout>
    <div class="py-12 px-4 sm:px-6 lg:px-8 min-h-[calc(100vh-140px)]">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-gray-800">Checkout</h1>
                <p class="text-gray-500 mt-2">Complete your enrollment for <strong>{{ $course->name }}</strong></p>
            </div>

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                    <p class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Course Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden sticky top-8">
                        @if($course->thumbnail)
                            <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="{{ $course->name }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                <i class="fas fa-book text-gray-300 text-5xl"></i>
                            </div>
                        @endif
                        <div class="p-6">
                            <div class="mb-4">
                                <span class="px-3 py-1 bg-tea-green text-primary text-xs font-bold rounded-full uppercase tracking-wider">
                                    {{ $course->course_code }}
                                </span>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800 mb-2 leading-tight">{{ $course->name }}</h2>
                            <p class="text-gray-600 text-sm mb-6 line-clamp-3">{{ $course->description }}</p>

                            <div class="space-x-3 text-lg flex items-center my-3">
                                <i class="fas fa-user-circle text-primary"></i>
                                <p class="font-bold">{{ $course->owner->fullName() }}</p>
                            </div>

                            <div class="space-y-3 pt-4 border-t border-gray-100">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Course Price</span>
                                    <span class="text-gray-800 font-semibold">${{ number_format((float)$course->price, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-lg pt-2">
                                    <span class="text-gray-800 font-bold">Total</span>
                                    <span class="text-2xl font-black text-primary">${{ number_format((float)$course->price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
                        <div class="flex items-center mb-8 pb-4 border-b border-gray-50">
                            <div class="w-12 h-12 bg-tea-green rounded-2xl flex items-center justify-center text-primary mr-4">
                                <i class="fas fa-credit-card text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Payment Information</h2>
                                <p class="text-gray-500 text-sm">All transactions are secure and encrypted</p>
                            </div>
                        </div>

                        <form action="{{ route('courses.checkout', $course) }}" method="POST" class="space-y-6">
                            @csrf

                            {{-- Name on Card --}}
                            <div>
                                <label for="name_on_card" class="block text-sm font-semibold text-gray-700 mb-2">Name on Card</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                        <i class="fas fa-user-circle"></i>
                                    </span>
                                    <input type="text" name="name_on_card" id="name_on_card" value="{{ old('name_on_card') }}" required
                                           class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                                           placeholder="John Doe">
                                </div>
                                @error('name_on_card') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Card Number --}}
                            <div>
                                <label for="card_number" class="block text-sm font-semibold text-gray-700 mb-2">Card Number</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                        <i class="fas fa-credit-card"></i>
                                    </span>
                                    <input type="text" name="card_number" id="card_number" value="{{ old('card_number') }}" required
                                           class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                                           placeholder="0000 0000 0000 0000">
                                </div>
                                @error('card_number') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                {{-- Expiry Date --}}
                                <div>
                                    <label for="expiry_date" class="block text-sm font-semibold text-gray-700 mb-2">Expiry Date</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <input type="text" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" required
                                               class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                                               placeholder="MM/YY">
                                    </div>
                                    @error('expiry_date') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- CVV --}}
                                <div>
                                    <label for="cvv" class="block text-sm font-semibold text-gray-700 mb-2">CVV</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="text" name="cvv" id="cvv" value="{{ old('cvv') }}" required
                                               class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                                               placeholder="123">
                                    </div>
                                    @error('cvv') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="pt-4">
                                <button type="submit"
                                        class="w-full bg-primary text-white font-bold py-4 rounded-2xl hover:opacity-90 transition duration-300 shadow-lg flex items-center justify-center space-x-2 group">
                                    <span>Pay and Enroll Now</span>
                                    <i class="fas fa-chevron-right text-xs group-hover:translate-x-1 transition-transform"></i>
                                </button>

                                <div class="mt-6 flex items-center justify-center space-x-6 text-gray-400 text-2xl">
                                    <i class="fab fa-cc-visa"></i>
                                    <i class="fab fa-cc-mastercard"></i>
                                    <i class="fab fa-cc-stripe"></i>
                                    <i class="fab fa-cc-apple-pay"></i>
                                </div>

                                <p class="text-center text-gray-400 text-xs mt-6 flex items-center justify-center">
                                    <i class="fas fa-lock mr-2"></i>
                                    Payments are encrypted and processed securely.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
