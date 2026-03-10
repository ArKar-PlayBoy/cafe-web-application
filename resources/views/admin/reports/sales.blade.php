@extends('layouts.admin')

@section('title', 'Sales Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Sales Analytics</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Detailed breakdown of revenue, orders, and performance.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl glass-card text-slate-700 dark:text-slate-200 font-medium hover:bg-white dark:hover:bg-slate-800 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Export PDF
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card rounded-2xl p-6 mb-8 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.sales') }}" class="flex flex-col sm:flex-row flex-wrap items-end gap-5">
            <div class="w-full sm:w-auto flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Time Period</label>
                <div class="relative">
                    <select name="date_range" class="w-full appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-shadow cursor-pointer">
                        @foreach($dateOptions as $key => $option)
                            <option value="{{ $key }}" {{ $dateRange === $key ? 'selected' : '' }}>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
            <div id="customDates" class="w-full sm:w-auto flex flex-wrap gap-5 flex-1 {{ $dateRange !== 'custom' ? 'hidden' : 'flex' }}">
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Start Date</label>
                    <input type="date" name="start_date" value="{{ $report['start_date'] }}" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-shadow">
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">End Date</label>
                    <input type="date" name="end_date" value="{{ $report['end_date'] }}" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-shadow">
                </div>
            </div>
            <button type="submit" class="w-full sm:w-auto bg-indigo-600 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-500/20 active:scale-95 transition-all shadow-md shadow-indigo-600/20">Apply Filters</button>
        </form>
    </div>

    <!-- Top Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Revenue -->
        <div class="glass-card relative p-6 rounded-2xl overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/80 to-transparent dark:from-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <h3 class="text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">Total Revenue</h3>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-sm border border-emerald-100/50 dark:border-emerald-500/20 transition-transform group-hover:scale-110 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="relative z-10 text-3xl sm:text-4xl font-black text-slate-800 dark:text-slate-100 leading-none mb-1">${{ number_format($report['total_revenue'], 2) }}</p>
        </div>

        <!-- Orders -->
        <div class="glass-card relative p-6 rounded-2xl overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/80 to-transparent dark:from-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <h3 class="text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">Total Orders</h3>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm border border-indigo-100/50 dark:border-indigo-500/20 transition-transform group-hover:scale-110 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <p class="relative z-10 text-3xl sm:text-4xl font-black text-slate-800 dark:text-slate-100 leading-none mb-1">{{ $report['order_count'] }}</p>
        </div>

        <!-- AOV -->
        <div class="glass-card relative p-6 rounded-2xl overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-50/80 to-transparent dark:from-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <h3 class="text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">Avg Order Value</h3>
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/20 flex items-center justify-center text-purple-600 dark:text-purple-400 shadow-sm border border-purple-100/50 dark:border-purple-500/20 transition-transform group-hover:scale-110 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="relative z-10 text-3xl sm:text-4xl font-black text-slate-800 dark:text-slate-100 leading-none mb-1">${{ number_format($report['average_order_value'], 2) }}</p>
        </div>

        <!-- Period -->
        <div class="glass-card relative p-6 rounded-2xl overflow-hidden group border-indigo-200 dark:border-indigo-500/30">
            <div class="relative z-10 flex items-center justify-between mb-4">
                <h3 class="text-indigo-500 dark:text-indigo-400 text-[11px] font-bold uppercase tracking-wider">Reporting Period</h3>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/30 flex items-center justify-center text-indigo-700 dark:text-indigo-300 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="relative z-10 text-lg lg:text-xl font-bold text-slate-800 dark:text-slate-100 leading-tight">
                {{ \Carbon\Carbon::parse($report['start_date'])->format('M d') }} 
                <span class="text-slate-400 font-normal mx-1">&mdash;</span> 
                {{ \Carbon\Carbon::parse($report['end_date'])->format('M d, Y') }}
            </p>
        </div>
    </div>

    <!-- Data Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Daily Sales -->
        <div class="glass-card rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                Daily Sales Trend
            </h2>
            @if($report['daily_sales']->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-slate-400 dark:text-slate-500">
                    <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="text-sm font-medium">No sales data for this period.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800/50">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700/50">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Date</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Orders</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800/80">
                            @foreach($report['daily_sales'] as $day)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white text-right">{{ $day->orders }}</td>
                                <td class="px-6 py-4 text-sm font-black text-emerald-600 dark:text-emerald-400 text-right">${{ number_format($day->revenue, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Payment Methods -->
        <div class="glass-card rounded-2xl p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Payment Methods
            </h2>
            @if($report['payment_method_breakdown']->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-slate-400 dark:text-slate-500">
                    <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <p class="text-sm font-medium">No payment data for this period.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($report['payment_method_breakdown'] as $method)
                    <div class="flex justify-between items-center p-4 rounded-xl border border-slate-100 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                                @if(strtolower($method->payment_method) == 'cash')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-slate-200 capitalize">{{ $method->payment_method ?? 'Unknown' }}</p>
                                <p class="text-xs font-medium text-slate-500">{{ $method->count }} orders</p>
                            </div>
                        </div>
                        <p class="text-lg font-black text-slate-900 dark:text-white">${{ number_format($method->total, 2) }}</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.querySelector('select[name="date_range"]').addEventListener('change', function() {
    document.getElementById('customDates').classList.toggle('hidden', this.value !== 'custom');
});
</script>
@endsection
