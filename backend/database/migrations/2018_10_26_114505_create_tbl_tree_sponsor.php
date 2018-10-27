<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblTreeSponsor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_tree_sponsor', function (Blueprint $table)
        {
            $table->increments('sponsor_id');
            $table->integer('sponsor_parent_id')->unsigned();
            $table->integer('sponsor_child_id')->unsigned();
            $table->integer('sponsor_level');

            $table->foreign('sponsor_parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sponsor_child_id')->references('id')->on('users')->onDelete('cascade');
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
