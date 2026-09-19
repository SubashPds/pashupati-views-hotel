<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'faqs' permission to superadmin and admin roles
        $roles = DB::table('roles')->whereIn('name', ['superadmin', 'admin'])->get();
        foreach ($roles as $role) {
            $perms = json_decode($role->permissions, true) ?? [];
            if (!in_array('faqs', $perms, true)) {
                $perms[] = 'faqs';
                DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode($perms)]);
            }
        }
    }

    public function down(): void
    {
        $roles = DB::table('roles')->whereIn('name', ['superadmin', 'admin'])->get();
        foreach ($roles as $role) {
            $perms = json_decode($role->permissions, true) ?? [];
            $perms = array_values(array_filter($perms, fn($p) => $p !== 'faqs'));
            DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode($perms)]);
        }
    }
};
