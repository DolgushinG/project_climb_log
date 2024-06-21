<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('middlename');
            $table->string('lastname')->nullable();
            $table->string('avatar')->nullable();
            $table->string('gender')->nullable();
            $table->string('birthday')->nullable();
            $table->string('city')->nullable();
            $table->string('contact')->nullable();
            $table->string('year')->nullable();
            $table->string('team')->nullable();
            $table->string('is_notify_about_new_event')->nullable();
            $table->string('is_notify_about_where_was_participant_event')->nullable();
            $table->unsignedBigInteger('telegram_id')->nullable();
            $table->unsignedBigInteger('vkontakte_id')->nullable();
            $table->unsignedBigInteger('yandex_id')->nullable();
            $table->string('category')->nullable();
            $table->string('skill')->nullable();
            $table->string('sport_category')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
