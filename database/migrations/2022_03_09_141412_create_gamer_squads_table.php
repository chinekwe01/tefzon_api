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
        Schema::create('gamer_squads', function (Blueprint $table) {
            $table->id();
            $table->integer('squad_no')->nullable();
            $table->string('player_name')->nullable();
            $table->string('player_position')->nullable();
            $table->integer('player_id')->nullable();
            $table->integer('position_id')->nullable();
            $table->integer('value')->nullable();
            $table->integer('team_id')->nullable();
            $table->string('team')->nullable();
            $table->boolean('is_captain')->default(false);
            $table->boolean('is_vice_captain')->default(false);
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_injured')->default(false);
            $table->foreignId('user_id')->nullable();
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
        Schema::dropIfExists('gamer_squads');
    }
};
