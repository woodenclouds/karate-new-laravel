<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify certificate_type on tbl_certificate_templates to VARCHAR(50)
        // to support 'belt', 'competition', 'intro_school', 'championship'
        if (Schema::hasTable('tbl_certificate_templates')) {
            DB::statement("ALTER TABLE `tbl_certificate_templates` MODIFY `certificate_type` VARCHAR(50) NOT NULL DEFAULT 'competition'");
        }

        // Modify certificate_type on tbl_certificate to VARCHAR(50)
        if (Schema::hasTable('tbl_certificate')) {
            DB::statement("ALTER TABLE `tbl_certificate` MODIFY `certificate_type` VARCHAR(50) NOT NULL DEFAULT 'competition'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tbl_certificate_templates')) {
            DB::statement("ALTER TABLE `tbl_certificate_templates` MODIFY `certificate_type` ENUM('belt', 'competition') NOT NULL DEFAULT 'competition'");
        }

        if (Schema::hasTable('tbl_certificate')) {
            DB::statement("ALTER TABLE `tbl_certificate` MODIFY `certificate_type` ENUM('belt', 'competition') NOT NULL DEFAULT 'competition'");
        }
    }
};
