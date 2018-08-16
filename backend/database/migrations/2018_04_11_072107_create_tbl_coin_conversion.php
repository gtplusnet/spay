<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCoinConversion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_coin_conversion', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('coin_conversion_id');
            $table->integer('coin_from')->unsigned();
            $table->integer('coin_to')->unsigned();
            $table->double('conversion_multiplier');

            $table->foreign('coin_from')
                  ->references('coin_id')->on('tbl_coin')
                  ->onDelete('cascade');

            $table->foreign('coin_to')
                  ->references('coin_id')->on('tbl_coin')
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
