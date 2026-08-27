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
    // If using ENUM
    DB::statement("ALTER TABLE joiner_trips MODIFY COLUMN status ENUM('active', 'inactive', 'completed') DEFAULT 'active'");
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('joiner_trips', function (Blueprint $table) {
            //
        });
    }
};
