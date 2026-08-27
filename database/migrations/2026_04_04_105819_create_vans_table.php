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
        Schema::create('vans', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name'); // e.g. Toyota Hiace
            $table->string('plate_number')->unique();

            // Capacity
            $table->integer('seats');

            // Pricing
            $table->decimal('price_min', 10, 2);
            $table->decimal('price_max', 10, 2);

            // Specs
            $table->string('transmission'); // Manual / Automatic

            // Image (optional)
            $table->string('image')->nullable();

            // Status (available / unavailable / maintenance)
            $table->string('status')->default('available');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vans');
    }
};
