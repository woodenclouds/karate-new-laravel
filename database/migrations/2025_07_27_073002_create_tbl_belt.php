<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('tbl_belt', function (Blueprint $table) {
        $table->id(); // Auto-incrementing primary key
        $table->string('from_belt');
        $table->string('to_belt');
        $table->decimal('fees', 10, 2);
        $table->timestamps(); // created_at and updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('tbl_belt');
    }
};