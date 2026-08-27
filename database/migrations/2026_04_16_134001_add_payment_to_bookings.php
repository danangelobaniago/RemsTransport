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
        Schema::table('bookings', function (Blueprint $table) {

            // Payment method (cash, gcash, card)
            $table->string('payment_method')->nullable()->after('status');

            // Payment status (pending, partial, paid)
            $table->string('payment_status')->default('pending')->after('payment_method');

            // Total amount of booking
            $table->decimal('total_amount', 10, 2)->default(0)->after('payment_status');

            // Amount already paid (20% downpayment)
            $table->decimal('amount_paid', 10, 2)->default(0)->after('total_amount');

            // Remaining balance (80%)
            $table->decimal('remaining_balance', 10, 2)->default(0)->after('amount_paid');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {

            $table->dropColumn([
                'payment_method',
                'payment_status',
                'total_amount',
                'amount_paid',
                'remaining_balance'
            ]);

        });
    }
};
