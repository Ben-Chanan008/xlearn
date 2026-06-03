<?php

use App\Models\CourseSection;
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
        Schema::create('course_sections_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CourseSection::class, 'course_section_id')->constrained()->cascadeOnDelete();
            $table->string('content_name');
            $table->string('content_information');
            $table->longText('files')->nullable();
            $table->enum('content_type', ['assignment', 'information']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_sections_contents');
    }
};