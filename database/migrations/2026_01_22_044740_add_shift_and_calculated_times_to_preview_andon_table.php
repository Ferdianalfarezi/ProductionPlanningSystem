<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preview_andon', function (Blueprint $table) {
            // Tambah kolom shift (1 atau 2)
            $table->enum('shift', ['1', '2'])->default('1')->after('efficiency');
            
            // Kolom untuk waktu yang dihitung
            $table->datetime('calculated_start')->nullable()->after('shift');
            $table->datetime('calculated_finish')->nullable()->after('calculated_start');
            
            // Durasi produksi dalam jam
            $table->decimal('production_duration', 8, 2)->default(0)->after('calculated_finish');
            
            // Kolom untuk menyimpan urutan dalam shift
            $table->integer('shift_sequence')->nullable()->after('production_duration');
            
            // Index untuk pencarian
            $table->index('shift');
            $table->index('calculated_start');
            $table->index('calculated_finish');
        });
    }

    public function down(): void
    {
        Schema::table('preview_andon', function (Blueprint $table) {
            $table->dropColumn([
                'shift',
                'calculated_start', 
                'calculated_finish',
                'production_duration',
                'shift_sequence'
            ]);
            
            $table->dropIndex(['shift']);
            $table->dropIndex(['calculated_start']);
            $table->dropIndex(['calculated_finish']);
        });
    }
};