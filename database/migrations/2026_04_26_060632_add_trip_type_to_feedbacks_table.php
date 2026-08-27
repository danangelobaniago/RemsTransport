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
    Schema::table('feedbacks', function (Blueprint $table) {
        // This column will store 'private' or 'joiner'
        $table->string('trip_type')->default('private')->after('booking_id');
    });
}

public function down(): void
{
    Schema::table('feedbacks', function (Blueprint $table) {
        $table->dropColumn('trip_type');
    });
}
};
