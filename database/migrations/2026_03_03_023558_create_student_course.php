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
        Schema::create('student_course', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class, 'student_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Course::class, 'course_id')->constrained()->cascadeOnDelete();
            $table->integer('course_progress')->default(0);
            $table->date('accessed_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_course');
    }
};
