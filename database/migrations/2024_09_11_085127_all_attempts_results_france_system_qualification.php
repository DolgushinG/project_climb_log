<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllAttemptsResultsFranceSystemQualification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('result_route_france_system_qualification', function (Blueprint $table) {
            $table->integer('all_attempts')->after('number_set_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('result_route_france_system_qualification', function (Blueprint $table) {
            $table->integer('all_attempts')->after('number_set_id')->default(0);
        });
    }
}
