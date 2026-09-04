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
            $table->string('name')->nullable()->after('destination');
            $table->text('description')->nullable()->after('name');
            $table->string('image')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('joiner_trips', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'image']);
        });
    }
};
