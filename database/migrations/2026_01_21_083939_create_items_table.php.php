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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('part_no', 50);
            $table->string('line', 50)->nullable();
            $table->foreignId('mesin_id')->constrained('mesin')->onDelete('cascade');
            $table->string('process', 50)->nullable();
            $table->string('process_name', 100)->nullable();
            $table->decimal('gsph', 12, 6)->nullable();
            $table->timestamps();
            
            // Index untuk pencarian
            $table->index('part_no');
            $table->index('line');
            $table->index('process_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};