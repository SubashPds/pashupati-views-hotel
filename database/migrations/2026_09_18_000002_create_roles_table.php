<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('permissions');
            $table->timestamps();
        });
        $modules = ['rooms', 'packages', 'experiences', 'hero-slides', 'promotions', 'gallery', 'services', 'testimonials', 'blogs', 'enquiries', 'settings'];
        foreach (['superadmin', 'admin', 'user'] as $name) {
            DB::table('roles')->insert([
                'name' => $name, 'permissions' => json_encode($name === 'user' ? [] : $modules),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
