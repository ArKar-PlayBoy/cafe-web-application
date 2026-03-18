@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Audit Logs</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Track all system activities and changes across the platform.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.audit-logs.export', request()->query()) }}" class="px-4 py-2.5 rounded-xl glass-card text-emerald-600 dark:text-emerald-400 font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-all flex items-center gap-2 border border-emerald-200 dark:border-emerald-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="glass-card p-6 rounded-xl mb-8">
    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">User</label>
            <select name="user_id" class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Users</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Action</label>
            <input type="text" name="action" value="{{ request('action') }}" placeholder="e.g. user.created" class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">From Date</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">To Date</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition-all">
                Filter
            </button>
            <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2.5 rounded-xl glass-card text-slate-500 dark:text-slate-400 font-semibold text-sm hover:bg-white dark:hover:bg-slate-800 transition-all">
                Reset
            </a>
        </div>
    </form>
    <div class="mt-4 flex items-center gap-3">
        <label class="flex items-center gap-2 text-sm cursor-pointer">
            <a href="{{ route('admin.audit-logs.index', array_merge(request()->query(), ['critical' => request('critical') === '1' ? '' : '1'])) }}" 
               class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-colors {{ request('critical') === '1' ? 'bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-500/5' }}">
                <span class="w-2 h-2 rounded-full {{ request('critical') === '1' ? 'bg-rose-500 animate-pulse' : 'bg-slate-400' }}"></span>
                <span class="font-semibold text-xs uppercase tracking-wider">Critical Only</span>
            </a>
        </label>
    </div>
</div>

{{-- Logs Table --}}
<div class="glass-card rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-indigo-50/50 dark:bg-slate-800/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Resource</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($logs as $log)
                <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-500/5 transition-colors group {{ $log->is_critical ? 'border-l-4 border-l-rose-500' : '' }}">
                    <td class="px-6 py-4">
                        <span class="text-sm text-slate-800 dark:text-slate-200 font-medium">{{ $log->created_at->format('M d, Y') }}</span>
                        <br>
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                                {{ $log->user ? substr($log->user->name, 0, 1) : 'S' }}
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $log->user ? $log->user->name : 'System' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                        @if($log->resource_type)
                        {{ $log->resource_type }} #{{ $log->resource_id }}
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($log->is_critical)
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 uppercase tracking-wider">Critical</span>
                        @else
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-500 uppercase tracking-wider">Normal</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-indigo-600 dark:text-indigo-400 text-sm font-semibold hover:underline">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-slate-500 dark:text-slate-400 italic">No audit logs found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>
@endsection
