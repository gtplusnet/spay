<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblMemberPosition062220180151pm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_position', function (Blueprint $table)
        {
            $table->dropColumn('member_purchase_required');
            $table->double('token_release')->default(0);
            $table->double('initial_release_percentage')->default(0);
            $table->double('commission')->default(0);
            $table->integer('needed_member')->default(0);
            $table->integer('needed_ambassador')->default(0);
            $table->integer('needed_advisor')->default(0);
            $table->integer('needed_marketing_director')->default(0);
            $table->integer('needed_community_manager')->default(0);
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
