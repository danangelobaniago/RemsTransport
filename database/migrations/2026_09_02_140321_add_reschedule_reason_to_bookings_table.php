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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('reschedule_reason')->nullable();
            $table->date('original_start_date')->nullable();
            $table->date('original_end_date')->nullable();
            $table->unsignedInteger('reschedule_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['reschedule_reason', 'original_start_date', 'original_end_date', 'reschedule_count']);
        });
    }
};
