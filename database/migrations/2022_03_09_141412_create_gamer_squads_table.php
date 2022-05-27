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
            $table->string('player_name');
            $table->string('player_position');
            $table->integer('player_id');
            $table->integer('position_id');
            $table->integer('value');
            $table->integer('team_id');
            $table->string('team');
            $table->boolean('is_captain')->default(false);
            $table->boolean('is_vice_captain')->default(false);
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_injured')->default(false);
            $table->foreignId('user_id');
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
        Schema::dropIfExists('gamer_squads');
    }
};
