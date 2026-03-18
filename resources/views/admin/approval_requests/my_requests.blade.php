@extends('layouts.admin')

@section('title', 'My Approval Requests')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">My Requests</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Track the status of your submitted approval requests.</p>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="flex items-center gap-2 mb-8">
    <a href="{{ route('admin.approval-requests.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all glass-card text-slate-600 dark:text-slate-400 hover:text-indigo-600">
        All Requests
    </a>
    <a href="{{ route('admin.approval-requests.pending') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all glass-card text-slate-600 dark:text-slate-400 hover:text-amber-600">
        Pending
    </a>
    <a href="{{ route('admin.approval-requests.my') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all bg-violet-600 text-white shadow-lg shadow-violet-600/20">
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
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Resource</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Approver</th>
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
                @endphp
                <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-500/5 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">#{{ $request->id }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-violet-100 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400">
                            {{ $request->action }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                        {{ $request->resource_type ?? '-' }}{{ $request->resource_id ? ' #' . $request->resource_id : '' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg {{ $style }} uppercase tracking-wider">
                            {{ $request->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                        {{ $request->approver ? $request->approver->name : '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                        {{ $request->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 flex items-center gap-3">
                        <a href="{{ route('admin.approval-requests.show', $request) }}" class="text-indigo-600 dark:text-indigo-400 text-sm font-semibold hover:underline">View</a>
                        @if($request->isPending())
                        <form method="POST" action="{{ route('admin.approval-requests.cancel', $request) }}" class="inline">
                            @csrf
                            <button type="submit" onclick="return confirm('Cancel this request?')" class="text-rose-600 dark:text-rose-400 text-sm font-semibold hover:underline">Cancel</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <p class="text-slate-500 dark:text-slate-400 italic">You haven't submitted any approval requests.</p>
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
