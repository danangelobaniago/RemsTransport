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
    Schema::create('feedbacks', function (Blueprint $table) {
        $table->id();

        // Link to the specific booking (Cascade means if booking is deleted, feedback is too)
        $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');

        // Link to the user who wrote the feedback
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

        // We store the driver name here too, so we can calculate driver averages easily
        $table->string('driver_name')->nullable();

        // Rating columns (usually 1 to 5)
        $table->integer('service_rating')->default(5);
        $table->integer('driver_rating')->default(5);

        // The actual text feedback
        $table->text('comment')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
