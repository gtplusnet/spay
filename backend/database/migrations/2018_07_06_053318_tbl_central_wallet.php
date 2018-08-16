<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblCentralWallet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_central_wallet', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('central_wallet_id');
            $table->string('central_wallet_owner')->nullable();
            $table->string('central_wallet_owner_password');
            $table->string('central_wallet_address');
            $table->string('central_wallet_guid');
            $table->string('google2fa_secret_key')->nullable();
            $table->string('google2fa_timestamp')->nullable();
            $table->integer('google2fa_secret_enabled')->default(1);
            $table->integer('central_wallet_default')->default(0);
            $table->dateTime('date_added')->nullable();
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
