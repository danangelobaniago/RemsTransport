<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // When the customer cancelled — a cancellation within 3 days (72 hours) of
    // booking gets a full refund, so the time has to be kept, not just the status.
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('status');
        });
        Schema::table('joiner_bookings', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });
        Schema::table('joiner_bookings', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });
    }
};
