<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Map', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->integer('owner_id');
            $table->string('author');
            $table->string('grade');
            $table->string('route_id');
            $table->integer('x');
            $table->integer('y');
            $table->string('color');
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
        Schema::dropIfExists('Map');
    }
}
