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
    Schema::table('joiner_bookings', function (Blueprint $table) {
        if (!Schema::hasColumn('joiner_bookings', 'payment_status')) {
            $table->string('payment_status')->default('pending')->after('status');
        }
        if (!Schema::hasColumn('joiner_bookings', 'amount_paid')) {
            $table->decimal('amount_paid', 10, 2)->default(0)->after('payment_status');
        }
        if (!Schema::hasColumn('joiner_bookings', 'total_price')) {
            $table->decimal('total_price', 10, 2)->default(0)->after('amount_paid');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('joiner_bookings', function (Blueprint $table) {
            //
        });
    }
};
