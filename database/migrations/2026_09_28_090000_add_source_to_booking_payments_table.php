<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            // 'bookings' (van rentals + tours, which share the bookings table)
            // or 'joiner_bookings' — needed because booking_id alone isn't
            // unique across those two tables, and joiner payments are now
            // logged here too.
            $table->string('source')->default('bookings')->after('booking_id');
        });
    }

    public function down(): void
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
