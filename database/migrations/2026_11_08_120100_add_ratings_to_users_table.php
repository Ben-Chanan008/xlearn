<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * All nullable: null means not enough data yet
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('rating', 2, 1)->nullable();
            $table->decimal('response_score', 3, 2)->nullable();
            // Percent of scored messages answered within 24 hours
            $table->unsignedTinyInteger('on_time_rate')->nullable();
            $table->timestamp('rating_updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rating', 'response_score', 'on_time_rate', 'rating_updated_at']);
        });
    }
};
