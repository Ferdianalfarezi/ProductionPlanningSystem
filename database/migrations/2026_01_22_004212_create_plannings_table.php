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
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            
            // Basic Info
            $table->integer('planning_id')->nullable()->comment('ID dari Excel');
            $table->string('judgement', 50)->nullable();
            $table->integer('priority')->nullable();
            
            // Part Numbers
            $table->string('part_no_fg', 50)->nullable();
            $table->string('part_no_parent', 50)->nullable();
            $table->string('part_no_child', 50)->nullable();
            
            // Location Info
            $table->string('store', 50)->nullable();
            $table->string('process', 20)->nullable();
            $table->string('line', 50)->nullable();
            $table->string('rack_no', 50)->nullable();
            
            // Quantity Info
            $table->integer('seq_calc')->nullable();
            $table->integer('qty_kbn')->nullable();
            $table->integer('qty_consume')->nullable();
            $table->integer('qty_lot')->nullable();
            
            // Stock Info
            $table->integer('stock_min')->nullable();
            $table->integer('stock_max')->nullable();
            $table->integer('stock_prod')->nullable();
            $table->integer('stock_store')->nullable();
            $table->integer('stock_realtime')->nullable();
            
            // Calculation Info
            $table->integer('calc_parent')->nullable();
            $table->integer('calc_child')->nullable();
            $table->integer('calc_lot')->nullable();
            $table->integer('calc_prod')->nullable();
            $table->integer('add_prod')->nullable();
            $table->integer('total_prod')->nullable();
            
            // Status & Production
            $table->string('status', 20)->nullable();
            $table->integer('qty_print')->nullable();
            $table->integer('actual')->nullable();
            $table->integer('urutan')->nullable();
            
            // Time Info
            $table->datetime('pulling_time')->nullable();
            $table->integer('lt_pull')->nullable();
            $table->integer('lt_prod')->nullable();
            $table->datetime('max_prod_time')->nullable();
            $table->datetime('start_time')->nullable();
            $table->datetime('end_time')->nullable();
            
            // Lot & Calculation
            $table->string('lot_no', 50)->nullable();
            $table->string('calc_by', 20)->nullable();
            $table->datetime('calc_time')->nullable();
            $table->string('reason', 255)->nullable();
            $table->datetime('update_time_excel')->nullable()->comment('UPDATE_TIME dari Excel');
            
            $table->timestamps();
            
            // Indexes
            $table->index('planning_id');
            $table->index('part_no_fg');
            $table->index('part_no_parent');
            $table->index('part_no_child');
            $table->index('line');
            $table->index('status');
            $table->index('judgement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};