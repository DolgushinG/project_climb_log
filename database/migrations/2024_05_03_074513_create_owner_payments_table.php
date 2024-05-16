<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwnerPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('owner_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->integer('event_id');
            $table->integer('amount_for_pay')->nullable();
            $table->string('bill')->nullable();
            $table->string('request_for_payment')->nullable();
            $table->boolean('is_paid')->default(0);
            $table->string('event_title');
            $table->integer('amount_participant');
            $table->integer('amount_start_price');
            $table->float('amount_cost_for_service');
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
        Schema::dropIfExists('owner_payments');
    }
}
