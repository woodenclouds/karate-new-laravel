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
        Schema::table('tbl_registration', function (Blueprint $table) {
            // Step 1: add column without unique first
            $table->string('registration_code')->nullable()->after('submitted_data');
        });

        // Step 2: update old records with unique values
        $rows = DB::table('tbl_registration')->get();
        $counter = 1;
        foreach ($rows as $row) {
            $code = 'REG' . str_pad($counter, 4, '0', STR_PAD_LEFT);
            DB::table('tbl_registration')
                ->where('id', $row->id)
                ->update(['registration_code' => $code]);
            $counter++;
        }

        // Step 3: alter column to unique + not null
        Schema::table('tbl_registration', function (Blueprint $table) {
            $table->string('registration_code')->unique()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_registration', function (Blueprint $table) {
            $table->dropColumn('registration_code');
        });
    }
};
