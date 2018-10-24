<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblReleaseLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_release_logs', function (Blueprint $table)
        {
            $table->increments('release_log_id');
            $table->string('release_type');
            $table->double('release_amount')->default(0);
            $table->double('release_fee')->default(0);
            $table->string('released_from');
            $table->string('released_to');
            $table->string('released_tx_hash');
            $table->dateTime('date_released');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
