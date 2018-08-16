<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblCountryCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_country_codes', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments('country_code_id')->unsigned();
            $table->string('country_code_abbr');
            $table->string('country_code');
            $table->string('country')->nullable();
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
