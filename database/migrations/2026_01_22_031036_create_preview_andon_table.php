<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preview_andon', function (Blueprint $table) {
            $table->id();
            $table->string('mesin_nama'); // Nama mesin dari tabel mesin
            $table->string('part_no');
            $table->decimal('gsph', 10, 2)->default(0);
            $table->datetime('start_time')->nullable();
            $table->datetime('end_time')->nullable();
            $table->integer('plan_qty')->default(0);
            $table->integer('actual_qty')->default(0);
            $table->decimal('efficiency', 5, 2)->default(0); // dalam persen
            $table->string('status')->default('idle'); // idle, running, completed
            $table->integer('mesin_id')->nullable(); // Foreign key ke tabel mesin
            $table->integer('planning_id')->nullable(); // Foreign key ke tabel planning
            $table->timestamps();
            
            // Indexes
            $table->index('mesin_nama');
            $table->index('status');
            $table->index('mesin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preview_andon');
    }
};