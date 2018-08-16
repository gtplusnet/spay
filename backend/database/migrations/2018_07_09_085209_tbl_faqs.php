<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblFaqs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_faqs', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';
            
            $table->increments("faq_id");
            $table->string("faq_category");
            $table->string("faq_question");
            $table->text("faq_answer");
            $table->integer("is_active")->default(1);
            $table->dateTime("date_added");
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
