<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Weekly rest day, 0 = Sunday ... 6 = Saturday (matches PHP date('w')).
            // Null means the driver has no fixed day off.
            $table->unsignedTinyInteger('day_off')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('day_off');
        });
    }
};
