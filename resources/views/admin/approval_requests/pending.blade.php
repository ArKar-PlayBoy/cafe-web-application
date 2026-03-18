@extends('layouts.admin')

@section('title', 'Pending Approval Requests')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Pending Approvals</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Review requests that need your attention.</p>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="flex items-center gap-2 mb-8">
    <a href="{{ route('admin.approval-requests.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all glass-card text-slate-600 dark:text-slate-400 hover:text-indigo-600">
        All
    </a>
    <a href="{{ route('admin.approval-requests.pending') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all bg-amber-500 text-white shadow-lg shadow-amber-500/20">
        Pending
    </a>
    <a href="{{ route('admin.approval-requests.my') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all glass-card text-slate-600 dark:text-slate-400 hover:text-violet-600">
        My Requests
    </a>
</div>

{{-- Pending Requests --}}
@forelse($requests as $request)
<div class="glass-card rounded-xl p-6 mb-4 border-l-4 border-l-amber-500 hover:shadow-sm transition-all duration-300">
    <div class="flex items-start justify-between">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <a href="{{ route('admin.approval-requests.show', $request) }}" class="text-lg font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    {{ $request->action }}
                </a>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ $request->resource_type }}{{ $request->resource_id ? ' #' . $request->resource_id : '' }}
                    &middot; Requested by <strong>{{ $request->requester ? $request->requester->name : 'Unknown' }}</strong>
                </p>
                @if($request->reason)
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 bg-slate-50 dark:bg-slate-800 rounded-xl px-3 py-2">{{ $request->reason }}</p>
                @endif
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">
                    {{ $request->created_at->diffForHumans() }}
                    @if($request->expires_at)
                    &middot; Expires {{ $request->expires_at->diffForHumans() }}
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('admin.approval-requests.show', $request) }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition-all shrink-0">
            Review
        </a>
    </div>
</div>
@empty
<div class="glass-card rounded-xl p-16 text-center">
    <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <h3 class="text-lg font-bold text-slate-500 dark:text-slate-400 mb-1">No Pending Requests</h3>
    <p class="text-sm text-slate-400 dark:text-slate-500">All approval requests have been processed.</p>
</div>
@endforelse

<div class="mt-6">
    {{ $requests->links() }}
</div>
@endsection
