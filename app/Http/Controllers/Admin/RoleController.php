<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index', ['roles' => Role::orderBy('id')->get()]);
    }

    public function update(Request $request, Role $role)
    {
        abort_unless(in_array($role->name, ['admin', 'user'], true), 403, 'Superadmin always has full access.');
        $data = $request->validate([
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['required', 'string', 'distinct', Rule::in(array_keys(Permissions::MODULES))],
        ]);
        $role->update(['permissions' => array_values($data['permissions'] ?? [])]);
        return back()->with('success', Permissions::ROLES[$role->name].' permissions updated.');
    }
}
