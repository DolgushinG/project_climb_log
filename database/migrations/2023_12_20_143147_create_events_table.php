<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->string('address');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('document')->nullable();
            $table->longText('description');
            $table->string('image');
            $table->json('grade_and_amount');
            $table->string('climbing_gym_name');
            $table->string('climbing_gym_name_eng');
            $table->string('city');
            $table->integer('count_routes');
            $table->string('title');
            $table->string('title_eng');
            $table->string('subtitle');
            $table->string('link');
            $table->integer('is_semifinal');
            $table->integer('amount_routes_in_final');
            $table->integer('amount_routes_in_semifinal');
            $table->integer('mode');
            $table->boolean('active');
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
        Schema::dropIfExists('events');
    }
}
