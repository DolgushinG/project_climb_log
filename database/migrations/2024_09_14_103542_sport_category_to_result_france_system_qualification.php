<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SportCategoryToResultFranceSystemQualification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('result_france_system_qualification', function (Blueprint $table) {
            $table->string('sport_category')->after('gender')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('result_france_system_qualification', function (Blueprint $table) {
            $table->string('sport_category')->after('gender')->nullable();
        });
    }
}
