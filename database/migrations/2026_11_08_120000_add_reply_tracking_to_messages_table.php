<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // True on the student message that started the instructor's reply clock
            $table->boolean('awaits_reply')->default(false);
            $table->timestamp('replied_at')->nullable();
            $table->index(['course_id', 'sender_id', 'awaits_reply']);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'sender_id', 'awaits_reply']);
            $table->dropColumn(['awaits_reply', 'replied_at']);
        });
    }
};
