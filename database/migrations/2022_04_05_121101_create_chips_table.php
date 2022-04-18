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
        Schema::create('chips', function (Blueprint $table) {
            $table->id();
            $table->integer('free_hit')->default(1);
            $table->integer('bench_boost')->default(1);
            $table->integer('wildcard')->default(2);
            $table->integer('triple_captain')->default(1);
            $table->integer('free_transfer')->default(1);
            $table->bigInteger('budget')->default(100000000);
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
        Schema::dropIfExists('chips');
    }
};
