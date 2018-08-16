<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewBusinessApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_business_application', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            
            $table->increments('business_application_id');
            $table->string('business_company_legal_name')->nullable();
            $table->string('business_line')->nullable();
            $table->string('business_director_name');
            $table->string('position');
            $table->string('business_country');
            $table->integer('business_number_of_employees')->default(0)->nullable();
            $table->integer('business_annual_revenue')->default(0)->nullable();
            $table->text('business_supporting_documents')->nullable();
            $table->string('business_pref_token_name');
            $table->string('preferred_ico_name');
            $table->string('business_contact_number');
            $table->string('business_contact_email');
            $table->text('business_remarks')->nullable();
            $table->dateTime('business_date_submitted');
            $table->integer('is_viewed')->default(0);

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
