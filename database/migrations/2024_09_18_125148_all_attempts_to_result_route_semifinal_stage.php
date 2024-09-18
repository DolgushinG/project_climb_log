<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllAttemptsToResultRouteSemifinalStage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('result_route_semifinal_stage', function (Blueprint $table) {
            $table->integer('all_attempts')->after('category_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('result_route_semifinal_stage', function (Blueprint $table) {
            $table->integer('all_attempts')->after('category_id')->default(0);
        });
    }
}
