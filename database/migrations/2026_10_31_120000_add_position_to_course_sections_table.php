<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_sections', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('section_name');
        });

        // Number the existing sections 1, 2, 3... per course, in the order they were created
        DB::table('course_sections')->orderBy('course_id')->orderBy('id')->get(['id', 'course_id'])
            ->groupBy('course_id')
            ->each(fn ($sections) => $sections->each(
                fn ($section, $index) => DB::table('course_sections')->where('id', $section->id)->update(['position' => $index + 1])
            ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
