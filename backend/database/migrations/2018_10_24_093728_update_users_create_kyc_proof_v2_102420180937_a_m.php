<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersCreateKycProofV2102420180937AM extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->string('gender')->default('Male');
            $table->string('nationality')->nullable();
            $table->text('address_line1')->nullable();
            $table->text('address_line2')->nullable();
        });

        Schema::create('tbl_kyc_proof_v2', function (Blueprint $table)
        {
            $table->increments('proof_id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('category');
            $table->string('id_type');
            $table->string('id_link');
            $table->string('status')->default('pending');
            $table->datetime('submitted_at');
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
