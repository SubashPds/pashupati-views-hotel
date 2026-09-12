<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('label', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('button_text', 80)->nullable();
            $table->string('button_url', 1000)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Preserve the previously configured single offer as the first popup.
        $offer = DB::table('site_settings')->where('group', 'offers')->pluck('value', 'key');
        if (filled($offer->get('offer_title'))) {
            DB::table('promotions')->insert([
                'title' => $offer->get('offer_title'), 'label' => $offer->get('offer_label'),
                'description' => $offer->get('offer_description'), 'image_path' => $offer->get('offer_image'),
                'image_alt' => $offer->get('offer_image_alt'), 'button_text' => $offer->get('offer_button_text'),
                'button_url' => $offer->get('offer_button_url'), 'is_active' => (bool) $offer->get('offer_active', true),
                'sort_order' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
