<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblKnowyourcustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_knowyourcustomer', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            
            $table->increments('kyc_id');
            $table->integer('kyc_member_id')->unsigned();
            $table->foreign('kyc_member_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('kyc_proof');
            $table->string('kyc_id_number')->nullable();
            $table->string('kyc_type');
            $table->integer('kyc_level');
            $table->string('kyc_remarks')->nullable();
            $table->string('kyc_status')->default('pending');
            $table->string('kyc_id_expiration')->nullable();
            $table->dateTime('kyc_upload_date');

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
