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
        Schema::create('driver_specifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->text('rent_story');
            $table->string('criminal_ids');
            $table->boolean('participation_accident');
            $table->integer('experience');
            $table->integer('count_seams');
            $table->boolean('abandoned_car');
            $table->unsignedBigInteger('republick_id');
            $table->integer('scoring');
            $table->boolean('alcohol');
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('republick_id')->references('id')->on('republicks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_specifications');
    }
};
