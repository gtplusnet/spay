<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblOtherInfo062220180449pm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_other_info', function (Blueprint $table)
        {
            $table->integer('registration_stage_id')->unsigned()->nullable();
            $table->foreign('registration_stage_id')->references('sale_stage_id')->on('tbl_sale_stage')->onDelete('cascade');
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
