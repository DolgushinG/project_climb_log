<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LikeDislikes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('like_dislikes', function (Blueprint $table) {
            $table->id();
            $table->integer('posts_id');
            $table->integer('all_gyms_id')->nullable();
            $table->string('user_ip')->nullable();
            $table->smallInteger('like')->default(0);
            $table->smallInteger('dislike')->default(0);
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
        Schema::dropIfExists('like_dislikes');
    }
}
