@extends('layouts.admin')

@section('title', 'Menu Cost Analysis')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/30 shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Menu Cost Analysis</h1>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-0.5">Analyze profit and cost breakdown for menu items</p>
            </div>
        </div>
        <a href="{{ route('admin.menu.index') }}" class="px-5 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Menu
        </a>
    </div>

    <div class="glass-card rounded-2xl p-6 lg:p-8">
        <form method="GET" action="{{ route('admin.menu.cost-analysis') }}" class="mb-8">
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Select Menu Item</label>
            <div class="flex gap-4">
                <select name="item_id" id="item_id" class="flex-1 appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium transition-shadow cursor-pointer" onchange="this.form.submit()">
                    <option value="">Select a menu item...</option>
                    @foreach($menuItems as $item)
                    <option value="{{ $item->id }}" {{ $selectedItem && $selectedItem->id == $item->id ? 'selected' : '' }}>
                        {{ $item->name }} - ${{ number_format($item->price, 2) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </form>

        @if($selectedItem)
        <div class="border-t border-slate-100 dark:border-slate-800 pt-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white">{{ $selectedItem->name }}</h2>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $selectedItem->category->name ?? 'Uncategorized' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Selling Price</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">${{ number_format($selectedItem->price, 2) }}</p>
                </div>
            </div>

            @php
            $hasRecipe = $selectedItem->stockItems->count() > 0;
            $hasUnitCosts = $selectedItem->stockItems
                ->filter(fn($s) => $s->unit_cost && $s->unit_cost > 0)
                ->count() > 0;
            $canShowProfit = $hasRecipe && $hasUnitCosts;
            @endphp

            @if($hasRecipe)
            <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800/50 mb-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700/50">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ingredient</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Quantity Needed</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Unit Cost</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Line Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800/80">
                        @foreach($selectedItem->stockItems as $stock)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">{{ $stock->name }}</td>
                            <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-400">{{ $stock->pivot->quantity_needed }} {{ $stock->unit ?? 'pcs' }}</td>
                            <td class="px-6 py-4 text-right {{ $stock->unit_cost ? 'text-slate-600 dark:text-slate-400' : 'text-amber-500 dark:text-amber-400' }}">
                                @if($stock->unit_cost)
                                ${{ number_format($stock->unit_cost, 2) }}
                                @else
                                <span class="text-xs">Not set</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-800 dark:text-white">
                                @if($stock->unit_cost)
                                ${{ number_format($stock->unit_cost * $stock->pivot->quantity_needed, 2) }}
                                @else
                                $0.00
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold bg-slate-50/50 dark:bg-slate-800/50">
                            <td class="px-6 py-4" colspan="3">Total Ingredient Cost</td>
                            <td class="px-6 py-4 text-right text-lg text-slate-900 dark:text-white">
                                @if($canShowProfit)
                                ${{ number_format($selectedItem->getIngredientCost(), 2) }}
                                @else
                                <span class="text-amber-500">$0.00</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($canShowProfit)
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-blue-50 dark:bg-blue-500/10 rounded-2xl p-4 text-center">
                    <p class="text-xs font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider mb-1">Sale Price</p>
                    <p class="text-2xl font-black text-blue-600 dark:text-blue-400">${{ number_format($selectedItem->price, 2) }}</p>
                </div>
                <div class="bg-rose-50 dark:bg-rose-500/10 rounded-2xl p-4 text-center">
                    <p class="text-xs font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider mb-1">Total Cost</p>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400">${{ number_format($selectedItem->getIngredientCost(), 2) }}</p>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-500/10 rounded-2xl p-4 text-center">
                    <p class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-wider mb-1">Profit</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">${{ number_format($selectedItem->getProfit(), 2) }}</p>
                    <p class="text-xs font-bold {{ $selectedItem->getProfitMargin() >= 20 ? 'text-emerald-500' : 'text-rose-500' }}">
                        {{ $selectedItem->getProfitMargin() }}% Profit Rate
                    </p>
                </div>
            </div>

            @if($selectedItem->getProfitMargin() < 20)
            <div class="mt-6 p-4 bg-rose-100 dark:bg-rose-500/20 rounded-xl flex items-start gap-3">
                <svg class="w-6 h-6 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <p class="font-bold text-rose-700 dark:text-rose-300">Low Profit Rate Warning</p>
                    <p class="text-sm text-rose-600 dark:text-rose-400">This item has a profit rate below 20%. Consider raising the price or reducing ingredient costs.</p>
                </div>
            </div>
            @endif
            @else
            <div class="mt-6 p-4 bg-amber-100 dark:bg-amber-500/20 rounded-xl flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <p class="font-bold text-amber-700 dark:text-amber-300">Unit Costs Not Set</p>
                    <p class="text-sm text-amber-600 dark:text-amber-400">
                        Set unit costs in stock items to see profit calculation.
                        <a href="{{ route('admin.stock.index') }}" class="underline hover:text-amber-800 dark:hover:text-amber-200">Go to Stock</a> to update costs.
                    </p>
                </div>
            </div>
            @endif

            @else
            <div class="text-center py-8">
                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-medium">No ingredients linked to this item</p>
                <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Link stock items via the Recipe page to see cost breakdown.</p>
                <a href="{{ route('admin.stock.recipe', $selectedItem->id) }}" class="inline-block mt-4 px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-bold hover:bg-amber-600 transition-colors">
                    Add Recipe
                </a>
            </div>
            @endif
        </div>
        @else
        <div class="text-center py-16">
            <div class="w-20 h-20 rounded-[1.5rem] bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-lg font-black text-slate-800 dark:text-white">Select a Menu Item</p>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-2 max-w-[300px] mx-auto">Choose a menu item from the dropdown above to view its cost breakdown and profit analysis.</p>
        </div>
        @endif
    </div>
</div>
@endsection
