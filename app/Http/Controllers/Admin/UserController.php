<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', ['users' => User::orderBy('name')->paginate(20)]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => 'user', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        User::create($this->validateUser($request));
        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateUser($request, $user);
        if ($user->id === $request->user()->id && ($data['role'] !== 'superadmin' || !$data['is_active'])) {
            throw ValidationException::withMessages(['role' => 'You cannot remove your own superadmin access or deactivate your own account.']);
        }
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['remember_token'] = Str::random(60);
        }
        // Only the validated fields and the generated remember token can be changed.
        $user->forceFill($data)->save();
        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        if (is_string($request->input('email'))) $request->merge(['email' => strtolower(trim($request->input('email')))]);
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'role' => ['required', Rule::in(array_keys(Permissions::ROLES))],
            'is_active' => ['required', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'max:16', 'confirmed'],
        ]);
    }
}
