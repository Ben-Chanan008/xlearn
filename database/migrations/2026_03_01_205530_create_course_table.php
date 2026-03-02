<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description');
            $table->longText('thumbnail')->nullable();
            $table->string('price');
            $table->string('discount_code')->nullable();
            $table->foreignIdFor(\App\Models\User::class, 'instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('course_code')->unique();
            $table->integer('max_students')->default(100);
            $table->string('status')->default('pending');
            $table->string('tags');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
