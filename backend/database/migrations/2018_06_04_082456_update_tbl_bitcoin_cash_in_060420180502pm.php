<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblBitcoinCashIn060420180502pm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_bitcoin_cash_in', function (Blueprint $table) {
            $table->renameColumn('bitcoin_to_aba_rate', 'lok_to_btc_rate');
            $table->integer('sale_stage_id')->unsigned();
            $table->foreign('sale_stage_id')->references('sale_stage_id')->on('tbl_sale_stage')->onDelete('cascade');
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
