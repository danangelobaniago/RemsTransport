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
        Schema::table('vans', function (Blueprint $table) {
            $table->date('last_oil_change')->nullable()->after('status');
            $table->date('last_maintenance')->nullable()->after('last_oil_change');
            $table->date('last_problem_date')->nullable()->after('last_maintenance');
            $table->string('last_problem_notes')->nullable()->after('last_problem_date');
        });
    }

    public function down(): void
    {
        Schema::table('vans', function (Blueprint $table) {
            $table->dropColumn(['last_oil_change', 'last_maintenance', 'last_problem_date', 'last_problem_notes']);
        });
    }
};
