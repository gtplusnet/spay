<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTblMemberAddress841652018 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {  
        DB::statement("ALTER TABLE `tbl_member_address`CHANGE `address_api_password` `address_api_password` text COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `address_actual_balance`");
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
