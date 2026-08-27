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
        Schema::table('joiner_trips', function (Blueprint $table) {
            $table->string('meetup_time', 5)->nullable()->after('meetup_point');
        });
    }

    public function down(): void
    {
        Schema::table('joiner_trips', function (Blueprint $table) {
            $table->dropColumn('meetup_time');
        });
    }
};
