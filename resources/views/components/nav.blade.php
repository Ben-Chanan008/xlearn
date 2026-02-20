<nav>
    <div class="grid grid-cols-3 py-8 px-16 items-center justify-around">
        <img src="{{ asset('images/logo.png') }}" alt="logo" class="w-50"/>

        <div class="flex space-x-8 w-full items-center justify-center">
            <ul class="flex font-semibold justify-center space-x-5">
                <li><a href="{{ route('home') }}" class="text-xl">Home</a></li>
                <li><a href="{{ route('home') }}" class="text-xl">About</a></li>
                <li><a href="{{ route('home') }}" class="text-xl">Learn</a></li>
            </ul>
            {{-- <div class="border-b-2 w-full flex items-center space-x-2 py-2">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" class="w-full focus:outline-none" placeholder="Search...">
            </div> --}}
        </div>


        @auth
            <div class="flex mx-auto items-center space-x-3 relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none cursor-pointer">
                    <div class="rounded-full h-15 w-15 flex justify-center items-center bg-gray-50 border border-gray-100 shadow-sm">
                        <i class="fas fa-2x fa-user text-gray-400"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-xs text-gray-500 leading-none">Hello,</p>
                        <p class="font-bold text-gray-800">{{ auth()->user()->fullName()}} <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i></p>
                    </div>
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="open"
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50"
                     style="display: none;">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-columns w-5 text-gray-400"></i>
                        <span class="ml-2">Dashboard</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-cog w-5 text-gray-400"></i>
                        <span class="ml-2">Settings</span>
                    </a>
                    <hr class="my-1 border-gray-100">
                    <a href="{{ route('logout') }}" class="flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition duration-200">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span class="ml-2 font-semibold">Logout</span>
                    </a>
                </div>
            </div>
        @else
            <div class="flex justify-center space-x-3">
                <a href="{{ route('register') }}" class="bg-primary text-white p-3 rounded-xl font-bold cursor-pointer">Get Started <i class="fas fa-circle-play"></i></a>
                <a href="{{ route('login') }}" class="bg-primary text-white p-3 rounded-xl font-bold cursor-pointer">Sign In <i class="fas fa-lock"></i></a>
            </div>
        @endauth
    </div>
</nav>
