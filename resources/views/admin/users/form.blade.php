@extends('layouts.admin')
@section('title', $user->exists ? 'Edit User' : 'Add User')
@section('page_title', $user->exists ? 'Edit User' : 'Add User')
@section('breadcrumb', 'Admin / Users / '.($user->exists ? 'Edit' : 'New'))
@section('content')
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="max-w-2xl space-y-5 rounded-2xl border border-white/10 bg-white/5 p-6">
    @csrf
    @if($user->exists) @method('PUT') @endif
    <x-admin.field label="Name" name="name" :value="$user->name" required maxlength="255" autocomplete="name" />
    <x-admin.field label="Email" name="email" type="email" :value="$user->email" required maxlength="255" autocomplete="email" />
    <div>
        <label for="user-role" class="mb-2 block text-sm font-medium text-gray-300">Role</label>
        <select id="user-role" name="role" required class="w-full rounded-xl border border-white/10 bg-gray-900 px-4 py-3 text-sm text-white">
            @foreach(\App\Support\Permissions::ROLES as $value => $label)
            <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="mt-2 text-xs text-gray-400">Access is determined by <a href="{{ route('admin.roles.index') }}" class="text-violet-300 underline">Roles &amp; Permissions</a>.</p>
    </div>
    <div>
        <label for="user-password" class="mb-2 block text-sm font-medium text-gray-300">{{ $user->exists ? 'New password' : 'Password' }}</label>
        <input id="user-password" type="password" name="password" autocomplete="new-password" minlength="15" maxlength="72" @required(!$user->exists) class="w-full rounded-xl border border-white/10 bg-gray-900 px-4 py-3 text-sm text-white">
        <p class="mt-2 text-xs text-gray-400">At least 15 characters; use a unique passphrase. {{ $user->exists ? 'Leave blank to keep the current password.' : 'Share login details with the user securely.' }}</p>
    </div>
    <div>
        <label for="user-password-confirmation" class="mb-2 block text-sm font-medium text-gray-300">Confirm password</label>
        <input id="user-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" @required(!$user->exists) class="w-full rounded-xl border border-white/10 bg-gray-900 px-4 py-3 text-sm text-white">
    </div>
    <label class="flex items-center gap-3 text-sm">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="h-4 w-4 accent-violet-500">
        Active — allow sign in
    </label>
    @if($user->id === auth()->id())
    <p class="text-xs text-gray-400">Your own account must remain active with the Superadmin role.</p>
    @endif
    <div class="flex flex-wrap gap-3">
        <button type="submit" class="rounded-xl bg-violet-600 px-6 py-3 text-sm font-semibold text-white hover:bg-violet-500">{{ $user->exists ? 'Save changes' : 'Create user' }}</button>
        <a href="{{ route('admin.users.index') }}" class="rounded-xl bg-white/5 px-6 py-3 text-sm text-gray-300 hover:bg-white/10">Cancel</a>
    </div>
</form>
@endsection
