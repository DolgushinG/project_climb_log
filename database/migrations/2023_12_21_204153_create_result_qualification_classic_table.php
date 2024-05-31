<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultQualificationClassicTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_qualification_classic', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->integer('event_id');
            $table->string('gender');
            $table->integer('number_set_id')->nullable();
            $table->integer('user_id');
            $table->integer('category_id')->nullable();
            $table->float('points')->nullable();
            $table->integer('user_place')->nullable();
            $table->boolean('active');
            $table->integer('amount_start_price')->nullable();
            $table->boolean('is_paid')->default(0);
            $table->string('bill')->nullable();
            $table->json('result_for_edit')->nullable();
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
        Schema::dropIfExists('result_qualification_classic');
    }
}
