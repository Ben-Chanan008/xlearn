<x-layout :footer="false">
    <div x-data="{
        name: @js($course->name),
        description: @js($course->description),
        price: '{{ old('price', $course->price) }}',
        tags: '{{ old('tags', $course->tags) }}',
        max_students: '{{ old('max_students', $course->max_students) }}',
        discount: '{{ old('discount_code', $course->discount_code ?? 0) }}',
        thumbnail: null,
        previewUrl: '{{ $course->thumbnail ? asset('storage/' . $course->thumbnail) : null }}',
        handleFile(e) {
            const file = e.target.files[0];
            if (file) {
                this.thumbnail = file;
                this.previewUrl = URL.createObjectURL(file);
            }
        },
        get tagList() {
            return this.tags.split(',').map(t => t.trim()).filter(t => t !== '');
        },
        get finalPrice() {
            let p = parseFloat(this.price) || 0;
            let d = parseFloat(this.discount) || 0;
            return (p * (1 - d/100)).toFixed(2);
        }
    }" class="py-8">

        {{-- Top Navigation & Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors flex items-center">
                        <i class="fas fa-home mr-1.5 text-xs"></i> Dashboard
                    </a>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-primary font-medium">Edit Course</span>
                </nav>
                <h1 class="text-5xl mt-8 font-black text-primary tracking-tight">
                    Refine Your <span class="text-golden italic">Masterpiece.</span>
                </h1>
                <p class="text-gray-600 mt-2 text-lg">Update your course details to keep your students engaged.</p>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-2xl font-bold text-gray-500 hover:bg-white/50 transition-all border border-transparent hover:border-gray-200">
                    Cancel
                </a>
                <button type="submit" form="courseForm" class="px-8 py-3 hover:cursor-pointer bg-primary text-white rounded-2xl font-bold shadow-xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-save text-sm"></i>
                    Update Course
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">

            {{-- Main Form Section --}}
            <div class="lg:col-span-7 space-y-8">
                <form id="courseForm" action="{{ route('courses.update', $course) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')

                    {{-- Section: Identity --}}
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-[2.5rem] shadow-sm border border-white/20 space-y-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-tea-green rounded-xl flex items-center justify-center text-primary">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Basic Information</h2>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Course Name</label>
                            <input type="text" name="name" id="name" x-model="name" required
                                   class="w-full px-6 py-4 rounded-2xl border-2 border-gray-100 focus:border-primary outline-none transition-all bg-gray-50/50 text-lg font-medium"
                                   placeholder="e.g. Master the Art of Minimalism">
                            @error('name') <p class="text-red-500 text-xs mt-2 ml-2 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-bold text-gray-700 mb-2 ml-1">What will they learn?</label>
                            <textarea name="description" id="description" rows="5" x-model="description" required
                                      class="w-full px-6 py-4 rounded-2xl border-2 border-gray-100 focus:border-primary outline-none transition-all bg-gray-50/50 resize-none font-medium"
                                      placeholder="A compelling overview of your course content...">{{ $course->description }}</textarea>
                            @error('description') <p class="text-red-500 text-xs mt-2 ml-2 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Section: Visuals --}}
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-[2.5rem] shadow-sm border border-white/20">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-tea-green rounded-xl flex items-center justify-center text-primary">
                                <i class="fas fa-paint-brush"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Visual Appeal</h2>
                        </div>

                        <div class="relative group">
                            <input type="file" name="thumbnail" id="thumbnail" @change="handleFile" class="hidden" accept="image/jpeg,image/png,image/jpg,image/gif,image/avif">
                            <label for="thumbnail" class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-200 rounded-3xl cursor-pointer hover:bg-gray-50/50 hover:border-primary/30 transition-all">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <template x-if="!previewUrl">
                                        <div class="text-center">
                                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-3 group-hover:text-primary/50 transition-colors"></i>
                                            <p class="text-sm font-bold text-gray-500">Drop your thumbnail here</p>
                                            <p class="text-xs text-gray-400 mt-1">PNG, JPG, GIF or AVIF (max. 2MB)</p>
                                        </div>
                                    </template>
                                    <template x-if="previewUrl">
                                        <div class="flex items-center gap-4 px-6">
                                            <img :src="previewUrl" class="w-32 h-20 object-cover rounded-xl shadow-md">
                                            <div class="text-left">
                                                <p class="text-sm font-bold text-primary">Image Selected!</p>
                                                <p class="text-xs text-gray-400 mt-1">Click to change the thumbnail</p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </label>
                            @error('thumbnail') <p class="text-red-500 text-xs mt-2 ml-2 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Section: Details & Pricing --}}
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-[2.5rem] shadow-sm border border-white/20">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-tea-green rounded-xl flex items-center justify-center text-primary">
                                <i class="fas fa-tags"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Details & Pricing</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label for="tags" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Tags (Comma separated)</label>
                                    <input type="text" name="tags" id="tags" x-model="tags" required
                                           class="w-full px-5 py-3.5 rounded-2xl border-2 border-gray-100 focus:border-primary outline-none transition-all bg-gray-50/50 font-medium"
                                           placeholder="Laravel, Design, UI">
                                    @error('tags') <p class="text-red-500 text-xs mt-2 ml-2 font-medium">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="max_students" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Max Students</label>
                                    <div class="relative">
                                        <i class="fas fa-users absolute left-5 top-1/2 -translate-y-1/2 text-gray-300"></i>
                                        <input type="number" name="max_students" id="max_students" x-model="max_students" required min="1"
                                               class="w-full pl-12 pr-5 py-3.5 rounded-2xl border-2 border-gray-100 focus:border-primary outline-none transition-all bg-gray-50/50 font-medium"
                                               placeholder="100">
                                    </div>
                                    @error('max_students') <p class="text-red-500 text-xs mt-2 ml-2 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label for="price" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Price ($)</label>
                                    <div class="relative">
                                        <i class="fas fa-dollar-sign absolute left-5 top-1/2 -translate-y-1/2 text-gray-300"></i>
                                        <input type="number" step="0.01" name="price" id="price" x-model="price" required min="0"
                                               class="w-full pl-12 pr-5 py-3.5 rounded-2xl border-2 border-gray-100 focus:border-primary outline-none transition-all bg-gray-50/50 font-medium"
                                               placeholder="49.99">
                                    </div>
                                    @error('price') <p class="text-red-500 text-xs mt-2 ml-2 font-medium">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="discount_code" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Discount (%) <span class="font-normal text-gray-400 italic">Optional</span></label>
                                    <div class="relative">
                                        <i class="fas fa-percent absolute left-5 top-1/2 -translate-y-1/2 text-gray-300"></i>
                                        <input type="number" name="discount_code" id="discount_code" x-model="discount" min="0" max="100"
                                               class="w-full pl-12 pr-5 py-3.5 rounded-2xl border-2 border-gray-100 focus:border-primary outline-none transition-all bg-gray-50/50 font-medium"
                                               placeholder="10">
                                    </div>
                                    @error('discount_code') <p class="text-red-500 text-xs mt-2 ml-2 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 p-6 bg-tea-green/30 rounded-3xl border border-tea-green/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-fingerprint text-primary/40 text-xl"></i>
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest font-black text-primary/50">Unique Course Identifier</p>
                                    <p class="font-mono font-bold text-primary">{{ $course->course_code }}</p>
                                    <input type="hidden" name="course_code" value="{{ $course->course_code }}">
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-primary/60 font-medium">Identifier cannot be changed</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Sidebar Preview Section --}}
            <div class="lg:col-span-5 sticky top-8">
                <div class="mb-4 flex items-center justify-between px-2">
                    <h3 class="text-sm font-black uppercase tracking-widest text-primary/60 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-golden animate-pulse"></span>
                        Live Preview
                    </h3>
                    <span class="text-[10px] font-bold bg-white/50 px-2 py-0.5 rounded-full text-gray-500 border border-gray-100">{{ ucfirst($course->status) }}</span>
                </div>

                <div class="group relative bg-white rounded-[2.5rem] overflow-hidden shadow-2xl shadow-primary/10 border border-white transition-all duration-500 hover:-translate-y-2">
                    {{-- Preview Thumbnail --}}
                    <div class="relative h-64 bg-gray-100 overflow-hidden">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!previewUrl">
                            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 text-gray-200">
                                <i class="fas fa-image text-7xl mb-4"></i>
                                <p class="text-sm font-bold uppercase tracking-widest opacity-50">Course Art</p>
                            </div>
                        </template>

                        <div class="absolute top-6 right-6">
                            <div class="bg-white/90 backdrop-blur-md px-4 py-2 rounded-2xl shadow-sm border border-white/50">
                                <p class="text-xs font-black text-primary/40 uppercase tracking-tighter leading-none mb-1">Students</p>
                                <p class="text-lg font-black text-primary leading-none" x-text="max_students || '0'"></p>
                            </div>
                        </div>

                        <div class="absolute bottom-0 left-0 w-full p-6 bg-gradient-to-t from-black/60 to-transparent">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="tag in tagList" :key="tag">
                                    <span class="px-3 py-1 bg-tea-green/90 backdrop-blur-md text-primary text-[10px] font-black uppercase rounded-lg shadow-sm" x-text="tag"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Preview Content --}}
                    <div class="p-8 space-y-6">
                        <div class="space-y-2">
                            <h3 class="text-3xl font-black text-primary leading-tight line-clamp-2" x-text="name"></h3>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-tea-green flex items-center justify-center">
                                    <i class="fas fa-user text-[10px] text-primary"></i>
                                </div>
                                <p class="text-sm font-bold text-gray-500">{{ $course->owner->fullName() }}</p>
                            </div>
                        </div>

                        <p class="text-gray-500 text-sm leading-relaxed line-clamp-3 font-medium" x-text="description"></p>

                        <div class="pt-6 border-t border-gray-50 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Tuition Fee</p>
                                <div class="flex items-end gap-2">
                                    <span class="text-3xl font-black text-primary leading-none">$<span x-text="finalPrice"></span></span>
                                    <template x-if="discount > 0">
                                        <span class="text-sm font-bold text-gray-300 line-through mb-1" x-text="'$' + price"></span>
                                    </template>
                                </div>
                            </div>
                            <button disabled class="px-6 py-3 bg-tea-green text-primary font-black rounded-2xl text-xs uppercase tracking-widest shadow-sm shadow-tea-green/20 opacity-50 cursor-not-allowed">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Pro Tip Card --}}
                <div class="mt-8 p-6 bg-primary rounded-[2rem] text-white/90 relative overflow-hidden group">
                    <div class="relative z-10">
                        <h4 class="font-black uppercase text-xs tracking-[0.2em] text-tea-green mb-2">Instructor Tip</h4>
                        <p class="text-sm leading-relaxed font-medium">Courses with high-quality thumbnails and clear descriptions have <span class="text-golden font-bold">80% higher</span> enrollment rates.</p>
                    </div>
                    <i class="fas fa-lightbulb absolute -right-4 -bottom-4 text-7xl text-white/5 rotate-12 group-hover:rotate-0 transition-transform duration-500"></i>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</x-layout>
