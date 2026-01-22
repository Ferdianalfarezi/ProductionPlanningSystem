<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('preview_andon', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('planning_id');
            
        });
    }

    public function down()
    {
        Schema::table('preview_andon', function (Blueprint $table) {
            $table->dropColumn(['is_active']);
        });
    }
};