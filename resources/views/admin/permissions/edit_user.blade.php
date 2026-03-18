@extends('layouts.admin')

@section('title', 'Edit User Permissions - ' . $user->name)

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.permissions.index') }}" class="p-2 rounded-xl glass-card hover:bg-white dark:hover:bg-slate-800 transition-all">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Edit User: {{ $user->name }}</h1>
        </div>
        <p class="text-slate-500 dark:text-slate-400 mt-1">
            Manage direct permissions for <strong>{{ $user->name }}</strong>. 
            @if($user->role)
            Role: <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ ucfirst($user->role->name) }}</span>
            @endif
        </p>
    </div>
</div>

{{-- User Info Card --}}
<div class="glass-card p-6 rounded-xl mb-8">
    <div class="flex items-center gap-5">
        <div class="w-14 h-14 rounded-2xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xl shadow-inner">
            {{ substr($user->name, 0, 1) }}
        </div>
        <div>
            <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ $user->name }}</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
        </div>
        @if($user->role)
        <span class="ml-auto px-4 py-1.5 rounded-xl text-sm font-bold {{ $user->role->is_super_admin ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400' }}">
            {{ ucfirst($user->role->name) }}
        </span>
        @endif
    </div>
</div>

<form method="POST" action="{{ route('admin.permissions.users.update', $user) }}">
    @csrf
    @method('PUT')

    <div class="space-y-6">
        @foreach($permissionGroups as $group => $data)
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    @if($group === 'system')
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span class="text-amber-600 dark:text-amber-400">{{ $data['label'] }}</span>
                    @else
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    {{ $data['label'] }}
                    @endif
                </h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($data['permissions'] as $slug => $name)
                @php
                    $isCritical = in_array($slug, \App\Services\PermissionService::CRITICAL_PERMISSIONS);
                    $isDirectPermission = in_array($slug, $userPermissions);
                    $isRolePermission = in_array($slug, $rolePermissions);
                @endphp
                <label class="flex items-center gap-4 p-4 rounded-2xl border-2 transition-all duration-200 {{ $isCritical ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:shadow-md' }} {{ $isDirectPermission ? 'border-indigo-300 dark:border-indigo-500/40 bg-indigo-50/50 dark:bg-indigo-500/5' : ($isRolePermission ? 'border-emerald-200 dark:border-emerald-500/30 bg-emerald-50/30 dark:bg-emerald-500/5' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-200 dark:hover:border-indigo-500/20') }}">
                    <input type="checkbox" 
                           name="permissions[]" 
                           value="{{ $slug }}" 
                           class="w-5 h-5 rounded-lg border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 transition-all"
                           {{ $isDirectPermission ? 'checked' : '' }}
                           {{ $isCritical ? 'disabled' : '' }}>
                    <div class="flex-1">
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 block">{{ $name }}</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $slug }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($isRolePermission)
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-full">Via Role</span>
                        @endif
                        @if($isCritical)
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-500 bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 rounded-full">Critical</span>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8 flex items-center gap-4">
        <button type="submit" class="px-8 py-3 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-600/30 transition-all hover:-translate-y-0.5">
            Save Direct Permissions
        </button>
        <a href="{{ route('admin.permissions.index') }}" class="px-8 py-3 rounded-2xl glass-card text-slate-700 dark:text-slate-300 font-semibold hover:bg-white dark:hover:bg-slate-800 transition-all">
            Cancel
        </a>
    </div>
</form>
@endsection
