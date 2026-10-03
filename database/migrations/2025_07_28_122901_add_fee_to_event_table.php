<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->decimal('fee', 10, 2)->nullable()->after('venue'); // You can change position if needed
        });
    }

    public function down(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->dropColumn('fee');
        });
    }
};
