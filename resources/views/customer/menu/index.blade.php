@extends('layouts.app')

@section('title', 'Menu')

@section('content')
@php
    $initialCartCount = auth()->check() ? \App\Models\Cart::where('user_id', auth()->id())->sum('quantity') : 0;
@endphp

<!-- Hero / Header Section -->
<div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 sticky top-[64px] z-40 pt-4 sm:pt-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">Menu</h1>
                <p class="mt-2 text-sm sm:text-base text-gray-500 dark:text-gray-400 font-medium">Handcrafted beverages and delicious treats.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Sleek Search -->
                <div class="relative w-full md:w-72">
                    <input type="text" id="menu-search" placeholder="Search our menu..." 
                        class="w-full bg-gray-100 dark:bg-gray-800 border-transparent focus:bg-white focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 rounded-full py-2.5 pl-11 pr-4 text-sm font-medium text-gray-900 dark:text-gray-100 transition-all duration-300 placeholder-gray-400">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Sleek Category Tabs -->
        <div class="flex overflow-x-auto hide-scrollbar gap-6 sm:gap-8 pb-px">
            <a href="{{ route('menu') }}" class="whitespace-nowrap pb-4 text-sm font-bold border-b-2 transition-colors duration-200 {{ !request('category') ? 'border-emerald-600 text-emerald-700 dark:border-emerald-500 dark:text-emerald-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                All Offerings
            </a>
            @foreach($categories as $category)
            <a href="{{ route('menu', ['category' => $category->id]) }}" class="whitespace-nowrap pb-4 text-sm font-bold border-b-2 transition-colors duration-200 {{ request('category') == $category->id ? 'border-emerald-600 text-emerald-700 dark:border-emerald-500 dark:text-emerald-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                {{ $category->name }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    
    <!-- AI Barista Banner -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="inline-flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 px-5 py-2.5 rounded-full border border-emerald-100 dark:border-emerald-800/30 w-fit">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <span class="text-xs font-bold tracking-wide text-emerald-800 dark:text-emerald-300 uppercase">AI Barista Ready</span>
        </div>
        
        <div class="flex items-center gap-3 self-end sm:self-auto">
            <span class="text-sm font-bold text-gray-500 dark:text-gray-400"><span id="item-count">{{ $menuItems->count() }}</span> Items</span>
            <!-- Minimalist Sort -->
            <select id="sort-select" class="text-sm font-bold bg-transparent border-none text-gray-700 dark:text-gray-300 focus:ring-0 cursor-pointer p-0 pr-6">
                <option class="bg-white dark:bg-gray-800" value="default">Featured</option>
                <option class="bg-white dark:bg-gray-800" value="price-low">Price: Low to High</option>
                <option class="bg-white dark:bg-gray-800" value="price-high">Price: High to Low</option>
                <option class="bg-white dark:bg-gray-800" value="name-asc">A-Z</option>
                <option class="bg-white dark:bg-gray-800" value="name-desc">Z-A</option>
            </select>
        </div>
    </div>

    <!-- Product Grid -->
    <div id="menu-grid" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-4 gap-y-10 sm:gap-x-6 sm:gap-y-12">
        @forelse($menuItems as $item)
        <div class="menu-item group flex flex-col" 
             data-name="{{ strtolower($item->name) }}" 
             data-price="{{ $item->price }}"
             data-category="{{ $item->category->slug }}">
             
            <!-- Image Hero -->
            <div class="relative w-full aspect-square bg-gray-50 dark:bg-gray-800 sm:rounded-[2rem] rounded-3xl overflow-hidden mb-4 shadow-sm border border-gray-100 dark:border-gray-800">
                <img src="{{ $item->featured_image }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                
                @if(!$item->is_available)
                <div class="absolute inset-0 bg-white/40 dark:bg-black/40 backdrop-blur-sm flex items-center justify-center">
                    <span class="bg-gray-900 text-white px-4 py-1.5 rounded-full text-xs font-bold tracking-widest uppercase">Sold Out</span>
                </div>
                @endif
                
                <!-- Quick Add Hover Overlay (Desktop) -->
                @if($item->is_available)
                <div class="absolute bottom-3 right-3 sm:bottom-4 sm:right-4 sm:opacity-0 sm:translate-y-2 sm:group-hover:opacity-100 sm:group-hover:translate-y-0 transition-all duration-300 ease-out drop-shadow-xl z-20">
                    <button type="button" onclick="document.getElementById('notes-{{ $item->id }}').classList.toggle('hidden')" class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-600 hover:bg-gray-700 text-white rounded-full flex items-center justify-center transform active:scale-95 transition-all outline-none focus:ring-4 focus:ring-gray-600/30 mb-2" aria-label="Add notes">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <form action="{{ route('cart.add', $item->id) }}" method="POST" class="quick-add-form">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <textarea name="notes" id="notes-{{ $item->id }}" class="hidden w-full text-xs sm:text-sm mb-2 px-2 py-1 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white" rows="2" placeholder="Notes (e.g., less ice)"></textarea>
                        <button type="submit" class="w-10 h-10 sm:w-12 sm:h-12 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full flex items-center justify-center transform active:scale-95 transition-all outline-none focus:ring-4 focus:ring-emerald-600/30" aria-label="Add to cart">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Details -->
            <div class="px-1 text-left flex-1 flex flex-col">
                <div class="flex items-center justify-between mb-1 gap-2">
                    <span class="text-[10px] sm:text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ $item->category->name }}</span>
                </div>
                <h3 class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white leading-snug mb-1 truncate">{{ $item->name }}</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed mb-3 flex-1">{{ $item->description }}</p>
                <div class="mt-auto pt-1 font-bold text-gray-900 dark:text-white text-sm sm:text-base">${{ number_format($item->price, 2) }}</div>
            </div>
            
        </div>
        @empty
        <div class="col-span-full py-20 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-gray-800 mb-6">
                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">No items found</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">We couldn't find any items matching your search criteria.</p>
            <a href="{{ route('menu') }}" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-full text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                Clear Filters
            </a>
        </div>
        @endforelse
    </div>
