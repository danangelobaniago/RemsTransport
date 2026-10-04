<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Marks the automatic "admin is offline" replies.
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_auto')->default(false)->after('from_admin');
        });

        // Used to tell whether any admin is currently online.
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('is_auto');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });
    }
};
