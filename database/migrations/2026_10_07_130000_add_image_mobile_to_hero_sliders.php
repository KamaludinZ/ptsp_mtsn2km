<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional portrait image of a slide for phones (the hero there is taller
 * than wide); without it phones show the centre of the landscape image.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_sliders', function (Blueprint $table) {
            $table->string('image_mobile')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('hero_sliders', function (Blueprint $table) {
            $table->dropColumn('image_mobile');
        });
    }
};
