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


        <div class="flex justify-center space-x-3">
            <button class="bg-primary text-white p-3 rounded-xl font-bold cursor-pointer">Get Started <i class="fas fa-circle-play"></i></button>
            <button class="bg-primary text-white p-3 rounded-xl font-bold cursor-pointer">Sign In <i class="fas fa-lock"></i></button>
        </div>
    </div>
</nav>