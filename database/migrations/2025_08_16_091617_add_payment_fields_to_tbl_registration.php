<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::table('tbl_registration', function (Blueprint $table) {
            $table->string('status')->default('pending'); // pending | paid
            $table->string('payment_id')->nullable();    // Razorpay payment id
        });
    }

    public function down(): void
    {
        Schema::table('tbl_registration', function (Blueprint $table) {
            $table->dropColumn(['status', 'payment_id']);
        });
    }
};
