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
        Schema::table('preview_andon', function (Blueprint $table) {
            $table->datetime('start_actual')->nullable()->after('calculated_finish');
            $table->datetime('finish_actual')->nullable()->after('start_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preview_andon', function (Blueprint $table) {
            $table->dropColumn(['start_actual', 'finish_actual']);
        });
    }
};