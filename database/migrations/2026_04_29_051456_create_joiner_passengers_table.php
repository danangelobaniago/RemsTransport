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
    Schema::create('joiner_passengers', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('joiner_booking_id');
        $table->string('name');
        $table->string('contact');
        $table->date('birthday');
        $table->string('gender');
        $table->timestamps();

        // Optional: link it to the main bookings table
        $table->foreign('joiner_booking_id')->references('id')->on('joiner_bookings')->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joiner_passengers');
    }
};
