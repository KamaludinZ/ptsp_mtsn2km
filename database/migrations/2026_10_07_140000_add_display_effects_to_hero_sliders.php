<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How a slide shows its image and text: fit (whole image over a blurred copy)
 * or cover, a slow zoom in/out, and the backdrop behind the title/description.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_sliders', function (Blueprint $table) {
            $table->string('image_fit', 10)->default('contain');
            $table->string('zoom_effect', 10)->default('in');
            $table->string('text_backdrop', 10)->default('glass');
        });
    }

    public function down(): void
    {
        Schema::table('hero_sliders', function (Blueprint $table) {
            $table->dropColumn(['image_fit', 'zoom_effect', 'text_backdrop']);
        });
    }
};
