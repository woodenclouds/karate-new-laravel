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
        Schema::table('event', function (Blueprint $table) {
            $table->decimal('additional_fee', 10, 2)->default(0)->after('fee');
        });
    }
    
    public function down(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->dropColumn('additional_fee');
        });
    }
};
