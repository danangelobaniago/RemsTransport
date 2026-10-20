<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Short-lived reservations of a van + driver for the dates a customer is
     * paying for. Placed (under a row lock) right before the customer is sent
     * to PayMongo, so two customers paying at the same time can't both get
     * the same van/driver. Expired holds are ignored and cleaned up.
     */
    public function up(): void
    {
        Schema::create('booking_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('van_id')->nullable();
            $table->string('plate_number')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('checkout_session_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['expires_at', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_holds');
    }
};
