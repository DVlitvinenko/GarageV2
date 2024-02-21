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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('park_id');
            $table->unsignedBigInteger('tariff_id');
            $table->unsignedBigInteger('rent_term_id');
            $table->integer('fuel_type');
            $table->integer('transmission_type');
            $table->string('brand');
            $table->string('model');
            $table->integer('year_produced');
            $table->string('car_id');
            $table->text('images');
            $table->boolean('status');
            $table->timestamps();
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
            $table->foreign('tariff_id')->references('id')->on('tariffs')->onDelete('cascade');
            $table->foreign('rent_term_id')->references('id')->on('rent_terms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
