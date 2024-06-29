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
            $table->string('address')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('document')->nullable();
            $table->longText('description')->nullable();
            $table->text('contact')->nullable();
            $table->text('contact_link')->nullable();
            $table->string('image')->nullable();
            $table->string('climbing_gym_name');
            $table->string('climbing_gym_name_eng');
            $table->string('city');
            $table->integer('count_routes')->nullable();
            $table->string('title');
            $table->string('title_eng');
            $table->string('subtitle')->nullable();
            $table->string('link');
            $table->string('admin_link')->nullable();
            $table->string('link_payment')->nullable();
            $table->string('img_payment')->nullable();
            $table->longText('info_payment')->nullable();
            $table->integer('amount_start_price')->nullable();
            $table->boolean('setting_payment')->default(0);
            $table->json('options_amount_price')->nullable();
            $table->integer('is_semifinal')->default(0);
            $table->integer('is_sort_group_final')->default(0);
            $table->integer('is_sort_group_semifinal')->default(0);
            $table->integer('is_france_system_qualification')->default(0);
            $table->float('amount_point_flash')->nullable();
            $table->float('amount_point_redpoint')->nullable();
            $table->integer('amount_the_best_participant')->nullable();
            $table->integer('amount_the_best_participant_to_go_final')->nullable();
            $table->json('categories');
            $table->boolean('is_auto_categories')->default(0);
            $table->json('options_categories')->nullable();
            $table->integer('is_input_birthday')->default(0);
            $table->integer('is_need_sport_category')->default(0);
            $table->integer('choice_transfer')->nullable();
            $table->integer('amount_routes_in_final');
            $table->integer('amount_routes_in_semifinal')->nullable();
            $table->json('transfer_to_next_category')->nullable();
            $table->integer('mode')->nullable();
            $table->integer('mode_amount_routes')->nullable();
            $table->boolean('active')->default(0);
            $table->boolean('is_input_set')->default(0);
            $table->boolean('is_registration_state')->default(1);
            $table->boolean('is_need_pay_for_reg')->default(1);
            $table->boolean('is_access_user_edit_result')->default(0);
            $table->boolean('registration_time_expired')->default(0);
            $table->datetime('datetime_registration_state')->nullable();
            $table->boolean('is_send_result_state')->default(0);
            $table->boolean('is_open_send_result_state')->default(1);
            $table->datetime('datetime_send_result_state')->nullable();
            $table->boolean('is_public')->default(0);
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
