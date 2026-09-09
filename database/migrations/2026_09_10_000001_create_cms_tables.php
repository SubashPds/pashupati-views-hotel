<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Site Settings (key-value pairs)
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('text'); // text, textarea, image, richtext, boolean
            $table->string('group')->default('general'); // general, contact, social, hero, footer
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Rooms
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category'); // deluxe, premium, suite
            $table->string('tagline')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->integer('size_sqm')->nullable();
            $table->integer('max_guests')->default(2);
            $table->string('bed_type')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable(); // CKEditor
            $table->json('amenities')->nullable(); // ["Wi-Fi", "Air conditioning", ...]
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Room Images
        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Experiences / Amenity Highlights
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();       // emoji or icon class
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Gallery
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image_path');
            $table->string('badge_label')->nullable();
            $table->string('section')->default('general'); // general, rooms, dining, etc.
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Services / Add-ons
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('price_label')->default('Price on request');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Testimonials
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->string('author_date')->nullable();  // e.g. "September 2026"
            $table->tinyInteger('rating')->default(5); // 1-5
            $table->text('review');
            $table->string('tag')->nullable(); // "Verified Guest", "Sample"
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Enquiries (contact form submissions)
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('guest_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('category')->nullable(); // Room & stay, Dining, etc.
            $table->text('message')->nullable();
            $table->string('status')->default('new'); // new, read, replied, archived
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('services');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('experiences');
        Schema::dropIfExists('room_images');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('site_settings');
    }
};
