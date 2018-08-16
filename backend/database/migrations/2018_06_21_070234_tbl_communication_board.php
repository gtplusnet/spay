<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblCommunicationBoard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_communication_board', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            
            $table->increments('communication_board_id');
            $table->string('communication_board_title');
            $table->string('communication_board_subtitle');
            $table->text('communication_board_description');
            $table->date('communication_board_start_date');
            $table->date('communication_board_end_date');
            $table->string('communication_board_thumbnail')->default("lokalize/kycphotos/8HUiSKq8RoSYo5GrrEL5gtwD2wqTpqMiqfmG8TnC.png");
            $table->string('communication_board_banner')->default("lokalize/kycphotos/8HUiSKq8RoSYo5GrrEL5gtwD2wqTpqMiqfmG8TnC.png");
            $table->string('communication_board_career_member')->default(0);
            $table->string('communication_board_career_community_manager')->default(0);
            $table->string('communication_board_career_marketing_director')->default(0);
            $table->string('communication_board_career_ambassador')->default(0);
            $table->string('communication_board_career_advisor')->default(0);
            $table->datetime('insert_date');
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
