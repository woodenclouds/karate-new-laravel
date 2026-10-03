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
            $table->boolean('is_attended')->default(true)->after('status');
            $table->string('rank')->nullable()->after('is_attended');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_registration', function (Blueprint $table) {
            $table->dropColumn(['is_attended', 'rank']);
        });
    }
};
