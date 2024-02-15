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
            $table->string('year')->nullable();
            $table->string('city')->nullable();
            $table->string('team')->nullable();
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
