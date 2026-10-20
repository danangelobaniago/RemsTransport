<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * History of price changes: one row per changed value. Global pricing
     * (base fare, price per km, driver fee) has van_id null; van price-range
     * edits carry the van.
     */
    public function up(): void
    {
        Schema::create('price_changes', function (Blueprint $table) {
            $table->id();
            $table->string('field', 40);            // base_fare, price_per_km, driver_fee, price_min, price_max
            $table->unsignedBigInteger('van_id')->nullable();
            $table->string('van_label')->nullable(); // kept even if the van is later deleted
            $table->decimal('old_value', 10, 2)->nullable();
            $table->decimal('new_value', 10, 2);
            $table->string('reason', 1000)->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_changes');
    }
};
