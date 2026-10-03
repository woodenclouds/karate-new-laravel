<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'tel' while keeping 'date'
        DB::statement("ALTER TABLE `tbl_form_fields` MODIFY `type` 
            ENUM('text', 'email', 'number', 'textarea', 'select', 'radio', 'checkbox', 'file', 'belt', 'date', 'tel') NOT NULL");
    }

    public function down(): void
    {
        // Rollback (remove 'tel' but keep 'date')
        DB::statement("ALTER TABLE `tbl_form_fields` MODIFY `type` 
            ENUM('text', 'email', 'number', 'textarea', 'select', 'radio', 'checkbox', 'file', 'belt', 'date') NOT NULL");
    }
};
