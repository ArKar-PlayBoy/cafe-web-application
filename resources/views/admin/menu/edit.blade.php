@extends('layouts.admin')

@section('title', 'Edit Menu Item')

@section('content')
<h1 class="text-2xl font-bold mb-6">Edit Menu Item</h1>

<form action="{{ route('admin.menu.update', $menu->id) }}" method="POST" class="bg-white p-6 rounded-lg shadow max-w-2xl">
    @csrf @method('PUT')
    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Name</label>
        <input type="text" name="name" value="{{ $menu->name }}" class="w-full border rounded px-3 py-2" required>
    </div>
    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Description</label>
        <textarea name="description" class="w-full border rounded px-3 py-2" rows="3">{{ $menu->description }}</textarea>
    </div>
    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Price</label>
        <input type="number" step="0.01" name="price" value="{{ $menu->price }}" class="w-full border rounded px-3 py-2" required>
    </div>
    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Category</label>
        <select name="category_id" class="w-full border rounded px-3 py-2" required>
            @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ $menu->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Image URL</label>
        <input type="url" name="featured_image" value="{{ $menu->featured_image }}" class="w-full border rounded px-3 py-2" required>
    </div>
    <div class="mb-4">
        <label class="flex items-center">
            <input type="checkbox" name="is_available" value="1" class="mr-2" {{ $menu->is_available ? 'checked' : '' }}>
            <span class="font-bold">Available</span>
        </label>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update</button>
    <a href="{{ route('admin.menu.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
</form>

@can('menu.view_cost')
@if($menu->stockItems->count() > 0)
<div class="mt-8 p-6 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-500/10 dark:to-orange-500/10 rounded-xl border border-amber-200 dark:border-amber-500/30">
    <h3 class="font-bold text-amber-800 dark:text-amber-400 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        Cost Breakdown
    </h3>
    
    <table class="w-full text-sm mb-4">
        <thead>
            <tr class="text-left text-amber-700 dark:text-amber-400 border-b border-amber-200 dark:border-amber-500/30">
                <th class="pb-2">Ingredient</th>
                <th class="pb-2 text-right">Qty Needed</th>
                <th class="pb-2 text-right">Unit Cost</th>
                <th class="pb-2 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($menu->stockItems as $stock)
            <tr class="border-b border-amber-100 dark:border-amber-500/20">
                <td class="py-2">{{ $stock->name }}</td>
                <td class="py-2 text-right">{{ $stock->pivot->quantity_needed }} {{ $stock->unit ?? 'pcs' }}</td>
                <td class="py-2 text-right">${{ number_format($stock->unit_cost ?? 0, 2) }}</td>
                <td class="py-2 text-right font-bold">${{ number_format(($stock->unit_cost ?? 0) * $stock->pivot->quantity_needed, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold text-amber-900 dark:text-amber-300">
                <td class="pt-3" colspan="3">Total Ingredient Cost</td>
                <td class="pt-3 text-right">${{ number_format($menu->getIngredientCost(), 2) }}</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="flex items-center justify-between p-4 bg-white/60 dark:bg-slate-900/60 rounded-lg">
        <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Selling Price</p>
            <p class="text-xl font-black text-slate-900 dark:text-white">${{ number_format($menu->price, 2) }}</p>
        </div>
        <div class="text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">Profit</p>
            <p class="text-xl font-black text-emerald-600 dark:text-emerald-400">${{ number_format($menu->getProfit(), 2) }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-slate-500 dark:text-slate-400">Profit Rate</p>
            <span class="inline-block px-3 py-1 rounded-full text-sm font-bold {{ $menu->getMarginBgClass() }} {{ $menu->getMarginClass() }}">
                {{ $menu->getProfitMargin() }}%
            </span>
        </div>
    </div>
    
    @if($menu->getProfitMargin() < 20)
    <div class="mt-4 p-3 bg-rose-100 dark:bg-rose-500/20 rounded-lg flex items-start gap-2">
        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <p class="text-sm text-rose-700 dark:text-rose-300">
            <strong>Low profit rate warning:</strong> This item has a profit rate below 20%. Consider adjusting the price or reducing ingredient costs.
        </p>
    </div>
    @endif
</div>
@endif
@endcan
@endsection
