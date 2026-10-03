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
        Schema::create('event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('tbl_category')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date'); // Only the date
            $table->time('event_time'); // Specific time of the event
            $table->string('venue');    // Location of the event
            $table->timestamps();       // created_at and updated_at
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};