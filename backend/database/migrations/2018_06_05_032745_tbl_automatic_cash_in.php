<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAutomaticCashIn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_automatic_cash_in', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('automatic_cash_in_id');
            $table->integer('member_log_id')->unsigned();
            $table->double('exchange_rate')->default(0);
            $table->double('amount_requested')->default(0);
            $table->double('sale_stage_discount')->default(0);
            $table->double('sale_stage_bonus')->default(0);
            $table->dateTime('expiration_date');
            $table->dateTime('date_requested');
            $table->foreign('member_log_id')->references('member_log_id')->on('tbl_member_log')->onDelete('cascade');
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
