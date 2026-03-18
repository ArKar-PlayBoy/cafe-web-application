@extends('layouts.staff')

@section('title', 'Log Waste - ' . $stock->name)

@section('content')
<h1 class="text-2xl font-bold mb-6 dark:text-white">Log Waste: {{ $stock->name }}</h1>

<div class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded">
    <p class="text-gray-600 dark:text-gray-300">Current Quantity: <span class="font-bold">{{ $stock->current_quantity }}</span></p>
</div>

<form action="{{ route('staff.stock.waste', $stock->id) }}" method="POST" class="max-w-lg">
    @csrf
    
    <div class="mb-4">
        <label class="block text-sm font-medium mb-2 dark:text-gray-300">Quantity to Waste</label>
        <input type="number" name="quantity" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700" min="1" max="{{ $stock->current_quantity }}" required>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium mb-2 dark:text-gray-300">Reason</label>
        <select name="reason" class="w-full border rounded px-3 py-2 bg-white text-gray-900 dark:bg-slate-800 dark:border-slate-700 dark:text-white" required>
            <option value="Expired" class="bg-white text-gray-900 dark:bg-slate-800 dark:text-white">Expired</option>
            <option value="Damaged" class="bg-white text-gray-900 dark:bg-slate-800 dark:text-white">Damaged</option>
            <option value="Spilled" class="bg-white text-gray-900 dark:bg-slate-800 dark:text-white">Spilled</option>
            <option value="Other" class="bg-white text-gray-900 dark:bg-slate-800 dark:text-white">Other</option>
        </select>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium mb-2 dark:text-gray-300">Note (Optional)</label>
        <textarea name="note" rows="2" class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700"></textarea>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">Log Waste</button>
        <a href="{{ route('staff.stock.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Cancel</a>
    </div>
</form>
@endsection
