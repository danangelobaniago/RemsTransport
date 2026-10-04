<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit log of vehicle / driver / route changes made by the admin under
     * Terms & Conditions §6 ("Vehicle, Route, and Driver Changes").
     * trip_type + trip_id point at bookings (rental), tour_packages (tour)
     * or joiner_trips (joiner).
     */
    public function up(): void
    {
        Schema::create('trip_changes', function (Blueprint $table) {
            $table->id();
            $table->string('trip_type', 20);
            $table->unsignedBigInteger('trip_id');
            $table->string('old_van')->nullable();
            $table->string('new_van')->nullable();
            $table->string('old_driver')->nullable();
            $table->string('new_driver')->nullable();
            $table->string('old_pickup')->nullable();
            $table->string('new_pickup')->nullable();
            $table->string('old_destination')->nullable();
            $table->string('new_destination')->nullable();
            $table->string('reason', 40);
            $table->text('details');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('customers_notified')->default(0);
            $table->timestamps();

            $table->index(['trip_type', 'trip_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_changes');
    }
};
