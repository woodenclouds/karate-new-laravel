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
        Schema::create('tbl_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('tbl_form')->onDelete('cascade');
            $table->string('label'); // "Full Name", "Age"
            $table->enum('type', ['text', 'email', 'number', 'textarea', 'select', 'radio', 'checkbox', 'file', 'date']);
            $table->boolean('required')->default(false);
            $table->json('options')->nullable(); // for dropdown/radio/checkbox fields
            $table->integer('order')->default(0); // sorting fields
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_form_fields');
    }
};