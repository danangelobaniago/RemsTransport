<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per refund the admin sends back (manually) for a rejected or
        // cancelled booking. The proof is a photo of the transfer receipt, which
        // is also what the customer gets. `source` mirrors booking_payments:
        // 'bookings' (van/tour) or 'joiner_bookings'.
        Schema::create('booking_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('source', 30)->default('bookings');
            $table->decimal('amount', 10, 2);
            $table->string('proof_path');                 // photo of the refund receipt (public disk)
            $table->string('notes', 500)->nullable();     // message to the customer
            $table->unsignedBigInteger('refunded_by')->nullable(); // admin user id
            $table->timestamp('refunded_at');
            $table->timestamps();

            $table->unique(['booking_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_refunds');
    }
};
