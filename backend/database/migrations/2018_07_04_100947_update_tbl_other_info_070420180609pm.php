<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblOtherInfo070420180609pm extends Migration
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
            $table->string('google2fa_secret_key')->nullable();
            $table->integer('google2fa_enabled')->default(0);
            $table->string('google2fa_ts')->nullable();
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
