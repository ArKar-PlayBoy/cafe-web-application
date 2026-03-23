@extends('layouts.admin')

@section('title', 'Menu Items')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30 shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Menu Catalog</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manage dishes, prices, and availability</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        @can('menu.view_cost')
        <a href="{{ route('admin.menu.cost-analysis') }}" class="px-4 py-2.5 rounded-2xl bg-amber-500 text-white text-sm font-bold shadow-lg shadow-amber-500/30 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Cost Analysis
        </a>
        @endcan
        @can('menu.create')
        <a href="{{ route('admin.menu.create') }}" class="px-5 py-2.5 rounded-2xl bg-emerald-600 text-white text-sm font-bold shadow-lg shadow-emerald-600/30 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Dish
        </a>
        @endcan
    </div>
</div>

@if($menuItems->isEmpty())
<div class="glass-card rounded-[2rem] p-12 text-center border border-slate-200/50 dark:border-slate-700/50 bg-white/40 dark:bg-slate-900/40 shadow-sm flex flex-col items-center justify-center min-h-[400px]">
    <div class="w-20 h-20 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-6 text-slate-400 dark:text-slate-500 shadow-inner">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
    </div>
    <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2">No Menu Items Found</h3>
    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-6 max-w-sm">Your menu is currently empty. Start adding delicious items for your customers to enjoy.</p>
    @can('menu.create')
    <a href="{{ route('admin.menu.create') }}" class="px-6 py-3 rounded-2xl bg-emerald-600 text-white text-sm font-bold shadow-lg shadow-emerald-600/30 hover:bg-emerald-700 transition-colors">
        Create First Item
    </a>
    @endcan
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @foreach($menuItems as $item)
    <div class="group glass-card rounded-[2rem] border border-slate-200/50 dark:border-slate-700/50 bg-white/40 dark:bg-slate-900/40 shadow-sm overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
        {{-- Image Section --}}
        <div class="relative h-48 overflow-hidden bg-slate-100 dark:bg-slate-800">
            @if($item->featured_image)
                <img src="{{ $item->featured_image }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            @else
                <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            @endif
            
            {{-- Status Badge --}}
            <div class="absolute top-4 right-4">
                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-xl shadow-sm backdrop-blur-md {{ $item->is_available ? 'bg-emerald-500/90 text-white' : 'bg-rose-500/90 text-white' }}">
                    {{ $item->is_available ? 'Available' : 'Unavailable' }}
                </span>
            </div>
            
            {{-- Category Badge --}}
            <div class="absolute bottom-4 left-4">
                <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-xl bg-white/90 dark:bg-slate-900/90 text-slate-700 dark:text-slate-300 shadow-sm backdrop-blur-md">
                    {{ $item->category->name ?? 'Uncategorized' }}
                </span>
            </div>
        </div>

        {{-- Content Section --}}
        <div class="p-5 flex-1 flex flex-col">
            <h3 class="font-black text-lg text-slate-900 dark:text-white mb-1 leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ $item->name }}</h3>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 line-clamp-2 mt-2 leading-relaxed flex-1">{{ $item->description }}</p>
            
            <div class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-800 flex items-start justify-between">
                <div>
                    <span class="text-xl font-black text-slate-900 dark:text-white">${{ number_format($item->price, 2) }}</span>
                    
                    @can('menu.view_cost')
                    <div id="costColumns-{{ $item->id }}" class="hidden mt-2 space-y-1">
                        @if($item->stockItems->count() > 0 && $item->getIngredientCost() > 0)
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Cost:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">${{ number_format($item->getIngredientCost(), 2) }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Profit:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">${{ number_format($item->getProfit(), 2) }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Profit Rate:</span>
                            <span class="px-2 py-0.5 rounded-full font-bold text-xs {{ $item->getMarginBgClass() }} {{ $item->getMarginClass() }}">
                                {{ $item->getProfitMargin() }}%
                            </span>
                        </div>
                        @else
                        <div class="text-xs text-slate-400 italic">No recipe</div>
                        @endif
                    </div>
                    @endcan
                </div>
                
                <div class="flex items-center gap-2">
                    @can('menu.edit')
                    <a href="{{ route('admin.menu.edit', $item->id) }}" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-emerald-100 hover:text-emerald-600 dark:hover:bg-emerald-500/20 dark:hover:text-emerald-400 flex items-center justify-center transition-colors" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </a>
                    @endcan
                    
                    @can('delete-menu-item', $item)
                    <form action="{{ route('admin.menu.destroy', $item->id) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-500/20 dark:hover:text-rose-400 flex items-center justify-center transition-colors" title="Delete" onclick="return confirm('Delete {{ addslashes($item->name) }} forever? This action cannot be undone.')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
