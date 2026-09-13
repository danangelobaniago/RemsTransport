<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('van_id');
            $table->string('image');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('van_id')->references('id')->on('vans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_images');
    }
};
