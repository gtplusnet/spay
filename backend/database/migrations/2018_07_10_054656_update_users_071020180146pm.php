<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsers071020180146pm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->integer('country_code_id')->nullable()->unsigned()->change();
            $table->string('phone_number')->nullable()->change();
            $table->string('is_admin')->default(0)->change();
            $table->string('phone_number')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('platform')->default('system');
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
