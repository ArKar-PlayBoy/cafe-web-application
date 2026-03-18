@extends('layouts.admin')

@section('title', 'Approval Request #' . $approvalRequest->id)

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.approval-requests.index') }}" class="p-2 rounded-xl glass-card hover:bg-white dark:hover:bg-slate-800 transition-all">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Approval Request #{{ $approvalRequest->id }}</h1>
        </div>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Review and take action on this approval request.</p>
    </div>
    @php
        $statusStyles = [
            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200 dark:border-amber-500/20',
            'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
            'rejected' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border-rose-200 dark:border-rose-500/20',
            'expired' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700',
        ];
        $style = $statusStyles[$approvalRequest->status] ?? $statusStyles['expired'];
        $isPending = $approvalRequest->isPending();
        $canAct = $isPending && $approvalRequest->requested_by !== auth('admin')->id();
    @endphp
    <span class="px-5 py-2.5 rounded-xl font-bold text-sm uppercase tracking-wider border {{ $style }}">
        {{ $approvalRequest->status }}
    </span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Request Details --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Request Details
                </h2>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Action</label>
                        <span class="px-3 py-1.5 text-sm font-bold rounded-xl bg-violet-100 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400">
                            {{ $approvalRequest->action }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Resource</label>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                            {{ $approvalRequest->resource_type ?? 'N/A' }}
                            @if($approvalRequest->resource_id)
                            <span class="text-slate-400">#{{ $approvalRequest->resource_id }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Requested At</label>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $approvalRequest->created_at->format('F j, Y \a\t g:i A') }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $approvalRequest->created_at->diffForHumans() }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Expires At</label>
                        @if($approvalRequest->expires_at)
                        <p class="text-sm font-semibold {{ $approvalRequest->isExpired() ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200' }}">
                            {{ $approvalRequest->expires_at->format('F j, Y \a\t g:i A') }}
                        </p>
                        @if($approvalRequest->isExpired())
                        <p class="text-xs text-rose-500 mt-0.5 font-bold">Expired</p>
                        @else
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $approvalRequest->expires_at->diffForHumans() }}</p>
                        @endif
                        @else
                        <p class="text-sm text-slate-400 italic">No expiration</p>
                        @endif
                    </div>
                </div>

                @if($approvalRequest->reason)
                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Reason</label>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl p-4">
                        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ $approvalRequest->reason }}</p>
                    </div>
                </div>
                @endif

                @if($approvalRequest->rejection_reason && $approvalRequest->status !== 'pending')
                <div>
                    <label class="block text-xs font-bold text-{{ $approvalRequest->status === 'approved' ? 'emerald' : 'rose' }}-500 uppercase tracking-wider mb-2">
                        {{ $approvalRequest->status === 'approved' ? 'Approval Notes' : 'Rejection Reason' }}
                    </label>
                    <div class="bg-{{ $approvalRequest->status === 'approved' ? 'emerald' : 'rose' }}-50/50 dark:bg-{{ $approvalRequest->status === 'approved' ? 'emerald' : 'rose' }}-500/5 rounded-2xl p-4 border border-{{ $approvalRequest->status === 'approved' ? 'emerald' : 'rose' }}-100 dark:border-{{ $approvalRequest->status === 'approved' ? 'emerald' : 'rose' }}-500/10">
                        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ $approvalRequest->rejection_reason }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Payload --}}
        @if($approvalRequest->payload)
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    Payload Data
                </h2>
            </div>
            <div class="p-6">
                <div class="bg-slate-50 dark:bg-slate-900 rounded-2xl p-4 overflow-x-auto">
                    <pre class="text-xs text-slate-700 dark:text-slate-300 font-mono leading-relaxed">{{ json_encode($approvalRequest->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        @if($canAct)
        <div class="glass-card rounded-xl p-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">Take Action</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Approve --}}
                <form method="POST" action="{{ route('admin.approval-requests.approve', $approvalRequest) }}" x-data="{ confirmApprove: false }">
                    @csrf
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Approval Notes (optional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Add any notes..."></textarea>
                        <button type="submit" onclick="return confirm('Are you sure you want to approve this request? This action will be executed immediately.')" class="w-full px-6 py-3 rounded-2xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Approve Request
                        </button>
                    </div>
                </form>

                {{-- Reject --}}
                <form method="POST" action="{{ route('admin.approval-requests.reject', $approvalRequest) }}">
                    @csrf
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-rose-500 uppercase tracking-wider">Rejection Reason (required)</label>
                        <textarea name="rejection_reason" rows="2" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-rose-500 focus:border-rose-500" placeholder="Reason for rejection..."></textarea>
                        <button type="submit" onclick="return confirm('Are you sure you want to reject this request?')" class="w-full px-6 py-3 rounded-2xl bg-rose-600 text-white font-bold hover:bg-rose-700 shadow-lg shadow-rose-600/20 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @elseif($isPending && $approvalRequest->requested_by === auth('admin')->id())
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p class="text-sm font-semibold text-amber-600 dark:text-amber-400">You cannot approve or reject your own request.</p>
            </div>
            <form method="POST" action="{{ route('admin.approval-requests.cancel', $approvalRequest) }}">
                @csrf
                <button type="submit" onclick="return confirm('Are you sure you want to cancel this request?')" class="px-6 py-3 rounded-2xl bg-slate-600 text-white font-bold hover:bg-slate-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cancel Request
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Requester Card --}}
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Requested By</h3>
            @if($approvalRequest->requester)
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-lg shadow-inner">
                    {{ substr($approvalRequest->requester->name, 0, 1) }}
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white">{{ $approvalRequest->requester->name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $approvalRequest->requester->email }}</p>
                </div>
            </div>
            @else
            <p class="text-sm text-slate-400 italic">Unknown User</p>
            @endif
        </div>

        {{-- Approver Card --}}
        @if($approvalRequest->approver)
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">
                {{ $approvalRequest->status === 'approved' ? 'Approved By' : 'Rejected By' }}
            </h3>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl {{ $approvalRequest->status === 'approved' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400' }} flex items-center justify-center font-bold text-lg shadow-inner">
                    {{ substr($approvalRequest->approver->name, 0, 1) }}
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white">{{ $approvalRequest->approver->name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $approvalRequest->approver->email }}</p>
                    @if($approvalRequest->approved_at)
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $approvalRequest->approved_at->diffForHumans() }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Timeline --}}
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Timeline</h3>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Request Created</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $approvalRequest->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                @if($approvalRequest->approved_at)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full {{ $approvalRequest->status === 'approved' ? 'bg-emerald-500' : 'bg-rose-500' }} mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ $approvalRequest->status === 'approved' ? 'Approved' : 'Rejected' }}
                        </p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $approvalRequest->approved_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                @endif
                @if($approvalRequest->expires_at)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full {{ $approvalRequest->isExpired() ? 'bg-rose-500' : 'bg-slate-400' }} mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ $approvalRequest->isExpired() ? 'Expired' : 'Expiry Date' }}
                        </p>
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $approvalRequest->expires_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
