<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblLogin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_login', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('login_id');
            $table->integer('id')->unsigned();
            $table->string('login_key');
            $table->string('login_regex')->comment("E.G: Firefox 0.9, Chrome 2.0");
            $table->string('login_parent')->comment("E.G: Firefox 0.9, Chrome 2.0");
            $table->string('login_platform')->comment("E.G: WinXP, iOS, Win10, Android5.0");
            $table->string('login_ip')->comment("E.G 11.11.11.11"); //can be used for strict IP Mode
            $table->dateTime('login_date');
            $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
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
