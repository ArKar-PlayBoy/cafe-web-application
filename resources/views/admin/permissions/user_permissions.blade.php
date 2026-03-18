@extends('layouts.admin')

@section('title', 'User Permissions - ' . $user->name)

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.permissions.index') }}" class="p-2 rounded-xl glass-card hover:bg-white dark:hover:bg-slate-800 transition-all">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $user->name }}'s Permissions</h1>
        </div>
        <p class="text-slate-500 dark:text-slate-400 mt-1">All effective permissions for this user (role + direct).</p>
    </div>
    <a href="{{ route('admin.permissions.users.edit', $user) }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit Permissions
    </a>
</div>

{{-- User Info --}}
<div class="glass-card p-6 rounded-xl mb-8">
    <div class="flex items-center gap-5">
        <div class="w-14 h-14 rounded-2xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xl shadow-inner">
            {{ substr($user->name, 0, 1) }}
        </div>
        <div>
            <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ $user->name }}</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
        </div>
        @if($role)
        <span class="ml-auto px-4 py-1.5 rounded-xl text-sm font-bold {{ $role->is_super_admin ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400' }}">
            {{ ucfirst($role->name) }}
            @if($role->is_super_admin)
             (Full Access)
            @endif
        </span>
        @endif
    </div>
</div>

{{-- Permissions List --}}
@if($user->isSuperAdmin())
<div class="glass-card p-8 rounded-xl text-center">
    <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    </div>
    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Super Admin</h3>
    <p class="text-slate-500 dark:text-slate-400">This user has full unrestricted access to all system features and permissions.</p>
</div>
@else
<div class="glass-card rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
        <h2 class="font-bold text-slate-900 dark:text-white">Effective Permissions ({{ count($allPermissions) }})</h2>
    </div>
    <div class="p-6">
        @if(count($allPermissions) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($allPermissions as $perm)
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/10">
                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $perm }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-500 dark:text-slate-400 italic text-center py-8">No permissions assigned to this user.</p>
        @endif
    </div>
</div>
@endif
@endsection
