@extends('layouts.admin')

@section('title', 'Audit Log Details')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.audit-logs.index') }}" class="p-2 rounded-xl glass-card hover:bg-white dark:hover:bg-slate-800 transition-all">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Audit Log #{{ $auditLog->id }}</h1>
        </div>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Detailed view of the recorded system event.</p>
    </div>
    @if($auditLog->is_critical)
    <span class="px-4 py-2 rounded-xl bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-sm uppercase tracking-wider animate-pulse">
        Critical Event
    </span>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Main Details --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Event Info --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Event Information
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Action</label>
                        <span class="px-3 py-1.5 text-sm font-bold rounded-xl bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                            {{ $auditLog->action }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Timestamp</label>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $auditLog->created_at->format('F j, Y \a\t g:i:s A') }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $auditLog->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Resource Type</label>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $auditLog->resource_type ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Resource ID</label>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $auditLog->resource_id ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($auditLog->ip_address || $auditLog->url)
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">IP Address</label>
                        <p class="text-sm font-mono text-slate-800 dark:text-slate-200">{{ $auditLog->ip_address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">HTTP Method</label>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $auditLog->method ?? 'N/A' }}</p>
                    </div>
                </div>
                @endif

                @if($auditLog->url)
                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">URL</label>
                    <p class="text-sm font-mono text-slate-600 dark:text-slate-400 break-all bg-slate-50 dark:bg-slate-800 rounded-xl px-4 py-2">{{ $auditLog->url }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Changes --}}
        @if($auditLog->old_values || $auditLog->new_values)
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Changes
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($auditLog->old_values)
                    <div>
                        <h4 class="text-xs font-bold text-rose-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            Old Values
                        </h4>
                        <div class="bg-rose-50/50 dark:bg-rose-500/5 rounded-2xl p-4 space-y-2">
                            @foreach($auditLog->old_values as $key => $value)
                            <div class="flex items-start justify-between gap-4">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 shrink-0">{{ $key }}</span>
                                <span class="text-xs text-slate-700 dark:text-slate-300 text-right break-all">{{ is_array($value) ? json_encode($value) : $value }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if($auditLog->new_values)
                    <div>
                        <h4 class="text-xs font-bold text-emerald-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            New Values
                        </h4>
                        <div class="bg-emerald-50/50 dark:bg-emerald-500/5 rounded-2xl p-4 space-y-2">
                            @foreach($auditLog->new_values as $key => $value)
                            <div class="flex items-start justify-between gap-4">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 shrink-0">{{ $key }}</span>
                                <span class="text-xs text-slate-700 dark:text-slate-300 text-right break-all">{{ is_array($value) ? json_encode($value) : $value }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- User Card --}}
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Performed By</h3>
            @if($auditLog->user)
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-lg shadow-inner">
                    {{ substr($auditLog->user->name, 0, 1) }}
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white">{{ $auditLog->user->name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $auditLog->user->email }}</p>
                    @if($auditLog->user->role)
                    <span class="text-[10px] font-bold uppercase tracking-wider text-violet-500">{{ ucfirst($auditLog->user->role->name) }}</span>
                    @endif
                </div>
            </div>
            @else
            <p class="text-sm text-slate-400 italic">System Action</p>
            @endif
        </div>

        {{-- User Agent --}}
        @if($auditLog->user_agent)
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">User Agent</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 break-all leading-relaxed">{{ $auditLog->user_agent }}</p>
        </div>
        @endif

        {{-- Quick Links --}}
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Quick Links</h3>
            <div class="space-y-2">
                @if($auditLog->user)
                <a href="{{ route('admin.audit-logs.user', $auditLog->user) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-500/5 transition-colors text-sm font-medium text-slate-700 dark:text-slate-300">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    View User Activity
                </a>
                @endif
                @if($auditLog->resource_type && $auditLog->resource_id)
                <a href="{{ route('admin.audit-logs.resource', [$auditLog->resource_type, $auditLog->resource_id]) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-500/5 transition-colors text-sm font-medium text-slate-700 dark:text-slate-300">
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    View Resource History
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
