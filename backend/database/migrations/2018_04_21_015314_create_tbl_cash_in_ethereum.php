<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCashInEthereum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_cash_in_eth', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('cash_in_eth_id')->unsigned();
            $table->dateTime('cash_in_eth_date');
            $table->string('cash_in_reference_address')->nullable();
            $table->double('cash_in_amount')->default(0);
            $table->double('cash_in_fee')->default(0);
            $table->integer('cash_in_by')->unsigned();
            $table->integer('member_log_id')->unsigned();

            $table->foreign('cash_in_by')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('member_log_id')
                  ->references('member_log_id')->on('tbl_member_log')
                  ->onDelete('cascade');
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
