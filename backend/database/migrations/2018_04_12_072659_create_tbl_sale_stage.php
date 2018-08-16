<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblSaleStage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_sale_stage', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('sale_stage_id')->unsigned();
            $table->string('sale_stage_type');
            $table->date('sale_stage_start_date')->nullable();
            $table->date('sale_stage_end_date')->nullable();
            $table->double('sale_stage_discount');
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
