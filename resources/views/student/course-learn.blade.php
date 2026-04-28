@php
    $inActiveClass = 'border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition duration-200';
    $activeClass = 'border-primary rounded-t-lg active group text-primary border-primary';  
    $activeSection = true;
@endphp

<x-layout :footer="false">
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <!-- Back to Courses -->
        <a href="{{ route('my-courses') }}" class="inline-flex items-center text-sm font-bold text-primary mb-6 hover:translate-x-[-4px] transition-transform">
            <i class="fas fa-arrow-left mr-2"></i> Back to My Courses
        </a>
        <x-header>{{ $course->name }}</x-header>
        <div class="mb-8 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="dashboard-tabs">
                <li class="mr-2">
                    <a href="{{ auth()->user()->isInstructor() ? route('instructor.dashboard') : route('dashboard') }}"
                    class="inline-block p-4 border-b-2 {{ (request()->routeIs('dashboard') || request()->routeIs('instructor.dashboard')) ?  $activeClass : $inActiveClass }}" aria-current="page">
                        <i class="fas fa-book-open mr-2"></i>Course
                    </a>
                </li>
                @if(auth()->user()->isStudent())
                    <li class="mr-2">
                        <a href="{{ route('my-courses') }}" class="inline-block p-4 border-b-2 {{ request()->routeIs('my-courses') ?  $activeClass : $inActiveClass }}">
                            <i class="fas fa-user-group mr-2"></i>Participants
                        </a>
                    </li>
                @endif
                @if(auth()->user()->isInstructor())
                    <li class="mr-2">
                        <a href="{{ route('instructor.courses') }}" class="inline-block p-4 border-b-2 {{ request()->routeIs('instructor.courses') ?  $activeClass : $inActiveClass }}">
                            <i class="fas fa-chalkboard-teacher mr-2"></i>Grades
                        </a>
                    </li>
                @endif
                <li class="mr-2">
                    <a href="#" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition duration-200">
                        <i class="fas fa-certificate mr-2"></i>Certificates
                    </a>
                </li>
            </ul>
        </div>

    </div>
    <div class="mb-4 grid grid-cols-[400px_1fr] gap-8 max-w-screen">
        <div class="p-8 bg-white rounded-xl space-y-4">
            <p class="font-bold">Course Content</p>
            {{-- Course Section --}}
            <a href="#">
                <div class="mt-3 items-center flex gap-4 bg-gray-200 p-3 rounded-xl shadow">
                    <span class="{{ $activeSection ? 'bg-black' : 'bg-gray-300 text-black!' }} rounded-full w-8 text-center font-bold text-white flex justify-center items-center h-8">1.</span>
                    <p class="truncate font-semibold">Introduction to Course Laravel fjk</p>
                </div>
            </a>
            <div class="mt-3 items-center flex gap-4 bg-gray-200 p-3 rounded-xl shadow">
                <span class="{{ $activeSection ? 'bg-black' : 'bg-gray-300 text-black!' }} rounded-full w-8 text-center font-bold text-white flex justify-center items-center h-8">2.</span>
                <p class="truncate font-semibold">Introduction to Course Laravel fjk</p>
            </div>
        </div>
        <div class="p-8 bg-white rounded-xl ">
            <section class="mx-16">
                <div class="flex items-center gap-x-2">
                    <p class="text-golden font-bold">Laravel 12 Course</p>
                    <span class="text-xs font-bold"> / </span>
                    <p class="text-golden font-bold">Introduction to Laravel 13</p>
                </div>

                <h3 class="my-4 text-2xl font-extrabold">
                   <i class="fas fa-file"></i> Welcome to Laravel 13 
                </h3>
                <div class="bg-gray-400 py-[2.5px] text-sm font-bold rounded-xl text-center text-white relative">
                    <div class="absolute bottom-0 top-0 left-0 rounded-xl" style="width: {{ $progress }}%"><span class="transition-progress h-full block rounded-xl bg-primary"></span></div>
                </div>
                <span class="font-bold text-xs my-2">In progress... {{ $progress}} %</span>
                <div id="content" class="my-8">
                    
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Recusandae ea facilis officia voluptatem quae nobis dolorum! Iusto voluptate commodi rerum tempore laborum accusamus, minus fugit, suscipit alias fugiat, voluptatem cupiditate excepturi vitae quis. Exercitationem molestiae doloremque tempora debitis excepturi. Veniam voluptatibus dignissimos voluptate omnis ipsam repellat unde veritatis dicta perferendis quis aperiam consequuntur magnam accusamus cum at ratione pariatur tempore facere voluptates inventore sequi voluptatem, consequatur modi aliquid! Facilis, facere ipsam eos odio eum architecto mollitia minima praesentium ipsa temporibus officiis eveniet expedita vel nesciunt dolore tenetur optio nulla, nisi non sequi! Sunt repellat error placeat, in incidunt perferendis. Repellendus aspernatur quaerat nisi accusamus consectetur rerum sunt enim corporis dolor quo blanditiis repudiandae, in nulla placeat provident iusto itaque ut excepturi et explicabo amet at vitae soluta. Sunt in mollitia aliquam voluptate officia esse pariatur quis deserunt consectetur expedita amet consequatur corrupti earum placeat tenetur aspernatur, asperiores laudantium iusto optio voluptatem sint. Culpa officia aut debitis nemo nostrum fugit veniam sed praesentium quia ut molestiae exercitationem nisi laboriosam labore, minima facere porro animi iure minus magni reiciendis voluptate doloribus libero. Ex consequatur suscipit ipsa fugit tempore hic ratione, in inventore porro? Eos nam reprehenderit accusantium modi totam? Aspernatur perspiciatis culpa cupiditate, consectetur inventore eaque totam. Suscipit, eligendi, eveniet natus veritatis recusandae nam, nemo aspernatur quaerat officiis perspiciatis itaque cumque fugit consequuntur sit quod fugiat? Facere, ipsum vitae distinctio dolore provident, voluptas, veritatis excepturi reprehenderit non minima nostrum aperiam! Deleniti molestiae laboriosam, voluptatum error ut consequuntur. Reprehenderit dicta illum sed rerum tempora aliquid rem, nesciunt, ab, unde voluptatibus ipsam. Ducimus neque porro, a sint, ratione fuga rem corrupti tenetur suscipit qui alias enim est sapiente sed modi, numquam id temporibus. Facilis doloribus dicta dignissimos suscipit cumque, exercitationem hic accusantium quia libero praesentium id neque voluptates necessitatibus maxime minus eaque laborum molestiae.
                    </p>
                    <br />

                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Optio voluptate explicabo, possimus magni voluptas aliquam consequatur obcaecati est, iure eum excepturi itaque laudantium! Quas facere sint, reprehenderit cumque temporibus ipsa pariatur iste libero aperiam et, incidunt suscipit corporis accusamus quae laudantium atque ut rem aspernatur qui officia accusantium quis. Repudiandae?</p>

                    <br/>

                    Lorem ipsum dolor, sit amet consectetur adipisicing elit. Praesentium in quos necessitatibus hic, quam sapiente! Quam necessitatibus impedit iure accusamus?
                
                </div>
                <p class="mt-16 text-sm font-bold">Last modified: {{ $course->created_at }}</p>
            </section>
        </div>
    </div>
</x-layout>
