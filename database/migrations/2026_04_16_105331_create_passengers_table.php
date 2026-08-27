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
    Schema::create('passengers', function (Blueprint $table) {
        $table->id();

        // 🔗 LINK TO BOOKINGS
        $table->unsignedBigInteger('booking_id');

        // 👤 PASSENGER NAME
        $table->string('first_name');
        $table->string('middle_name')->nullable();
        $table->string('last_name');

        // ✅ ADD THESE (MISSING)
        $table->date('birthday')->nullable();
         $table->string('gender', 10)->nullable();

        $table->timestamps();

        // FOREIGN KEY (IMPORTANT)
        $table->foreign('booking_id')
              ->references('id')
              ->on('bookings')
              ->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passengers');
    }
};
