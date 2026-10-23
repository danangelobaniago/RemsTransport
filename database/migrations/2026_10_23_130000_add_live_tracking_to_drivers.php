<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Secret device id for the Traccar Client app (background tracking)
            $table->string('tracking_token', 40)->nullable()->unique()->after('location_updated_at');
            $table->string('location_source', 10)->nullable()->after('tracking_token'); // portal / app
            $table->decimal('location_speed', 6, 2)->nullable()->after('location_source');   // m/s
            $table->decimal('location_heading', 5, 1)->nullable()->after('location_speed');  // degrees
        });

        // Recent positions, for the trail drawn on the admin live map.
        // Only the last 2 days are kept (pruned as new points arrive).
        Schema::create('driver_location_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->unsignedInteger('accuracy')->nullable();
            $table->timestamp('recorded_at');

            $table->index(['driver_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_location_points');

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropUnique(['tracking_token']);
            $table->dropColumn(['tracking_token', 'location_source', 'location_speed', 'location_heading']);
        });
    }
};
