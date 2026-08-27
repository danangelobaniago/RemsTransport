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
        // Adding the column after 'van'
        $table->string('driver_name')->nullable()->after('van');
    });
}

public function down()
{
    Schema::table('joiner_trips', function (Blueprint $table) {
        $table->dropColumn('driver_name');
    });
}
};
