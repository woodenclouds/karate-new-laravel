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
        Schema::create('tbl_certificate', function (Blueprint $table) {
            $table->id();
            $table->string('event_title');
            $table->date('event_date');
            $table->string('venue');
            $table->enum('certificate_type', ['belt', 'competition'])->default('competition');
            $table->foreignId('template_id')->nullable()->constrained('tbl_certificate_templates')->onDelete('set null');
            $table->string('excel_file_path')->nullable();
            $table->json('participants_data')->nullable();
            $table->integer('certificate_count')->default(0);
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_certificate');
    }
};

