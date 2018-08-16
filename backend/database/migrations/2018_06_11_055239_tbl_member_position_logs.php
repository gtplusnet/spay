<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblMemberPositionLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_member_position_logs', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('member_position_log_id');
            $table->integer('member_position_id')->unsigned();
            $table->foreign('member_position_id')->references('member_position_id')->on('tbl_member_position')->onDelete('cascade');
            $table->integer('member_id')->unsigned();
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('is_previous')->default(0);
            $table->dateTime('created_at');
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
