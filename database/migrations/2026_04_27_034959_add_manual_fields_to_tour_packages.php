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
    Schema::table('tour_packages', function (Blueprint $table) {
        $table->string('van')->nullable();
        $table->string('plate_number')->nullable();
        $table->string('driver_name')->nullable();
        $table->string('driver_license_number')->nullable();
    });
}

public function down()
{
    Schema::table('tour_packages', function (Blueprint $table) {
        $table->dropColumn(['van', 'plate_number', 'driver_name', 'driver_license_number']);
    });
}
};
