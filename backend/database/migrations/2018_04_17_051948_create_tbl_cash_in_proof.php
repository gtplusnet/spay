<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCashInProof extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_member_address', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('member_address_id');
            $table->integer('member_id')->unsigned();
            $table->integer('coin_id')->unsigned();
            $table->string('member_address', 100)->comment('Specific digit address when sending crypto-currency.')->unique();
            $table->double('address_balance')->default(0)->comment('Balance of Address in SATOSHI FORMAT');
            $table->double('address_actual_balance')->default(0)->comment('Actual Balance of Address in SATOSHI FORMAT');
            $table->string('address_api_password', 150);
            $table->string('address_api_reference', 150);
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('coin_id')->references('coin_id')->on('tbl_coin')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('tbl_member_log', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('member_log_id');
            $table->integer('member_address_id')->unsigned();
            $table->foreign('member_address_id')->references('member_address_id')->on('tbl_member_address')->onDelete('cascade');
            $table->string('log_type')->default('transfer')->comment("reward, transfer, lending");
            $table->string('log_mode')->comment("receive, send");
            $table->double('log_amount');
            $table->double('log_transaction_fee');
            $table->double('log_net_amount');
            $table->dateTime('log_time');
            $table->string('log_status')->default('pending')->comment("pending, rejected, confirmed");
            $table->string('log_message', 255);
            $table->tinyInteger('is_viewed')->default(0);
            $table->string('log_method')->comment("cash in, cash out");
        });

        Schema::create('tbl_cash_in_proof', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
          
            $table->increments('cash_in_proof_id')->unsigned();
            $table->dateTime('cash_in_proof_date');
            $table->integer('cash_in_method_id')->unsigned();
            $table->string('cash_in_proof_image')->nullable();
            $table->string('cash_in_reference_number')->nullable();
            $table->double('cash_in_amount')->default(0);
            $table->double('cash_in_fee')->default(0);
            $table->integer('cash_in_by')->unsigned();
            $table->integer('member_log_id')->unsigned();

            $table->foreign('cash_in_by')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('cash_in_method_id')
                  ->references('cash_in_method_id')->on('tbl_cash_in_method')
                  ->onDelete('cascade');

            $table->foreign('member_log_id')
                  ->references('member_log_id')->on('tbl_member_log')
                  ->onDelete('cascade');
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
