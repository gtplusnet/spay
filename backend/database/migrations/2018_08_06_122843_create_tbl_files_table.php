<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_files', function (Blueprint $table) {
            $table->increments('file_id');
            $table->string('file_name');
            $table->string('file_category');
            $table->string('file_type');
            $table->string('file');
            $table->dateTime('date_added');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_files');
    }
}