</div>

<!-- Premium Floating Cart Pill -->
<div id="floating-cart" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 transition-all duration-500 transform {{ $initialCartCount > 0 ? '' : 'translate-y-full opacity-0' }}">
    <a href="{{ route('cart') }}" class="flex items-center gap-3 sm:gap-4 bg-gray-900/95 dark:bg-white/95 backdrop-blur-md text-white dark:text-gray-900 px-5 sm:px-6 py-3 sm:py-3.5 rounded-full shadow-2xl border border-gray-800 dark:border-gray-200 hover:scale-105 active:scale-95 transition-all">
        <div class="relative flex items-center justify-center">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span id="floating-cart-count" class="absolute -top-2 -right-2 sm:-right-3 bg-emerald-500 text-white text-[10px] sm:text-xs font-extrabold px-1.5 py-0.5 rounded-full min-w-[18px] sm:min-w-[20px] text-center shadow-sm">{{ $initialCartCount > 9 ? '9+' : $initialCartCount }}</span>
        </div>
        <span class="font-bold text-sm sm:text-base tracking-wide whitespace-nowrap">View Order</span>
    </a>
</div>

<style>
/* Hide scrollbar for category tabs */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('menu-search');
    const sortSelect = document.getElementById('sort-select');
    const menuItems = document.querySelectorAll('.menu-item');
    const itemCountEl = document.getElementById('item-count');
    const menuGrid = document.getElementById('menu-grid');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    function filterItems() {
        if (!searchInput) return;
        
        const searchTerm = searchInput.value.toLowerCase();
        const sortValue = sortSelect ? sortSelect.value : 'default';

        let filteredItems = Array.from(menuItems);

        // Filter by search
        if (searchTerm) {
            filteredItems = filteredItems.filter(item => {
                const name = item.dataset.name;
                return name.includes(searchTerm);
            });
        }

        // Sort
        if (sortValue !== 'default') {
            filteredItems.sort((a, b) => {
                const priceA = parseFloat(a.dataset.price);
                const priceB = parseFloat(b.dataset.price);
                const nameA = a.dataset.name;
                const nameB = b.dataset.name;

                switch(sortValue) {
                    case 'price-low': return priceA - priceB;
                    case 'price-high': return priceB - priceA;
                    case 'name-asc': return nameA.localeCompare(nameB);
                    case 'name-desc': return nameB.localeCompare(nameA);
                    default: return 0;
                }
            });
        }

        // Hide all items first
        menuItems.forEach(item => item.style.display = 'none');

        // Re-append filtered items in sorted order to actually reorder the DOM
        filteredItems.forEach(item => {
            item.style.display = 'flex';
            menuGrid.appendChild(item);
        });
        
        // Update count
        if(itemCountEl) itemCountEl.textContent = filteredItems.length;
    }

    if(searchInput) searchInput.addEventListener('input', filterItems);
    if(sortSelect) sortSelect.addEventListener('change', filterItems);

    // Quick add to cart feedback
    document.querySelectorAll('.quick-add-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalHTML = btn.innerHTML;
            
            btn.innerHTML = '<svg class="w-5 h-5 sm:w-6 sm:h-6 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(response => {
                if(response.redirected) {
                    window.location.href = response.url;
                    return null;
                }
                return response.json().then(data => ({ status: response.status, body: data }));
            })
            .then(result => {
                if(!result) return; 
                const { status, body } = result;
                if (status >= 200 && status < 300 && body.success) {
                    btn.innerHTML = '<svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
                    btn.classList.replace('bg-emerald-600', 'bg-gray-900');
                    btn.classList.replace('hover:bg-emerald-700', 'hover:bg-gray-800');
                    
                    // Update badges
                    const counts = document.querySelectorAll('.cart-count-badge');
                    counts.forEach(c => {
                        c.textContent = body.cartCount > 9 ? '9+' : body.cartCount;
                        c.classList.remove('hidden');
                        c.classList.add('flex', 'items-center', 'justify-center');
                    });
                    
                    const floatingCart = document.getElementById('floating-cart');
                    const floatingCount = document.getElementById('floating-cart-count');
                    if (floatingCart && floatingCount) {
                        floatingCount.textContent = body.cartCount > 9 ? '9+' : body.cartCount;
                        floatingCart.classList.remove('translate-y-full', 'opacity-0');
                    }
                    
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.classList.replace('bg-gray-900', 'bg-emerald-600');
                        btn.classList.replace('hover:bg-gray-800', 'hover:bg-emerald-700');
                    }, 2000);
                } else {
                    btn.innerHTML = originalHTML;
                    alert(body.message || 'Failed to add item to cart');
                }
            }).catch(() => {
                btn.innerHTML = originalHTML;
                alert('Failed to add item to cart. Please check your connection.');
            });
        });
    });
});
</script>
@endsection
