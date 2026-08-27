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
        Schema::create('joiner_trips', function (Blueprint $table) {
            $table->id();

            // Trip details
            $table->string('destination'); // Baguio, La Union, etc.
            $table->date('trip_date');

            // Capacity
            $table->integer('total_seats')->default(15);
            $table->integer('available_seats')->default(15);

            // Pricing
            $table->decimal('price_per_seat', 10, 2);

            $table->string('van')->nullable();

            // Status
            $table->enum('status', ['open', 'full', 'cancelled'])->default('open');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joiner_trips');
    }
};
