<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTblFormReplaceEventIdWithCategoryId extends Migration
{
    public function up()
    {
        Schema::table('tbl_form', function (Blueprint $table) {
            // Drop FK first, then drop the column
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');

            // Add category_id with FK to tbl_category
            $table->unsignedBigInteger('category_id')->after('id');
            $table->foreign('category_id')->references('id')->on('tbl_category')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('tbl_form', function (Blueprint $table) {
            // Drop new FK then column
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            // Re-add event_id and its FK back to event table
            $table->unsignedBigInteger('event_id')->after('id');
            $table->foreign('event_id')->references('id')->on('event')->onDelete('cascade');
        });
    }
}
