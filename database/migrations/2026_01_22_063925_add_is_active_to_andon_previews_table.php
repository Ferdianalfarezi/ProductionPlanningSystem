<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_add_is_active_to_andon_previews_table.php
public function up()
{
    Schema::table('preview_andon', function (Blueprint $table) {
        $table->boolean('is_active')->default(1)->after('id'); // 1 = active, 0 = inactive
    });
}

public function down()
{
    Schema::table('preview_andon', function (Blueprint $table) {
        $table->dropColumn('is_active');
    });
}
};
