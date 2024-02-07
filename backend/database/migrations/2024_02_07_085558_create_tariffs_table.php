<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->string('class');
            $table->unsignedBigInteger('park_id');
            $table->unsignedBigInteger('city_id');
            $table->string('criminal_ids');
            $table->boolean('participation_accident');
            $table->integer('experience');
            $table->integer('max_cont_seams');
            $table->boolean('abandoned_car');
            $table->integer('min_scoring');
            $table->string('forbidden_republic_ids');
            $table->boolean('alcohol');
            $table->timestamps();

            $table->foreign('park_id')->references('id')->on('parks')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
