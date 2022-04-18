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
        Schema::create('league_overviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('winner_id')->nullable();
            $table->foreign('winner_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->integer('winner_price')->nullable();
            $table->unsignedBigInteger('second_id')->nullable();
            $table->foreign('second_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->integer('second_price')->nullable();
            $table->unsignedBigInteger('third_id')->nullable();
            $table->foreign('third_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade')->nullable();
            $table->integer('third_price')->nullable();
            $table->foreignId('league_id');
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
        Schema::dropIfExists('league_overviews');
    }
};
