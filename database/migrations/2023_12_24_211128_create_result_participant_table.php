<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_participant', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->integer('user_id');
            $table->integer('event_id');
            $table->integer('route_id');
            $table->integer('attempt');
            $table->string('grade');
            $table->float('points');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('result_participant');
    }
}
