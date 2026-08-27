<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            // Relational Links (Van and Driver)
            $table->unsignedBigInteger('van_id')->nullable()->after('id');
            $table->unsignedBigInteger('driver_id')->nullable()->after('van_id');

            // Location Details
            $table->string('destination')->nullable()->after('name');
            $table->string('pickup_point')->nullable()->after('destination');

            // Schedule (Start and End)
            $table->date('tour_date')->nullable()->after('duration');
            $table->date('end_date')->nullable()->after('tour_date');

            // Capacity and Extras
            $table->integer('max_passengers')->default(10)->after('price');
            $table->text('inclusions')->nullable()->after('description');

            // Foreign Keys
            $table->foreign('van_id')->references('id')->on('vans')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropForeign(['van_id']);
            $table->dropForeign(['driver_id']);
            $table->dropColumn([
                'van_id', 'driver_id', 'destination', 'pickup_point',
                'tour_date', 'end_date', 'max_passengers', 'inclusions'
            ]);
        });
    }
};
