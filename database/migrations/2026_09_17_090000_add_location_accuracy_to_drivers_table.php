<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // GPS accuracy radius in meters, as reported by the browser's
            // Geolocation API (position.coords.accuracy). Lets the admin map
            // show a confidence circle and judge how much to trust a fix.
            $table->unsignedInteger('location_accuracy')->nullable()->after('current_lng');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('location_accuracy');
        });
    }
};
