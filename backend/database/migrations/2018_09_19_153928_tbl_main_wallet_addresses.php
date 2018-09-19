<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblMainWalletAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_main_wallet_addresses', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('mwallet_id');
            $table->string('mwallet_type');
            $table->string('mwallet_owner');
            $table->string('mwallet_password');
            $table->string('mwallet_email');
            $table->string('mwallet_address');
            $table->string('mwallet_primary')->nullable();
            $table->string('mwallet_secondary')->nullable();
            $table->integer('mwallet_default')->default(0);
            $table->string('g2fa_key')->nullable();
            $table->string('g2fa_ts')->nullable();
            $table->integer('g2fa_enabled')->default(0);
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
