<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Record-keeping marker for account deletion. Not a Spatie/Eloquent
            // "soft delete" scope — the row is anonymized in place (name/email/
            // phone scrubbed, password re-hashed to an unknown random string)
            // rather than hidden, since past bookings/feedback/payments still
            // need a valid users.id to report against. The random password is
            // what actually prevents login again; this column is just so an
            // admin view can tell "Deleted User" apart from a real customer.
            $table->timestamp('deleted_at')->nullable()->after('otp_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
