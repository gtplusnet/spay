<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCashInMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_cash_in_method', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('cash_in_method_id')->unsigned();
            $table->string('cash_in_method_name');
            $table->double('cash_in_method_fee');
            $table->text('cash_in_method_header');
            $table->text('cash_in_account_name');
            $table->text('cash_in_account_number');
            $table->text('cash_in_method_payment_rule');
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
