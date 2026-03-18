@extends('layouts.admin')

@section('title', 'Resource Activity')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.audit-logs.index') }}" class="p-2 rounded-xl glass-card hover:bg-white dark:hover:bg-slate-800 transition-all">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Resource Activity</h1>
        </div>
        <p class="text-slate-500 dark:text-slate-400 mt-1">
            All activity for <strong class="text-violet-600 dark:text-violet-400">{{ $type }} #{{ $id }}</strong>.
        </p>
    </div>
</div>

<div class="glass-card rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-indigo-50/50 dark:bg-slate-800/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($logs as $log)
                <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-500/5 transition-colors {{ $log->is_critical ? 'border-l-4 border-l-rose-500' : '' }}">
                    <td class="px-6 py-4">
                        <span class="text-sm text-slate-800 dark:text-slate-200 font-medium">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                                {{ $log->user ? substr($log->user->name, 0, 1) : 'S' }}
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $log->user ? $log->user->name : 'System' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ $log->action }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($log->is_critical)
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 uppercase tracking-wider">Critical</span>
                        @else
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 uppercase tracking-wider">Normal</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-indigo-600 dark:text-indigo-400 text-sm font-semibold hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <p class="text-slate-500 dark:text-slate-400 italic">No activity found for this resource.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
        {{ $logs->links() }}
    </div>
</div>
@endsection
