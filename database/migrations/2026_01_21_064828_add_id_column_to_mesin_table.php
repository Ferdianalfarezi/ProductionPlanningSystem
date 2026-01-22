<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Ubah 'no' menjadi non auto_increment (tetap primary key dulu)
        DB::statement('ALTER TABLE `mesin` MODIFY `no` INT UNSIGNED NOT NULL');
        
        // Step 2: Sekarang baru drop primary key
        DB::statement('ALTER TABLE `mesin` DROP PRIMARY KEY');
        
        // Step 3: Tambah kolom 'id' sebagai primary key auto_increment
        DB::statement('ALTER TABLE `mesin` ADD `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        
        // Step 4: Jadikan 'no' sebagai unique
        DB::statement('ALTER TABLE `mesin` ADD UNIQUE KEY `mesin_no_unique` (`no`)');
    }

    public function down(): void
    {
        // Drop kolom id dan unique constraint
        DB::statement('ALTER TABLE `mesin` DROP COLUMN `id`');
        DB::statement('ALTER TABLE `mesin` DROP INDEX `mesin_no_unique`');
        
        // Kembalikan 'no' sebagai primary key auto_increment
        DB::statement('ALTER TABLE `mesin` MODIFY `no` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
    }
};