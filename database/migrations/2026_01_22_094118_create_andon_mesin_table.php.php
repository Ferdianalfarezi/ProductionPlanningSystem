// database/migrations/[timestamp]_create_andon_mesin_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('andon_mesin', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('mesin_id');
            $table->string('mesin_nama');
            $table->enum('shift', ['1', '2']);
            $table->date('tanggal');
            $table->json('data_json');
            $table->timestamp('submitted_at');
            $table->timestamps();
            
            // Satu mesin hanya bisa submit 1x per shift per tanggal
            $table->unique(['mesin_id', 'shift', 'tanggal']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('andon_mesin');
    }
};