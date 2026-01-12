<x-layout>
    <div class="hero grid grid-cols-[2fr_1fr] py-16">
        <div class="flex flex-col">
            <p class="text-9xl pt-9 font-bold">Learn Now <br> Benefit Later</p>
            <span>The best way to learn anything! <a href="" class="text-primary underline">Sign up</a> now and begin your journey. Lorem, ipsum dolor sit amet consectetur adipisicing elit. Illum molestiae voluptates suscipit labore facilis. Recusandae commodi illum error earum molestiae?</span>
        </div>
        <img src="{{ asset('images/hero.svg') }}" alt="hero" />
    </div>
    <section>
        <x-header>A great selection of courses!</x-header>
        <div class="grid grid-cols-1 gap-8 my-10 px-16">
            <div class="grid gap-8 grid-cols-[2fr_300px]">
                <div class="w-full bg-primary text-white flex items-center gap-8 p-8 rounded-2xl">
                    <i class="fab fa-7x fa-html5"></i>
                    <p class="text-4xl font-bold">Learn Web Development</p>
                </div>
                <p><span class="font-bold">HTML5 CSS JS</span>, sit amet consectetur adipisicing elit. Nisi repudiandae a, pariatur ea mollitia quasi repellat aperiam commodi numquam dolores sunt animi, voluptatum similique maiores. Est dignissimos repudiandae voluptate eius.</p>
            </div>
            <div class="grid gap-8 grid-cols-[300px_1fr]">
                <p><span class="font-bold">Figma, Adobe, UI/UX</span> Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nisi repudiandae a, pariatur ea mollitia quasi repellat aperiam commodi numquam dolores sunt animi, voluptatum similique maiores. Est dignissimos repudiandae voluptate eius.</p>
                <div class="w-full bg-primary text-white flex items-center gap-8 p-8 rounded-2xl">
                    <i class="fab fa-7x fa-uikit"></i>
                    <p class="text-4xl font-bold">Become a UI/UX Designer</p>
                </div>
            </div>
            <div class="grid gap-8 grid-cols-[1fr_300px]">
                <div class="w-full bg-primary text-white flex items-center gap-8 p-8 rounded-2xl">
                    <i class="fab fa-7x fa-laravel"></i>
                    <p class="text-4xl font-bold">Top-teir Laravel Courses</p>
                </div>
                <p><span class="font-bold">Routing, MVC, Elegant PHP</span> Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nisi repudiandae a, pariatur ea mollitia quasi repellat aperiam commodi numquam dolores sunt animi, voluptatum similique maiores. Est dignissimos repudiandae voluptate eius.</p>
            </div>
            <div class="grid gap-8 grid-cols-[300px_1fr]">
                <p><span class="font-bold">Vue, Nuxtjs, Nodejs</span> Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nisi repudiandae a, pariatur ea mollitia quasi repellat aperiam commodi numquam dolores sunt animi, voluptatum similique maiores. Est dignissimos repudiandae voluptate eius.</p>
                <div class="w-full bg-primary text-white flex items-center gap-8 p-8 rounded-2xl">
                    <i class="fab fa-7x fa-react"></i>
                    <p class="text-4xl font-bold">Full Stack JS Development</p>
                </div>
            </div>
        </div>
        <div class="px-16">
            <a href="" class="text-primary underline">See more courses...</a>
        </div>
    </section>    
    <section id="what-you-get" class="my-10">
        <x-header>What You Get?</x-header>
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Magni vel adipisci eos` similique nesciunt et odit voluptas tenetur sunt sit.</p>
        <div class="flex p-8 h-25">
            <div class="w-full h-full flex items-center justify-center font-bold text-golden border-r-2 px-3">
                <p>Web Development Courses</p>
            </div>
            <div class="w-full h-full flex items-center justify-center font-bold text-golden border-r-2 px-3">
                <p>World Leadership Courses</p>
            </div>
            <div class="w-full h-full flex items-center justify-center font-bold text-golden border-r-2 px-3">
                <p>Educational Courses</p>
            </div>
            <div class="w-full h-full flex items-center justify-center font-bold text-golden px-3">
                <p>Faith Improvement Courses</p>
            </div>
        </div>
        <div class="mx-16">
            <p class="text-2xl font-bold">Unlock your full learning potential!</p>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatem vitae, repellat ipsam expedita molestiae natus itaque illum fugit quisquam voluptates. Facere amet tempora repellendus culpa at laboriosam esse repellat consectetur!</p>
        </div>
        <section>
            <div class="grid grid-cols-2 p-8 items-center">
                {{-- <img src="https://placehold.co/600x400" alt="placeholder_img" class="rounded-2xl"> --}}
                <div class="justify-self-center">
                    <i class="fas fa-child-reaching fa-10x"></i>
                </div>
                <div>
                    <p class="text-4xl my-5 font-semibold relative">Great Instructors!</p>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Neque nisi error, accusantium debitis ipsum cupiditate itaque pariatur recusandae in magnam voluptates repudiandae corrupti? Fugit voluptates perspiciatis excepturi quae itaque. Sit veritatis natus deserunt eaque quaerat commodi enim esse nihil doloribus, explicabo odio minus asperiores dolorum ducimus totam, vitae, officiis dolore?</p>
                </div>
            </div>
            <div class="grid grid-cols-2 p-8 items-center">
                {{-- <img src="https://placehold.co/600x400" alt="placeholder_img" class="rounded-2xl"> --}}
                <div>
                    <p class="text-4xl my-5 font-semibold relative">We respect time!</p>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Neque nisi error, accusantium debitis ipsum cupiditate itaque pariatur recusandae in magnam voluptates repudiandae corrupti? Fugit voluptates perspiciatis excepturi quae itaque. Sit veritatis natus deserunt eaque quaerat commodi enim esse nihil doloribus, explicabo odio minus asperiores dolorum ducimus totam, vitae, officiis dolore?</p>
                </div>
                <div class="justify-self-center">
                    <i class="far fa-clock fa-10x"></i>
                </div>
            </div>
            <div class="grid grid-cols-2 p-8 items-center">
                {{-- <img src="https://placehold.co/600x400" alt="placeholder_img" class="rounded-2xl"> --}}
                <div class="justify-self-center">
                    <i class="fas fa-business-time fa-10x"></i>
                </div>
                <div>
                    <p class="text-4xl my-5 font-semibold relative">Multitasking simplified!</p>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Neque nisi error, accusantium debitis ipsum cupiditate itaque pariatur recusandae in magnam voluptates repudiandae corrupti? Fugit voluptates perspiciatis excepturi quae itaque. Sit veritatis natus deserunt eaque quaerat commodi enim esse nihil doloribus, explicabo odio minus asperiores dolorum ducimus totam, vitae, officiis dolore?</p>
                </div>
            </div>
        </section>
        <button class="bg-golden text-white btn">Get Started <i class="ml-3 fas fa-arrow-right-long"></i></button>
    </section>
</x-layout>