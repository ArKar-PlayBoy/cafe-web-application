@extends('layouts.admin')

@section('title', 'Permission Management')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Permission Management</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Manage roles and user permissions across the system.</p>
    </div>
    <div class="flex items-center gap-3">
        <form method="POST" action="{{ route('admin.permissions.clear-cache') }}">
            @csrf
            <button type="submit" class="px-4 py-2.5 rounded-xl glass-card text-amber-600 dark:text-amber-400 font-semibold hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all flex items-center gap-2 border border-amber-200 dark:border-amber-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Clear Cache
            </button>
        </form>
    </div>
</div>

{{-- Roles Section --}}
<div class="mb-10">
    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Roles
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
        <div class="glass-card p-6 rounded-xl hover:shadow-sm hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 {{ $role->is_super_admin ? 'bg-amber-500/5' : 'bg-violet-500/5' }} rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl {{ $role->is_super_admin ? 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400' : 'bg-violet-100 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400' }} flex items-center justify-center">
                        @if($role->is_super_admin)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white">{{ ucfirst($role->name) }}</h3>
                        @if($role->is_super_admin)
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-500">Super Admin</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-2 mb-5">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Permissions</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">
                        @if($role->is_super_admin)
                            <span class="text-amber-500">All</span>
                        @else
                            {{ $role->permissions->count() }}
                        @endif
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Users</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $role->users()->count() }}</span>
                </div>
            </div>

            @if(!$role->is_super_admin)
            <a href="{{ route('admin.permissions.roles.edit', $role) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 text-white font-semibold text-sm hover:bg-violet-700 shadow-lg shadow-violet-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Permissions
            </a>
            @else
            <div class="w-full text-center py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-medium text-sm cursor-not-allowed">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Full Access (Locked)
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- Permission Groups Overview --}}
<div>
    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        All Permission Groups
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($permissionGroups as $group => $data)
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    @if($group === 'system')
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    @else
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    @endif
                    {{ $data['label'] }}
                </h3>
            </div>
            <div class="p-4 space-y-2">
                @foreach($data['permissions'] as $slug => $name)
                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="flex items-center gap-3">
                        @if(in_array($slug, \App\Services\PermissionService::CRITICAL_PERMISSIONS))
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                        @else
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        @endif
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $name }}</span>
                    </div>
                    <code class="text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg">{{ $slug }}</code>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
