<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblOtherInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_other_info', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('info_id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('referrer_id')->unsigned()->nullable();
            $table->foreign('referrer_id')->references('referral_id')->on('tbl_referral')->onDelete('cascade');
            $table->integer('member_position_id')->unsigned();
            $table->foreign('member_position_id')->references('member_position_id')->on('tbl_member_position')->onDelete('cascade');
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
