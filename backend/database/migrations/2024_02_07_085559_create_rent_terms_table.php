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
        Schema::create('rent_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('park_id');
            $table->decimal('deposit_amount_daily', 10, 2);
            $table->decimal('deposit_amount_total', 10, 2);
            $table->integer('minimum_period_days');
            $table->string('name');
            $table->boolean('is_buyout_possible');
            $table->timestamps();

            $table->foreign('park_id')->references('id')->on('parks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_terms');
    }
};
