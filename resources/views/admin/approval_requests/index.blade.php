@extends('layouts.admin')

@section('title', 'Approval Requests')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Approval Requests</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Review and manage critical action approval requests.</p>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="flex items-center gap-2 mb-8">
    <a href="{{ route('admin.approval-requests.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('admin.approval-requests.index') && !request()->routeIs('*.pending') && !request()->routeIs('*.my') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'glass-card text-slate-600 dark:text-slate-400 hover:text-indigo-600' }}">
        All
    </a>
    <a href="{{ route('admin.approval-requests.pending') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ request()->routeIs('*.pending') ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' : 'glass-card text-slate-600 dark:text-slate-400 hover:text-amber-600' }}">
        Pending
        @if($pendingCount > 0)
        <span class="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
        @endif
    </a>
    <a href="{{ route('admin.approval-requests.my') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('*.my') ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'glass-card text-slate-600 dark:text-slate-400 hover:text-violet-600' }}">
        My Requests
    </a>
</div>

{{-- Requests Table --}}
<div class="glass-card rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-indigo-50/50 dark:bg-slate-800/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Requester</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Resource</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($requests as $request)
                @php
                    $statusStyles = [
                        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                        'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                        'rejected' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
                        'expired' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                    ];
                    $style = $statusStyles[$request->status] ?? $statusStyles['expired'];
                    $isPending = $request->isPending();
                @endphp
                <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-500/5 transition-colors group {{ $isPending ? 'border-l-4 border-l-amber-500' : '' }}">
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">#{{ $request->id }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-violet-100 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400">
                            {{ $request->action }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                                {{ $request->requester ? substr($request->requester->name, 0, 1) : '?' }}
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $request->requester ? $request->requester->name : 'Unknown' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                        @if($request->resource_type)
                        {{ $request->resource_type }} #{{ $request->resource_id }}
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg {{ $style }} uppercase tracking-wider">
                            {{ $request->status }}
                        </span>
                        @if($request->isExpired() && $request->status === 'pending')
                        <span class="ml-1 px-2 py-0.5 text-[10px] font-bold rounded bg-slate-200 dark:bg-slate-700 text-slate-500 uppercase">Expired</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">{{ $request->created_at->format('M d, Y') }}</span>
                        <br>
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $request->created_at->diffForHumans() }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.approval-requests.show', $request) }}" class="text-indigo-600 dark:text-indigo-400 text-sm font-semibold hover:underline">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-slate-500 dark:text-slate-400 italic">No approval requests found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
        {{ $requests->links() }}
    </div>
</div>
@endsection
