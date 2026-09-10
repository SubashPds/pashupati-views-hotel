<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();          // e.g. "MOST POPULAR"
            $table->string('badge')->nullable();            // e.g. "Best Value", "New"
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();    // CKEditor rich text

            // Pricing
            $table->string('price_label')->nullable();      // e.g. "NPR 15,000 / person"
            $table->decimal('price_from', 10, 2)->nullable(); // numeric for sorting

            // Stay details
            $table->string('duration')->nullable();         // e.g. "2 Nights / 3 Days"
            $table->tinyInteger('min_guests')->default(1);
            $table->tinyInteger('max_guests')->nullable();

            // JSON lists
            $table->json('includes')->nullable();           // ["Breakfast", "Airport pickup", ...]
            $table->json('highlights')->nullable();         // ["Pashupatinath Aarati View", ...]

            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
