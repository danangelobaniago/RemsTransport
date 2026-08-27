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
    Schema::table('joiner_trips', function (Blueprint $table) {
        $table->date('end_date')->nullable()->after('trip_date');
        $table->string('driver_license_number')->nullable()->after('driver_name');
        $table->string('driver_license_image')->nullable()->after('driver_license_number');
    });
}

public function down(): void
{
    Schema::table('joiner_trips', function (Blueprint $table) {
        $table->dropColumn(['end_date', 'driver_license_number', 'driver_license_image']);
    });
}
};
