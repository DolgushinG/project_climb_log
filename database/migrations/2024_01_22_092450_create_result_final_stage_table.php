<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultFinalStageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_final_stage', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->integer('category_id');
            $table->integer('event_id');
            $table->string('gender');
            $table->integer('place')->nullable();
            $table->integer('user_id');
            $table->json('result_for_edit_final')->nullable();
            $table->integer('amount_top')->nullable();
            $table->integer('amount_try_top')->nullable();
            $table->integer('amount_zone')->nullable();
            $table->integer('amount_try_zone')->nullable();
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
        Schema::dropIfExists('result_final_stage');
    }
}
