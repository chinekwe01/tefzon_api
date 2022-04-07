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
        Schema::create('gameweek_points', function (Blueprint $table) {
            $table->id();

            $table->string('player_name');
            $table->string('player_position');
            $table->string('image_path')->nullable();
            $table->integer('player_id');
            $table->integer('position_id')->nullable();
            $table->string('position');
            $table->boolean('is_captain')->default(false);
            $table->boolean('is_vice_captain')->default(false);
            $table->boolean('is_starting')->default(false);
            $table->integer('point')->default(0);
            $table->integer('gameweek');
            $table->foreignId('user_id');
            $table->foreignId('gamer_squad_id');
            $table->timestamps();
            $table->softDeletes();
            $table->bigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gameweek_points');
    }
};
