<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('preview_andon', function (Blueprint $table) {
            // Tambahkan kolom shift_sequence
            $table->integer('shift_sequence')->nullable()->after('shift');
            
            // Jika belum ada, tambahkan juga kolom is_active dan sort_order
            if (!Schema::hasColumn('preview_andon', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('planning_id');
            }
            
            if (!Schema::hasColumn('preview_andon', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });
    }

    public function down()
    {
        Schema::table('preview_andon', function (Blueprint $table) {
            $table->dropColumn(['shift_sequence', 'is_active', 'sort_order']);
        });
    }
};