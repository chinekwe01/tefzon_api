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
        Schema::create('live_leagues', function (Blueprint $table) {
            $table->id();
            $table->string('league_id');
            $table->string('current_season_id');
            $table->string('previous_season')->nullable();
            $table->string('name');
            $table->string('current_round_id')->nullable();
            $table->string('current_stage_id')->nullable();
            $table->string('country_id');
            $table->string('logo_path');
            $table->boolean('is_cup');
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
        Schema::dropIfExists('live_leagues');
    }
};
