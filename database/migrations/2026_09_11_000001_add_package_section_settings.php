<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['key' => 'packages_subtitle', 'value' => 'STAY EXPERIENCES', 'type' => 'text', 'label' => 'Packages Section Subtitle'],
            ['key' => 'packages_title', 'value' => 'Curated Packages', 'type' => 'text', 'label' => 'Packages Section Title'],
            ['key' => 'packages_description', 'value' => 'Tailored experiences that go beyond a simple room — moments designed around your purpose of visit.', 'type' => 'textarea', 'label' => 'Packages Section Description'],
            ['key' => 'packages_note', 'value' => 'All packages can be customised. Contact us to tailor a package that perfectly fits your itinerary.', 'type' => 'textarea', 'label' => 'Packages Bottom Note'],
        ];

        foreach ($settings as $index => $setting) {
            DB::table('site_settings')->insertOrIgnore([
                ...$setting,
                'group' => 'packages',
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('site_settings')->where('group', 'packages')->whereIn('key', [
            'packages_subtitle', 'packages_title', 'packages_description', 'packages_note',
        ])->delete();
    }
};
