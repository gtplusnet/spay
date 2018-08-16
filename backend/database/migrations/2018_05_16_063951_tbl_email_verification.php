<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblEmailVerification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_email_verification', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('verification_id')->unsigned();
            $table->string('verification_code');
            $table->integer('verification_user_id');
            $table->string('verification_email');
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
