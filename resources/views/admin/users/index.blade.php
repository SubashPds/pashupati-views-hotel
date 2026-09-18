@extends('layouts.admin')
@section('title', 'Users')
@section('page_title', 'Users')
@section('breadcrumb', 'Admin / Users')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <p class="text-sm text-gray-400">Create accounts and manage their role and access status.</p>
    <a href="{{ route('admin.users.create') }}" class="rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white hover:bg-violet-500">Add user</a>
</div>
<div class="overflow-x-auto rounded-2xl border border-white/10 bg-white/5">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-white/10 text-gray-400"><tr>
            <th scope="col" class="px-5 py-4">Name</th><th scope="col" class="px-5 py-4">Email</th>
            <th scope="col" class="px-5 py-4">Role</th><th scope="col" class="px-5 py-4">Status</th><th scope="col" class="px-5 py-4">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-white/10">
            @foreach($users as $user)
            <tr>
                <td class="px-5 py-4 font-medium">{{ $user->name }} @if($user->id === auth()->id())<span class="text-xs text-gray-400">(You)</span>@endif</td>
                <td class="px-5 py-4">{{ $user->email }}</td>
                <td class="px-5 py-4 whitespace-nowrap">{{ $user->role_label }}</td>
                <td class="px-5 py-4 {{ $user->is_active ? 'text-emerald-300' : 'text-gray-400' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                <td class="px-5 py-4"><a href="{{ route('admin.users.edit', $user) }}" class="text-violet-300 hover:underline" aria-label="Edit {{ $user->name }}">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $users->links() }}</div>
@endsection
