@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $greetingIcon = $hour < 12 ? 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z' : ($hour < 17 ? 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z' : 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z');
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 lg:auto-rows-min gap-4 sm:gap-6 lg:gap-8">
    
    {{-- Main Welcome Card (Left, Spans 8 cols) --}}
    <div class="lg:col-span-8 md:col-span-2 glass-card rounded-[2rem] p-6 lg:p-8 relative overflow-hidden flex flex-col justify-between min-h-[14rem] group hover:scale-[1.01] transition-all duration-300 border-indigo-100/50 dark:border-indigo-500/20 bg-gradient-to-br from-white/80 to-indigo-50/30 dark:from-slate-800/80 dark:to-indigo-900/10">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/20 dark:bg-indigo-500/10 rounded-full blur-3xl pointer-events-none group-hover:bg-indigo-500/30 transition-all duration-500"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6 h-full">
            <div>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-[1.25rem] bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $greetingIcon }}"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 dark:text-white mb-1">{{ $greeting }}, <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600 dark:from-indigo-400 dark:to-violet-400">{{ Auth::guard('admin')->user()->name }}</span>!</h1>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ now()->format('l, F j, Y') }}</p>
                    </div>
                </div>
                <p class="text-slate-600 dark:text-slate-300 text-sm md:text-base max-w-xl">
                    Here's a quick overview of your cafe's performance today. You have <strong class="text-emerald-600 dark:text-emerald-400">{{ $stats['pendingOrders'] ?? 0 }} pending orders</strong> and <strong class="text-amber-600 dark:text-amber-400">{{ $stats['lowStockItems'] ?? 0 }} items running low</strong>.
                </p>
            </div>
            <div class="shrink-0 flex gap-3 mt-4 md:mt-0">
                <a href="{{ route('admin.menu.create') }}" class="px-5 py-3 rounded-2xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-bold shadow-lg shadow-slate-900/20 dark:shadow-white/20 hover:scale-105 transition-transform flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Item
                </a>
                <a href="{{ route('admin.reports.sales') }}" class="px-5 py-3 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-sm font-bold border border-indigo-100 dark:border-indigo-500/20 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Reports
                </a>
            </div>
        </div>
    </div>

    {{-- Today's Revenue (Right, Spans 4 cols) --}}
    <div class="lg:col-span-4 glass-card rounded-[2rem] p-6 lg:p-8 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300 flex flex-col justify-between min-h-[14rem] border-emerald-100/50 dark:border-emerald-500/20 bg-gradient-to-br from-white/80 to-emerald-50/30 dark:from-slate-800/80 dark:to-emerald-900/10">
        <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-emerald-400/20 rounded-full blur-2xl group-hover:bg-emerald-400/30 transition-all duration-500"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-[1rem] bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black tracking-widest uppercase">
                Today
            </span>
        </div>
        <div class="relative z-10 mt-auto">
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Total Revenue</p>
            <p class="text-4xl lg:text-5xl font-black text-slate-800 dark:text-slate-100 leading-none">${{ number_format($stats['totalRevenue'], 2) }}</p>
            <div class="mt-3 flex items-center gap-2 text-sm font-semibold">
                <span class="text-emerald-500 flex items-center">
                    <svg class="w-4 h-4 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    8%
                </span>
                <span class="text-slate-400 text-xs">vs yesterday</span>
            </div>
        </div>
    </div>

    {{-- Stats Row (Total Orders, Total Users, Menu Items, Today's Orders) --}}
    
    {{-- Total Orders --}}
    <div class="lg:col-span-3 lg:row-span-1 glass-card p-6 lg:p-8 rounded-[2rem] relative overflow-hidden group hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <span class="px-3 py-1 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                12%
            </span>
        </div>
        <div class="mt-auto">
            <p class="text-3xl font-black text-slate-800 dark:text-slate-100 mb-1">{{ number_format($stats['totalOrders']) }}</p>
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Total Orders</p>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-indigo-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </div>

    {{-- Total Users --}}
    <div class="lg:col-span-3 lg:row-span-1 glass-card p-6 lg:p-8 rounded-[2rem] relative overflow-hidden group hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-violet-100 dark:bg-violet-500/20 flex items-center justify-center text-violet-600 dark:text-violet-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <span class="px-3 py-1 rounded-xl bg-violet-100 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400 text-xs font-bold flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-violet-500 animate-pulse"></span>
                Active
            </span>
        </div>
        <div class="mt-auto">
            <p class="text-3xl font-black text-slate-800 dark:text-slate-100 mb-1">{{ number_format($stats['totalUsers']) }}</p>
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Total Users</p>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-violet-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </div>

    {{-- Menu Items Stats --}}
    <div class="lg:col-span-3 lg:row-span-1 glass-card p-6 lg:p-8 rounded-[2rem] relative overflow-hidden group hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-100 dark:bg-cyan-500/20 flex items-center justify-center text-cyan-600 dark:text-cyan-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>
        <div class="mt-auto">
            <p class="text-3xl font-black text-slate-800 dark:text-slate-100 mb-1">{{ number_format($stats['totalMenuItems']) }}</p>
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Menu Items</p>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-cyan-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </div>

    {{-- Today's Orders --}}
    <div class="lg:col-span-3 lg:row-span-1 glass-card p-6 lg:p-8 rounded-[2rem] relative overflow-hidden group hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center text-orange-600 dark:text-orange-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <span class="px-3 py-1 rounded-xl bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 text-xs font-bold uppercase tracking-widest">Today</span>
        </div>
        <div class="mt-auto">
            <p class="text-3xl font-black text-slate-800 dark:text-slate-100 mb-1">{{ number_format($stats['todayOrders'] ?? 0) }}</p>
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Orders Today</p>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-orange-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </div>

    {{-- Focus / Action Required (Spans 4 cols, 2 rows) --}}
    <div class="lg:col-span-4 lg:row-span-2 md:col-span-2 glass-card rounded-[2rem] p-6 lg:p-8 flex flex-col border border-rose-100/50 dark:border-rose-900/30">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg shadow-rose-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">Action Required</h2>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Needs your attention</p>
                </div>
            </div>
            @if(empty($stats['lowStockItems']) && empty($pendingApprovals) && $stats['pendingOrders'] == 0)
                <span class="px-2.5 py-1 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold">All caught up</span>
            @endif
        </div>

        <div class="flex-1 flex flex-col gap-4 justify-center">
            @php $hasAlerts = false; @endphp
            
            @if(($stats['pendingOrders'] ?? 0) > 0)
            @php $hasAlerts = true; @endphp
            <a href="{{ route('admin.orders') }}?status=pending" class="group flex items-center justify-between p-4 lg:p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-500/5 hover:bg-amber-50 dark:hover:bg-amber-500/10 border border-amber-100/50 dark:border-amber-500/20 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Pending Orders</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Awaiting confirmation</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $stats['pendingOrders'] }}</span>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-amber-600 transition-colors group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            @endif

            @if(($stats['lowStockItems'] ?? 0) > 0)
            @php $hasAlerts = true; @endphp
            <a href="{{ route('admin.stock.index') }}" class="group flex items-center justify-between p-4 lg:p-5 rounded-2xl bg-rose-50/50 dark:bg-rose-500/5 hover:bg-rose-50 dark:hover:bg-rose-500/10 border border-rose-100/50 dark:border-rose-500/20 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center text-rose-600 dark:text-rose-400 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">Low Stock Alert</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Below threshold</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $stats['lowStockItems'] }}</span>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-rose-600 transition-colors group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            @endif

            @if(Auth::guard('admin')->user()->isSuperAdmin())
            @php
                $pendingApprovals = \App\Models\ApprovalRequest::where('status', 'pending')->where(function($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>', now()); })->count();
            @endphp
            @if($pendingApprovals > 0)
            @php $hasAlerts = true; @endphp
            <a href="{{ route('admin.approval-requests.index') }}" class="group flex items-center justify-between p-4 lg:p-5 rounded-2xl bg-violet-50/50 dark:bg-violet-500/5 hover:bg-violet-50 dark:hover:bg-violet-500/10 border border-violet-100/50 dark:border-violet-500/20 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-500/20 flex items-center justify-center text-violet-600 dark:text-violet-400 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">Pending Approvals</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Needs sign-off</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-violet-600 dark:text-violet-400">{{ $pendingApprovals }}</span>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-violet-600 transition-colors group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            @endif
            @endif

            @if(!$hasAlerts)
            <div class="flex items-center justify-center p-8 text-center h-full">
                <div>
                    <div class="w-20 h-20 rounded-[1.5rem] bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-lg font-black text-slate-800 dark:text-slate-200">All caught up!</p>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-2 max-w-[200px] mx-auto">No urgent actions required at this time.</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Recent Orders (Spans 8 cols, 2 rows) --}}
    <div class="lg:col-span-8 md:col-span-2 glass-card rounded-[2rem] p-6 lg:p-8 flex flex-col min-h-[500px]">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white">Recent Orders</h2>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Latest activity from your customers</p>
                </div>
            </div>
            <a href="{{ route('admin.orders') }}" class="px-5 py-2.5 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 text-sm font-bold border border-indigo-100/50 dark:border-indigo-500/20 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all flex items-center gap-2 group">
                View All
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="flex-1 overflow-auto rounded-[1.5rem] bg-white/40 dark:bg-slate-900/40 border border-slate-200/50 dark:border-slate-800">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-200/50 dark:border-slate-700/50 backdrop-blur-md">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10 w-full md:w-auto">Customer & Order</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10 hidden md:table-cell w-1/4">Total Amount</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10 hidden sm:table-cell w-1/4">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10 text-right hidden lg:table-cell w-1/4">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50 dark:divide-slate-800/50">
                    @forelse($recentOrders->take(6) as $order)
                    <tr class="hover:bg-white/80 dark:hover:bg-slate-800/50 transition-colors cursor-pointer group" onclick="window.location='{{ route('admin.orders') }}'">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-800 flex items-center justify-center text-sm font-black text-slate-700 dark:text-slate-300 shadow-sm shrink-0">
                                    {{ substr($order->user->name ?? 'G', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">{{ $order->user->name ?? 'Guest' }}</p>
                                    <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }} • {{ $order->items_count ?? $order->items->count() }} items</p>
                                </div>
                            </div>
                            <!-- Mobile info -->
                            <div class="mt-3 flex items-center justify-between md:hidden">
                                <span class="text-sm font-black text-slate-800 dark:text-slate-200">${{ number_format($order->total, 2) }}</span>
                                @php
                                    $statusConfig = [
                                        'pending' => ['bg' => 'bg-amber-100 dark:bg-amber-500/20', 'text' => 'text-amber-700 dark:text-amber-400', 'dot' => 'bg-amber-500'],
                                        'confirmed' => ['bg' => 'bg-blue-100 dark:bg-blue-500/20', 'text' => 'text-blue-700 dark:text-blue-400', 'dot' => 'bg-blue-500'],
                                        'preparing' => ['bg' => 'bg-violet-100 dark:bg-violet-500/20', 'text' => 'text-violet-700 dark:text-violet-400', 'dot' => 'bg-violet-500'],
                                        'ready' => ['bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'text' => 'text-cyan-700 dark:text-cyan-400', 'dot' => 'bg-cyan-500'],
                                        'completed' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
                                        'cancelled' => ['bg' => 'bg-rose-100 dark:bg-rose-500/20', 'text' => 'text-rose-700 dark:text-rose-400', 'dot' => 'bg-rose-500'],
                                    ];
                                    $config = $statusConfig[$order->status] ?? ['bg' => 'bg-slate-100 dark:bg-slate-500/20', 'text' => 'text-slate-700 dark:text-slate-400', 'dot' => 'bg-slate-500'];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-xl {{ $config['bg'] }} {{ $config['text'] }} capitalize">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
                                    {{ $order->status }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <span class="text-sm font-black text-slate-800 dark:text-slate-200">${{ number_format($order->total, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 hidden sm:table-cell">
                            @php
                                $statusConfig = [
                                    'pending' => ['bg' => 'bg-amber-100 dark:bg-amber-500/20', 'text' => 'text-amber-700 dark:text-amber-400', 'dot' => 'bg-amber-500'],
                                    'confirmed' => ['bg' => 'bg-blue-100 dark:bg-blue-500/20', 'text' => 'text-blue-700 dark:text-blue-400', 'dot' => 'bg-blue-500'],
                                    'preparing' => ['bg' => 'bg-violet-100 dark:bg-violet-500/20', 'text' => 'text-violet-700 dark:text-violet-400', 'dot' => 'bg-violet-500'],
                                    'ready' => ['bg' => 'bg-cyan-100 dark:bg-cyan-500/20', 'text' => 'text-cyan-700 dark:text-cyan-400', 'dot' => 'bg-cyan-500'],
                                    'completed' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
                                    'cancelled' => ['bg' => 'bg-rose-100 dark:bg-rose-500/20', 'text' => 'text-rose-700 dark:text-rose-400', 'dot' => 'bg-rose-500'],
                                ];
                                $config = $statusConfig[$order->status] ?? ['bg' => 'bg-slate-100 dark:bg-slate-500/20', 'text' => 'text-slate-700 dark:text-slate-400', 'dot' => 'bg-slate-500'];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl {{ $config['bg'] }} {{ $config['text'] }} capitalize">
                                <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right hidden lg:table-cell">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $order->created_at->diffForHumans() }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <p class="text-sm font-bold text-slate-600 dark:text-slate-400">No recent orders</p>
                                <p class="text-xs mt-1">Orders will appear here once customers start purchasing.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Actions grid (Spans 12 cols, wide row) --}}
    {{-- <div class="lg:col-span-12 glass-card rounded-[2rem] p-6 lg:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white">Quick Actions</h2>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Jump right into management</p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
            <a href="{{ route('admin.menu.create') }}" class="group relative overflow-hidden rounded-[1.5rem] bg-emerald-50/50 dark:bg-emerald-500/5 hover:bg-emerald-100/50 dark:hover:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 p-5 lg:p-6 transition-all hover:-translate-y-1">
                <div class="w-12 h-12 rounded-[1rem] bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4 group-hover:scale-110 group-hover:rotate-6 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Add Menu Item</h3>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Create a new dish</p>
                </div>
                <div class="absolute top-4 right-4 text-emerald-200 dark:text-emerald-900/50 group-hover:text-emerald-500/30 transition-colors">
                    <svg class="w-16 h-16 transform right-[-10px] top-[-10px] absolute opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
            </a>
            
            <a href="{{ route('admin.users.create') }}" class="group relative overflow-hidden rounded-[1.5rem] bg-indigo-50/50 dark:bg-indigo-500/5 hover:bg-indigo-100/50 dark:hover:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 p-5 lg:p-6 transition-all hover:-translate-y-1">
                <div class="w-12 h-12 rounded-[1rem] bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 mb-4 group-hover:scale-110 group-hover:rotate-6 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Add User</h3>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Staff or customer account</p>
                </div>
            </a>

            <a href="{{ route('admin.tables.create') }}" class="group relative overflow-hidden rounded-[1.5rem] bg-pink-50/50 dark:bg-pink-500/5 hover:bg-pink-100/50 dark:hover:bg-pink-500/10 border border-pink-100 dark:border-pink-500/20 p-5 lg:p-6 transition-all hover:-translate-y-1">
                <div class="w-12 h-12 rounded-[1rem] bg-pink-100 dark:bg-pink-500/20 flex items-center justify-center text-pink-600 dark:text-pink-400 mb-4 group-hover:scale-110 group-hover:rotate-6 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors">Add Table</h3>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Configure layout</p>
                </div>
            </a>

            <a href="{{ route('admin.categories.create') }}" class="group relative overflow-hidden rounded-[1.5rem] bg-orange-50/50 dark:bg-orange-500/5 hover:bg-orange-100/50 dark:hover:bg-orange-500/10 border border-orange-100 dark:border-orange-500/20 p-5 lg:p-6 transition-all hover:-translate-y-1">
                <div class="w-12 h-12 rounded-[1rem] bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center text-orange-600 dark:text-orange-400 mb-4 group-hover:scale-110 group-hover:rotate-6 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">Add Category</h3>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">For menu items</p>
                </div>
            </a>
        </div>
    </div> --}}

</div>
@endsection
