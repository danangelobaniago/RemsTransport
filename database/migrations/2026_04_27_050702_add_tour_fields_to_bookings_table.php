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
    Schema::table('bookings', function (Blueprint $table) {
        // Add tour_id after user_id
        $table->unsignedBigInteger('tour_id')->nullable()->after('user_id');

        // Add package_name after tour_id
        $table->string('package_name')->nullable()->after('tour_id');

        // IMPORTANT: We use 'after' columns that definitely exist (like user_id or id)
        // Add downpayment and passenger_count at the end
        $table->decimal('downpayment', 10, 2)->nullable();
        $table->integer('passenger_count')->nullable();

        // Add booking_type to distinguish between 'tour' and 'private'
        $table->string('booking_type')->default('tour')->after('status');
    });
}

public function down()
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->dropColumn(['tour_id', 'package_name', 'downpayment', 'passenger_count', 'booking_type']);
    });
}
};
