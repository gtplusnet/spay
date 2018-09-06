<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblMemberLog090520181201pm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_log', function(Blueprint $table)
        {
            $table->integer('cash_in_method')->unsigned()->nullable();
            $table->foreign('cash_in_method')->references('cash_in_method_id')->on('tbl_cash_in_method')->onDelete('cascade');
            $table->longText('cash_in_proof_img')->nullable();
            $table->string('cash_in_proof_tx')->nullable();
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
