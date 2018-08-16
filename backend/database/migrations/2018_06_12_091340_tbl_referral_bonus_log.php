<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblReferralBonusLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_referral_bonus_log', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('referral_bonus_log_id');
            $table->integer('member_log_from')->unsigned();
            $table->foreign('member_log_from')->references('member_log_id')->on('tbl_member_log')->onDelete('cascade');
            $table->integer('member_log_to')->unsigned();
            $table->foreign('member_log_to')->references('member_log_id')->on('tbl_member_log')->onDelete('cascade');
            $table->dateTime('referral_bonus_log_date');
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
