<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblForgetPassword extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_forget_password', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('forget_pass_id')->unsigned();
            $table->string('verification_link');
            $table->string('verification_code');
            $table->string('verification_credential');
            $table->integer('is_used')->default(0);
            $table->dateTime('expiration_date');
            $table->dateTime('date_generated');
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
