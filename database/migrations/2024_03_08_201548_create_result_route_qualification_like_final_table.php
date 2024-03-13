<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultRouteQualificationLikeFinalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_route_qualification_like_final', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->integer('event_id');
            $table->integer('user_id');
            $table->integer('route_id');
            $table->integer('category_id')->nullable();
            $table->integer('amount_top');
            $table->integer('amount_try_top');
            $table->integer('amount_zone');
            $table->integer('amount_try_zone');
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
        Schema::dropIfExists('result_route_qualification_like_final');
    }
}