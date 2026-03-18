@extends('layouts.admin')

@section('title', 'Customer Analytics')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Customer Analytics</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Understand your audience, retention rates, and top spenders.</p>
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
        <form method="GET" action="{{ route('admin.reports.customers') }}" class="flex flex-col sm:flex-row flex-wrap items-end gap-5">
            <div class="w-full sm:w-auto flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Time Period</label>
                <div class="relative">
                    <select name="date_range" class="w-full appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 font-medium transition-shadow cursor-pointer">
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
            
            <div id="customDates" class="w-full sm:w-auto flex flex-wrap gap-5 flex-1 {{ $dateRange !== 'custom' ? 'hidden' : 'flex' }}">
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 font-medium transition-shadow">
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 font-medium transition-shadow">
                </div>
            </div>
            <button type="submit" class="w-full sm:w-auto bg-sky-600 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-sky-700 focus:ring-4 focus:ring-sky-500/20 active:scale-95 transition-all shadow-md shadow-sky-600/20">Apply Filters</button>
        </form>
    </div>

    <!-- Top Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total -->
        <div class="glass-card relative p-6 rounded-2xl overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-100 to-transparent dark:from-slate-800/50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <h3 class="text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">Total Customers</h3>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 shadow-sm border border-slate-200/50 dark:border-slate-700/50 transition-transform group-hover:scale-110 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
            <p class="relative z-10 text-3xl sm:text-4xl font-black text-slate-800 dark:text-slate-100 leading-none mb-1">{{ $analytics['total_customers'] }}</p>
        </div>

        <!-- New -->
        <div class="glass-card relative p-6 rounded-2xl overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/80 to-transparent dark:from-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <h3 class="text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">New Customers</h3>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-sm border border-emerald-100/50 dark:border-emerald-500/20 transition-transform group-hover:scale-110 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
            </div>
            <div class="relative z-10 flex items-baseline gap-2">
                <p class="text-3xl sm:text-4xl font-black text-slate-800 dark:text-slate-100 leading-none mb-1">{{ $analytics['new_customers'] }}</p>
                <span class="text-sm font-bold text-emerald-500 px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20">{{ number_format($analytics['new_customer_percentage'], 1) }}%</span>
            </div>
        </div>

        <!-- Returning -->
        <div class="glass-card relative p-6 rounded-2xl overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-50/80 to-transparent dark:from-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <h3 class="text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">Returning Customers</h3>
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/20 flex items-center justify-center text-purple-600 dark:text-purple-400 shadow-sm border border-purple-100/50 dark:border-purple-500/20 transition-transform group-hover:scale-110 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
            </div>
            <div class="relative z-10 flex items-baseline gap-2">
                <p class="text-3xl sm:text-4xl font-black text-slate-800 dark:text-slate-100 leading-none mb-1">{{ $analytics['returning_customers'] }}</p>
                <span class="text-sm font-bold text-purple-500 px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-500/10 border border-purple-100 dark:border-purple-500/20">{{ number_format($analytics['returning_customer_percentage'], 1) }}%</span>
            </div>
        </div>

        <!-- Retention -->
        <div class="glass-card relative p-6 rounded-2xl overflow-hidden group border-sky-200 dark:border-sky-500/30">
            <div class="absolute inset-0 bg-gradient-to-br from-sky-50/80 to-transparent dark:from-sky-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <h3 class="text-sky-600 dark:text-sky-400 text-[11px] font-bold uppercase tracking-wider">Retention Rate</h3>
                <div class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-500/30 flex items-center justify-center text-sky-700 dark:text-sky-300 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <p class="relative z-10 text-3xl sm:text-4xl font-black text-slate-800 dark:text-slate-100 leading-none mb-1">{{ number_format($analytics['retention_rate'], 1) }}%</p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="glass-card rounded-2xl p-6 shadow-sm overflow-hidden relative">
        <!-- Decorative subtle icon -->
        <svg class="absolute -bottom-10 -right-10 w-64 h-64 text-sky-50 dark:text-sky-900/10 pointer-events-none transform -rotate-12" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
        </svg>

        <h2 class="relative z-10 text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Top Customers by Spending
        </h2>
        
        @if($analytics['top_customers']->isEmpty())
            <div class="relative z-10 flex flex-col items-center justify-center py-16 text-slate-400 dark:text-slate-500">
                <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <p class="text-sm font-bold tracking-wide uppercase">No customer activity during this period</p>
            </div>
        @else
            <div class="relative z-10 overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800/50 bg-white/40 dark:bg-slate-900/40 backdrop-blur-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700/50">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest w-16 text-center">Rank</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Customer Profile</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Lifetime Orders</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Total Spent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800/80">
                        @foreach($analytics['top_customers'] as $index => $customer)
                        <tr class="hover:bg-sky-50/30 dark:hover:bg-sky-500/5 transition-colors group">
                            <td class="px-6 py-4 text-center">
                                @if($index === 0)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold text-sm shadow-sm border border-amber-200 dark:border-amber-500/30">1</span>
                                @elseif($index === 1)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-600/30 text-slate-600 dark:text-slate-300 font-bold text-sm shadow-sm border border-slate-300 dark:border-slate-500/30">2</span>
                                @elseif($index === 2)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-800/30 text-orange-700 dark:text-orange-400 font-bold text-sm shadow-sm border border-orange-200 dark:border-orange-500/30">3</span>
                                @else
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-50 dark:bg-slate-800/50 text-slate-400 dark:text-slate-500 font-bold text-sm">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-100 to-sky-100 dark:from-indigo-900/50 dark:to-sky-900/50 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-sm border border-white/20 shadow-sm shrink-0">
                                        {{ substr($customer->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ $customer->name }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $customer->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-3 py-1 rounded-lg text-sm font-bold">{{ $customer->total_orders }} <span class="text-xs font-normal opacity-70">orders</span></span>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-emerald-600 dark:text-emerald-400 text-lg">${{ number_format($customer->total_spent, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
document.querySelector('select[name="date_range"]').addEventListener('change', function() {
    document.getElementById('customDates').classList.toggle('hidden', this.value !== 'custom');
});
</script>
@endsection
