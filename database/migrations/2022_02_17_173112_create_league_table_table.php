<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('league_table', function (Blueprint $table) {
            $table->id();
            $table->integer('rank');
            $table->integer('points');
            $table->integer('gameweek');
            $table->foreignId('user_id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('league_id')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('league_table');
    }
};
