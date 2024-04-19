<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultQualificationLikeFinalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_qualification_like_final', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->integer('event_id');
            $table->integer('user_id');
            $table->string('gender');
            $table->integer('number_set_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('amount_top')->nullable();
            $table->integer('amount_try_top')->nullable();
            $table->integer('amount_zone')->nullable();
            $table->integer('amount_try_zone')->nullable();
            $table->integer('place')->nullable();
            $table->boolean('active')->nullable();
            $table->boolean('is_paid')->nullable();
            $table->string('bill')->nullable();
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
        Schema::dropIfExists('result_qualification_like_final');
    }
}
