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
    Schema::table('feedbacks', function (Blueprint $table) {
        // Drop the old restricted foreign key
        $table->dropForeign(['booking_id']);
        // Change it to a regular bigInteger so it can accept IDs from any booking table
        $table->unsignedBigInteger('booking_id')->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
