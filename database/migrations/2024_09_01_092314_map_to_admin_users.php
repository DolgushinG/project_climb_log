<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MapToAdminUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('admin.database.users_table'), function (Blueprint $table) {
            $table->string('map')->after('is_access_to_create_event')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('admin.database.users_table'), function (Blueprint $table) {
            $table->string('map')->after('is_access_to_create_event')->nullable();
        });
    }
}
