<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {

    $table->id();

    // 🔥 LINK TO USER (IMPORTANT)
    $table->unsignedBigInteger('user_id')->nullable();

    // 🔹 CUSTOMER INFO
    $table->string('name')->nullable();
    $table->string('email')->nullable();
    $table->string('phone')->nullable();

    // 🔹 VAN
    $table->string('van')->nullable();

    // 🔹 TRIP DETAILS
    $table->string('pickup');
    $table->string('destination');
    $table->string('custom_pickup')->nullable();
    $table->string('custom_drop')->nullable();

    // 🔹 DATES
    $table->date('start_date');
    $table->date('end_date');

    // 🔹 BOOKING DETAILS
    $table->unsignedInteger('days')->default(1);
    $table->unsignedInteger('passengers');

    // 🔹 DRIVER
    $table->string('driver')->nullable();

    // 🔹 PRICING
    $table->decimal('base_fare', 10, 2)->default(0);
    $table->decimal('driver_fee', 10, 2)->default(0);
    $table->decimal('total', 10, 2)->default(0);

    // 🔹 STATUS
    $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'downpayment_paid'])
          ->default('pending');

    // 🔹 NOTES
    $table->text('notes')->nullable();

    $table->timestamps();

    $table->unsignedBigInteger('trip_id')->nullable();
    $table->unsignedBigInteger('van_id')->nullable();
    $table->string('payment_id')->nullable();

});
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
