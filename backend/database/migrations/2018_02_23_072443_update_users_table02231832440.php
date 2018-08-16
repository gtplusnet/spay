<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTable02231832440 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->after('password');
            $table->string('sponsor')->nullable()->after('phone_number');
            $table->tinyInteger('verified_mail')->unsigned()->after('sponsor');
            $table->string('email_token')->nullable()->after('verified_mail');
            $table->tinyInteger('is_admin')->unsigned()->after('email_token');
            $table->string('create_ip_address')->after('is_admin');

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
