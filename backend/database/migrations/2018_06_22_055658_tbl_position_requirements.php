<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblPositionRequirements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_position_requirements', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('requirement_id');
            $table->integer('member_id')->unsigned();
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->double('token_release')->default(0);
            $table->double('initial_release_percentage')->default(0);
            $table->double('commission')->default(0);
            $table->double('after_purchase_commission')->default(0);
            $table->integer('needed_member')->default(0);
            $table->integer('needed_ambassador')->default(0);
            $table->integer('needed_advisor')->default(0);
            $table->integer('needed_marketing_director')->default(0);
            $table->integer('needed_community_manager')->default(0);
            $table->dateTime('date_created');
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
