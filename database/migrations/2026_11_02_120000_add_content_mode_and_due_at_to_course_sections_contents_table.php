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
        Schema::table('course_sections_contents', function (Blueprint $table) {
            // Written lessons are HTML now, so 255 characters isn't enough; file-only content has none
            $table->longText('content_information')->nullable()->change();
            // text, files or both: which inputs the instructor filled in
            $table->string('content_mode')->default('text')->after('content_name');
            $table->dateTime('due_at')->nullable()->after('content_type');
        });

        // files was a single path string; it's now a JSON list of {path, name, mime}
        DB::table('course_sections_contents')->get(['id', 'content_information', 'files'])
            ->each(function ($content) {
                $files = $content->files && ! str_starts_with($content->files, '[')
                    ? json_encode([['path' => $content->files, 'name' => basename($content->files), 'mime' => null]])
                    : $content->files;

                DB::table('course_sections_contents')->where('id', $content->id)->update([
                    'files' => $files,
                    'content_mode' => match (true) {
                        $files && $content->content_information => 'both',
                        (bool) $files => 'files',
                        default => 'text',
                    },
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Back to a single path string: the first file, if any
        DB::table('course_sections_contents')->whereNotNull('files')->get(['id', 'files'])
            ->each(fn ($content) => DB::table('course_sections_contents')->where('id', $content->id)->update([
                'files' => json_decode($content->files, true)[0]['path'] ?? null,
            ]));

        Schema::table('course_sections_contents', function (Blueprint $table) {
            $table->dropColumn(['content_mode', 'due_at']);
            $table->string('content_information')->change();
        });
    }
};
