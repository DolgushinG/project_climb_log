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
            $table->string('subtitle')->nullable();
            $table->string('link');
            $table->string('link_payment')->nullable();
            $table->string('img_payment')->nullable();
            $table->string('amoout_start_price')->nullable();
            $table->integer('is_semifinal');
            $table->integer('is_additional_final')->nullable();
            $table->json('categories');
            $table->integer('choice_transfer')->nullable();
            $table->integer('amount_routes_in_final');
            $table->integer('amount_routes_in_semifinal')->nullable();
            $table->json('transfer_to_next_category')->nullable();
            $table->integer('mode');
            $table->integer('mode_amount_routes')->nullable();
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
