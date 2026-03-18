@extends('layouts.admin')

@section('title', 'Stock Management')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Stock Management</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.stock.batches') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">Batches</a>
        <a href="{{ route('admin.stock.alerts') }}" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Alerts</a>
        <a href="{{ route('admin.stock.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">New Item</a>
    </div>
</div>

<div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-gray-100 dark:border-slate-700 rounded-2xl shadow-lg overflow-hidden transition-all duration-300">
    <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($stockItems as $stock)
            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                <td class="px-6 py-4">{{ $stock->name }}</td>
                <td class="px-6 py-4 capitalize">{{ $stock->category }}</td>
                <td class="px-6 py-4 font-bold">{{ $stock->current_quantity }}</td>
                <td class="px-6 py-4">{{ $stock->min_quantity }}</td>
                <td class="px-6 py-4">{{ $stock->bin_location ?? '-' }}</td>
                <td class="px-6 py-4">
                    @if($stock->isLowStock())
                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Low Stock</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">OK</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <button type="button" onclick="document.getElementById('add-stock-{{ $stock->id }}').showModal()" class="bg-emerald-500 text-white px-3 py-1 rounded-lg text-sm shadow-sm hover:shadow active:scale-95 transition-all">Add</button>
                        <a href="{{ route('admin.stock.edit', $stock->id) }}" class="bg-amber-500 text-white px-3 py-1 rounded-lg text-sm shadow-sm hover:shadow active:scale-95 transition-all">Edit</a>
                        <a href="{{ route('admin.stock.recipe', $stock->id) }}" class="bg-indigo-500 text-white px-3 py-1 rounded-lg text-sm shadow-sm hover:shadow active:scale-95 transition-all">Recipe</a>
                        @can('stock.delete')
                        <form action="{{ route('admin.stock.destroy', $stock->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-rose-600 text-white px-3 py-1 rounded-lg text-sm shadow-sm hover:shadow active:scale-95 transition-all" onclick="return confirm('Delete?')">Del</button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            <dialog id="add-stock-{{ $stock->id }}" class="modal p-6 rounded-lg shadow-sm dark:bg-slate-800 w-96">
                <h3 class="text-lg font-bold mb-4">Add Stock: {{ $stock->name }}</h3>
                <form action="{{ route('admin.stock.add', $stock->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input type="number" name="quantity" min="1" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Cost (optional)</label>
                        <input type="number" name="cost" step="0.01" min="0" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Expiry Date (optional)</label>
                        <input type="date" name="expiry_date" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Note (optional)</label>
                        <input type="text" name="note" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">Add Stock</button>
                        <button type="button" onclick="document.getElementById('add-stock-{{ $stock->id }}').close()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</button>
                    </div>
                </form>
            </dialog>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
