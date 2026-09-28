<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('joiner_passengers', function (Blueprint $table) {
            // The joiner passenger form now collects First/Middle/Last Name
            // separately (matching the van/tour passengers table), instead of
            // one combined "name" field. The existing `name` column is kept
            // and still populated (as the full concatenated name) since
            // admin/joiner_passengers.blade.php and driver/joiner-manifest.blade.php
            // read it directly — nullable here only because rows created
            // before this migration won't have these split fields.
            $table->string('first_name')->nullable()->after('joiner_booking_id');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
        });
    }

    public function down(): void
    {
        Schema::table('joiner_passengers', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });
    }
};
