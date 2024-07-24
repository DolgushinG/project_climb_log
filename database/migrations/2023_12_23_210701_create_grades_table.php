<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->integer('event_id');
            $table->integer('place_id')->nullable();
            $table->integer('area_id')->nullable();
            $table->integer('country_id')->nullable();
            $table->json('rocks_id')->nullable();
            $table->integer('count_routes')->nullable();
            $table->json('grade_and_amount')->nullable();
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
        Schema::dropIfExists('grades');
    }
}
