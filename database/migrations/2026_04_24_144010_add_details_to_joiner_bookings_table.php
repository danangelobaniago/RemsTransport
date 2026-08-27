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
        Schema::table('joiner_bookings', function (Blueprint $table) {
    $table->string('passenger_name')->after('joiner_trip_id');
    $table->string('passenger_contact')->after('passenger_name');
    $table->string('payment_id')->nullable()->after('passenger_contact');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('joiner_bookings', function (Blueprint $table) {
            //
        });
    }
};
