@extends('layouts.admin')

@section('title', 'Edit Stock Item')

@section('content')
<h1 class="text-2xl font-bold mb-6">Edit Stock Item</h1>

<form action="{{ route('admin.stock.update', $stock->id) }}" method="POST" class="max-w-2xl">
    @csrf
    @method('PUT')
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-2" for="name">Name</label>
        <input type="text" name="name" id="name" value="{{ $stock->name }}" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" required autocomplete="off">
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-2" for="current_quantity">Current Quantity</label>
            <input type="number" name="current_quantity" id="current_quantity" value="{{ $stock->current_quantity }}" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" required autocomplete="off">
        </div>
        <div>
            <label class="block text-sm font-medium mb-2" for="min_quantity">Min Quantity (Alert Level)</label>
            <input type="number" name="min_quantity" id="min_quantity" value="{{ $stock->min_quantity }}" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" required autocomplete="off">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-2" for="barcode">Barcode (Optional)</label>
            <input type="text" name="barcode" id="barcode" value="{{ $stock->barcode }}" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" autocomplete="off">
        </div>
        <div>
            <label class="block text-sm font-medium mb-2" for="bin_location">Bin Location</label>
            <select name="bin_location" id="bin_location" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" autocomplete="off">
                {{-- <option value="">-- Select Location --</option> --}}
                <option value="Walk-in Fridge" {{ $stock->bin_location == 'Walk-in Fridge' ? 'selected' : '' }}>Walk-in Fridge</option>
                <option value="Freezer" {{ $stock->bin_location == 'Freezer' ? 'selected' : '' }}>Freezer</option>
                <option value="Dry Storage A" {{ $stock->bin_location == 'Dry Storage A' ? 'selected' : '' }}>Dry Storage A</option>
                <option value="Dry Storage B" {{ $stock->bin_location == 'Dry Storage B' ? 'selected' : '' }}>Dry Storage B</option>
                <option value="Storage Room" {{ $stock->bin_location == 'Storage Room' ? 'selected' : '' }}>Storage Room</option>
            </select>
        </div>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium mb-2" for="category">Category</label>
        <select name="category" id="category" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" required autocomplete="off">
            <option value="ingredient" {{ $stock->category == 'ingredient' ? 'selected' : '' }}>Ingredient</option>
            <option value="supply" {{ $stock->category == 'supply' ? 'selected' : '' }}>Supply</option>
        </select>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update</button>
        <a href="{{ route('admin.stock.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Cancel</a>
    </div>
</form>
@endsection
