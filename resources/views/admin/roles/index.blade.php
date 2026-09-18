@extends('layouts.admin')
@section('title', 'Roles & Permissions')
@section('page_title', 'Roles & Permissions')
@section('breadcrumb', 'Admin / Roles & Permissions')
@section('content')
<p class="mb-6 max-w-3xl text-sm text-gray-400">Each permission allows viewing and managing that module, including adding, editing, and deleting its records where available. All active accounts can sign in to the dashboard. Changes apply to everyone with that role on their next request.</p>
<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    @foreach($roles as $role)
    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="rounded-2xl border border-white/10 bg-white/5 p-6">
        @csrf @method('PUT')
        <h2 class="mb-2 text-lg font-semibold">{{ \App\Support\Permissions::ROLES[$role->name] }}</h2>
        @if($role->name === 'superadmin')
            <p class="text-sm text-gray-400">Full access to every module, user accounts, and role permissions. This role cannot be restricted.</p>
        @else
            <fieldset class="mt-4 space-y-3">
                <legend class="sr-only">{{ \App\Support\Permissions::ROLES[$role->name] }} module permissions</legend>
                @foreach(\App\Support\Permissions::MODULES as $value => $label)
                <label class="flex items-center gap-3 text-sm text-gray-300">
                    <input type="checkbox" name="permissions[]" value="{{ $value }}" @checked(in_array($value, $role->permissions, true)) class="h-4 w-4 accent-violet-500">
                    {{ $label }}
                </label>
                @endforeach
            </fieldset>
            <p class="mt-5 text-xs text-gray-400">User accounts and role permissions are always restricted to Superadmin.</p>
            <button type="submit" class="mt-5 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white hover:bg-violet-500">Save {{ \App\Support\Permissions::ROLES[$role->name] }} permissions</button>
        @endif
    </form>
    @endforeach
</div>
@endsection
