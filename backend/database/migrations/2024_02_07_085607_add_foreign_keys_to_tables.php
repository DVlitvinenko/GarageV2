<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->foreign('park_id')->references('id')->on('parks')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
        });

        Schema::table('managers', function (Blueprint $table) {
            $table->foreign('park_id')->references('id')->on('parks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
        });

        Schema::table('driver_specifications', function (Blueprint $table) {
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('republick_id')->references('id')->on('republicks')->onDelete('cascade');
        });

        Schema::table('tariffs', function (Blueprint $table) {
            $table->foreign('park_id')->references('id')->on('parks')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->foreign('forbidden_republic_ids')->references('id')->on('republicks')->onDelete('cascade');
        });

        Schema::table('driver_docs', function (Blueprint $table) {
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });

        Schema::table('cars', function (Blueprint $table) {
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
            $table->foreign('tariff_id')->references('id')->on('tariffs')->onDelete('cascade');
            $table->foreign('rent_term_id')->references('id')->on('rent_terms')->onDelete('cascade');
        });

        Schema::table('schemas', function (Blueprint $table) {
            $table->foreign('rent_term_id')->references('id')->on('rent_terms')->onDelete('cascade');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('car_id')->references('id')->on('cars')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('park_id')->references('id')->on('parks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
