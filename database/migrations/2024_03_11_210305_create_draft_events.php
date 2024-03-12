<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDraftEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('draft_events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->string('address')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('document')->nullable();
            $table->longText('description')->nullable();
            $table->text('contact')->nullable();
            $table->string('image')->nullable();
            $table->json('grade_and_amount')->nullable();
            $table->string('climbing_gym_name')->nullable();
            $table->string('climbing_gym_name_eng')->nullable();
            $table->string('city')->nullable();
            $table->integer('count_routes')->nullable();
            $table->string('title')->nullable();
            $table->string('title_eng')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('link')->nullable();
            $table->string('link_payment')->nullable();
            $table->string('img_payment')->nullable();
            $table->longText('info_payment')->nullable();
            $table->integer('amount_start_price')->nullable();
            $table->integer('is_semifinal')->nullable();
            $table->integer('is_additional_final')->nullable();
            $table->integer('is_qualification_counting_like_final')->nullable();
            $table->integer('amount_point_flash')->nullable();
            $table->integer('amount_point_redpoint')->nullable();
            $table->integer('amount_the_best_participant')->nullable();
            $table->json('categories')->nullable();
            $table->integer('is_input_birthday')->nullable();
            $table->integer('is_need_sport_category')->nullable();
            $table->integer('choice_transfer')->nullable();
            $table->integer('amount_routes_in_final')->nullable();
            $table->integer('amount_routes_in_qualification_like_final')->nullable();
            $table->integer('amount_routes_in_semifinal')->nullable();
            $table->json('transfer_to_next_category')->nullable();
            $table->integer('mode')->nullable();
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
        Schema::dropIfExists('draft_events');
    }
}
