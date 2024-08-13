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
            $table->integer('is_other_event')->default(0);
            $table->integer('category_id')->nullable();
            $table->integer('global_category_id')->nullable();
            $table->json('last_category_after_merged')->nullable();
            $table->float('points')->nullable();
            $table->integer('user_place')->nullable();
            $table->integer('user_global_place')->nullable();
            $table->json('last_user_place_after_merged')->nullable();
            $table->float('global_points')->nullable();
            $table->json('last_points_after_merged')->nullable();
            $table->boolean('active');
            $table->integer('amount_start_price')->nullable();
            $table->json('helper_amount')->nullable();
            $table->boolean('is_paid')->default(0);
            $table->boolean('is_recheck')->default(0);
            $table->string('bill')->nullable();
            $table->string('document')->nullable();
            $table->json('products_and_discounts')->nullable();
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
