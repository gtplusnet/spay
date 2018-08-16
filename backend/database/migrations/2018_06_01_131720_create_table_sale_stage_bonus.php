<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSaleStageBonus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_sale_stage_bonus', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('sale_stage_bonus_id');
            $table->integer('sale_stage_id')->unsigned();
            $table->foreign('sale_stage_id')->references('sale_stage_id')->on('tbl_sale_stage')->onDelete('cascade');
            $table->double('buy_coin_bonus_from')->default(0);
            $table->double('buy_coin_bonus_to')->default(0);
            $table->double('buy_coin_bonus_percentage')->default(0);
            
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
