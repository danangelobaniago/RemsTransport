<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // ✅ structured name
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();

            // password
            $table->string('password');

            // role (admin / customer)
            $table->string('role')->default('customer');

            // remember login
            $table->rememberToken();

            // ✅ OTP
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_expires_at')->nullable();

            // timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
