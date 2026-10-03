<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify enum using raw SQL (Laravel doesn't support altering ENUM easily)
        DB::statement("ALTER TABLE `tbl_form_fields` MODIFY `type` ENUM('text', 'email', 'number', 'textarea', 'select', 'radio', 'checkbox', 'file', 'belt') NOT NULL");
    }

    public function down(): void
    {
        // Revert back if needed (remove 'belt')
        DB::statement("ALTER TABLE `tbl_form_fields` MODIFY `type` ENUM('text', 'email', 'number', 'textarea', 'select', 'radio', 'checkbox', 'file', 'belt') NOT NULL");
    }
};

