<?php

use App\Support\VanVariant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vans', function (Blueprint $table) {
            $table->string('color', 50)->nullable()->after('name');
        });

        // Vans used to carry their color in the name, e.g. "Nissan Urvan NV350 (Gray)".
        // Copy it into the new column; the name itself is left as-is because
        // joiner trips and tour packages still refer to vans by name.
        foreach (DB::table('vans')->get(['id', 'name']) as $van) {
            $color = VanVariant::colorFromName($van->name);
            if ($color !== null) {
                DB::table('vans')->where('id', $van->id)->update(['color' => $color]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('vans', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
