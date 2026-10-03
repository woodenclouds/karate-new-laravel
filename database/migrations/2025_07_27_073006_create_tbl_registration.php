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
        Schema::create('tbl_registration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('event');
            $table->json('submitted_data'); // actual form submission (matches event->form_fields)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_registration');
    }
};