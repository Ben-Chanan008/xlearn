<x-layout :footer="false">
    <div class="flex items-center justify-center min-h-[calc(100vh-140px)]">
        <div class="w-full max-w-md p-10 bg-white rounded-3xl shadow-xl border border-gray-100">
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-tea-green rounded-full mb-4">
                    <i class="fas fa-key text-primary text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Reset Password</h1>
                <p class="text-gray-500 mt-2">Enter your email and we'll send you a link to reset your password.</p>
            </div>

            <form action="#" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" name="email" id="email" required
                               class="w-full pl-11 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition duration-200 bg-gray-50 focus:bg-white"
                               placeholder="you@example.com">
                    </div>
                    @error('email') <p class="text-red-500 text-sm mt-1 px-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="w-full bg-primary text-white font-bold py-4 rounded-2xl hover:opacity-90 transition duration-300 shadow-lg flex items-center justify-center space-x-2">
                    <span>Send Reset Link</span>
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </form>

            <div class="mt-10 text-center">
                <p class="text-sm text-gray-500">
                    Remember your password?
                    <a href="{{ route('login') }}" class="font-bold text-primary hover:underline transition duration-200">Go back to login</a>
                </p>
            </div>
        </div>
    </div>
</x-layout>
