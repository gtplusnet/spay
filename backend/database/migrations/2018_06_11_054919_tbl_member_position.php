<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblMemberPosition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_member_position', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('member_position_id');
            $table->string('member_position_name');
            $table->double('member_min_purchase')->default(0);
            $table->double('member_bonus_percentage')->default(0);
            $table->double('member_buy_bonus_percentage')->default(0);
            $table->string('member_purchase_required')->default(0);
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
