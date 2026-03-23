@extends('layouts.admin')

@section('title', 'Add Stock Item')

@section('content')
<h1 class="text-2xl font-bold mb-6">Add Stock Item</h1>

<form action="{{ route('admin.stock.store') }}" method="POST" class="max-w-2xl">
    @csrf
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-2" for="name">Name</label>
        <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" required autocomplete="off">
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-2" for="current_quantity">Initial Quantity</label>
            <input type="number" name="current_quantity" id="current_quantity" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" value="0" required autocomplete="off">
        </div>
        <div>
            <label class="block text-sm font-medium mb-2" for="min_quantity">Min Quantity (Alert Level)</label>
            <input type="number" name="min_quantity" id="min_quantity" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" value="10" required autocomplete="off">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-2" for="barcode">Barcode (Optional)</label>
            <input type="text" name="barcode" id="barcode" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" autocomplete="off">
        </div>
        <div>
            <label class="block text-sm font-medium mb-2" for="bin_location">Bin Location</label>
            <select name="bin_location" id="bin_location" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" autocomplete="off">
                {{-- <p>Select Location</p> --}}
                <option value="Walk-in Fridge">Walk-in Fridge</option>
                <option value="Freezer">Freezer</option>
                <option value="Dry Storage A">Dry Storage A</option>
                <option value="Dry Storage B">Dry Storage B</option>
                <option value="Storage Room">Storage Room</option>
            </select>
        </div>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium mb-2" for="category">Category</label>
        <select name="category" id="category" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" required autocomplete="off">
            <option value="ingredient">Ingredient</option>
            <option value="supply">Supply</option>
        </select>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-2" for="unit">Unit of Measure</label>
            <select name="unit" id="unit" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" required autocomplete="off">
                <option value="kg">kg (kilogram)</option>
                <option value="g">g (gram)</option>
                <option value="L">L (liter)</option>
                <option value="ml">ml (milliliter)</option>
                <option value="pcs">pcs (pieces)</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2" for="unit_cost">Unit Cost ($)</label>
            <input type="number" step="0.01" name="unit_cost" id="unit_cost" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" placeholder="0.00" autocomplete="off">
            <p class="text-xs text-slate-500 mt-1">Cost per unit (e.g., $20.00/kg)</p>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Create</button>
        <a href="{{ route('admin.stock.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Cancel</a>
    </div>
</form>
@endsection
