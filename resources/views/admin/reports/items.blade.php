@extends('layouts.admin')

@section('title', 'Popular Items Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Popular Items</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Discover your best-selling menu items and their revenue impact.</p>
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
        <form method="GET" action="{{ route('admin.reports.items') }}" class="flex flex-col sm:flex-row flex-wrap items-end gap-5">
            <div class="w-full sm:w-auto flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Time Period</label>
                <div class="relative">
                    <select name="date_range" class="w-full appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium transition-shadow cursor-pointer">
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
            
            <div class="w-full sm:w-auto flex-1 min-w-[150px]">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Top Items Limit</label>
                <div class="relative">
                    <select name="limit" class="w-full appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium transition-shadow cursor-pointer">
                        <option value="5" {{ $limit == 5 ? 'selected' : '' }}>Top 5 Items</option>
                        <option value="10" {{ $limit == 10 ? 'selected' : '' }}>Top 10 Items</option>
                        <option value="20" {{ $limit == 20 ? 'selected' : '' }}>Top 20 Items</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <div id="customDates" class="w-full sm:w-auto flex flex-wrap gap-5 flex-1 {{ $dateRange !== 'custom' ? 'hidden' : 'flex' }}">
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium transition-shadow">
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium transition-shadow">
                </div>
            </div>
            <button type="submit" class="w-full sm:w-auto bg-amber-500 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-amber-600 focus:ring-4 focus:ring-amber-500/20 active:scale-95 transition-all shadow-md shadow-amber-500/20">Apply Filters</button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="glass-card rounded-2xl p-6 shadow-sm overflow-hidden relative">
        <!-- Decorative subtle icon -->
        <svg class="absolute -bottom-10 -right-10 w-64 h-64 text-amber-50 dark:text-amber-900/10 pointer-events-none transform -rotate-12" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd"/>
        </svg>

        <h2 class="relative z-10 text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            Top Selling Items List
        </h2>

        @if($popularItems->isEmpty())
            <div class="relative z-10 flex flex-col items-center justify-center py-16 text-slate-400 dark:text-slate-500">
                <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-sm font-bold tracking-wide uppercase">No items sold during this period</p>
            </div>
        @else
            <div class="relative z-10 overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800/50 bg-white/40 dark:bg-slate-900/40 backdrop-blur-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700/50">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest w-16 text-center">Rank</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Item Name</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Quantity Sold</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Total Orders</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Revenue Generated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800/80">
                        @foreach($popularItems as $index => $item)
                        <tr class="hover:bg-amber-50/50 dark:hover:bg-amber-500/10 transition-colors group">
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
                            <td class="px-6 py-4 font-bold text-slate-800 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $item->menuItem->name ?? 'Unknown Item' }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-3 py-1 rounded-lg text-sm font-bold">{{ $item->total_quantity }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-slate-500 dark:text-slate-400">{{ $item->order_count }} orders</td>
                            <td class="px-6 py-4 text-right font-black text-amber-600 dark:text-amber-400 text-lg">${{ number_format($item->revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@can('menu.view_cost')
<div class="glass-card rounded-2xl p-6 shadow-sm mt-8">
    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Profit Rate Report
    </h2>
    
    @php
    $allMenuItems = \App\Models\MenuItem::with('stockItems')->get();
    $sortedByMargin = $allMenuItems->filter(fn($item) => $item->stockItems->count() > 0 && $item->getIngredientCost() > 0)
        ->sortBy(fn($item) => $item->getProfitMargin());
    @endphp
    
    @if($sortedByMargin->isEmpty())
        <p class="text-slate-400 dark:text-slate-500 text-sm italic py-8 text-center">No menu items have linked stock recipes with costs.</p>
    @else
    <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800/50">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700/50">
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Item</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Price</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Cost</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Profit</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Profit Rate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800/80">
                @foreach($sortedByMargin as $item)
                <tr class="hover:bg-emerald-50/50 dark:hover:bg-emerald-500/10 transition-colors">
                    <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">{{ $item->name }}</td>
                    <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-400">${{ number_format($item->price, 2) }}</td>
                    <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-400">${{ number_format($item->getIngredientCost(), 2) }}</td>
                    <td class="px-6 py-4 text-right font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($item->getProfit(), 2) }}</td>
                    <td class="px-6 py-4 text-right">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $item->getMarginBgClass() }} {{ $item->getMarginClass() }}">
                            {{ $item->getProfitMargin() }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endcan

<script>
document.querySelector('select[name="date_range"]').addEventListener('change', function() {
    document.getElementById('customDates').classList.toggle('hidden', this.value !== 'custom');
});
</script>
@endsection
