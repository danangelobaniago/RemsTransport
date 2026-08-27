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
    Schema::table('joiner_trips', function (Blueprint $table) {
        $table->string('plate_number')->nullable()->after('van');
    });
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
