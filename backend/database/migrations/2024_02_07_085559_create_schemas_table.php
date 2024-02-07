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
        Schema::create('schemas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rent_term_id');
            $table->integer('daily_amount');
            $table->integer('non_working_days');
            $table->integer('working_days');
            $table->timestamps();

            $table->foreign('rent_term_id')->references('id')->on('rent_terms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schemas');
    }
};
