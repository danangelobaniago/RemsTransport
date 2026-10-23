<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pickups outside Metro Manila (but within Luzon) are allowed, but the
        // admin has to agree to them before approving the booking.
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('pickup_outside_ncr')->default(false)->after('pickup');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('pickup_outside_ncr');
        });
    }
};
