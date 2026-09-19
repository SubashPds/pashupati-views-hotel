<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('overview')->nullable();
            foreach (['opening', 'closing', 'breakfast_start', 'breakfast_end', 'lunch_start', 'lunch_end', 'dinner_start', 'dinner_end'] as $time) {
                $table->time($time.'_time')->nullable();
            }
            $table->json('cuisines')->nullable();
            $table->json('featured_dishes')->nullable();
            $table->string('menu_image')->nullable();
            $table->boolean('is_active')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('restaurant_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        foreach (DB::table('roles')->whereIn('name', ['admin', 'superadmin'])->get() as $role) {
            $permissions = json_decode($role->permissions, true) ?? [];
            $permissions[] = 'restaurant';
            DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode(array_values(array_unique($permissions)))]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_images');
        Schema::dropIfExists('restaurants');
        foreach (DB::table('roles')->get() as $role) {
            $permissions = array_values(array_diff(json_decode($role->permissions, true) ?? [], ['restaurant']));
            DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode($permissions)]);
        }
    }
};
