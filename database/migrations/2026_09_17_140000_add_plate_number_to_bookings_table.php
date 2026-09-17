<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // The one van identifier every table (bookings, joiner_trips,
            // tour_packages) can reliably match on — unique, unlike a van's
            // model name, and actually populated at insert time (bookings.van
            // never was). Used by BookingValidator::checkAvailability().
            $table->string('plate_number')->nullable()->after('van_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('plate_number');
        });
    }
};
