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
        Schema::table('tbl_registration', function (Blueprint $table) {
            $table->string('entered_by')->nullable()->after('amount')->comment('admin/user - who entered the registration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_registration', function (Blueprint $table) {
            $table->dropColumn('entered_by');
        });
    }
};
