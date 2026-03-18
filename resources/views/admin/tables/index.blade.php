@extends('layouts.admin')

@section('title', 'Tables')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Tables</h1>
    <a href="{{ route('admin.tables.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Table</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($tables as $table)
    <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-gray-100 dark:border-slate-700 p-6 rounded-2xl shadow-lg hover:shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex justify-between items-center">
            <h3 class="font-bold text-xl text-gray-900 dark:text-gray-100">{{ $table->table_number }}</h3>
            <span class="px-2 py-1 text-xs rounded {{ $table->status === 'available' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : ($table->status === 'reserved' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300') }}">
                {{ ucfirst($table->status) }}
            </span>
        </div>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Capacity: {{ $table->capacity }} persons</p>
        <div class="flex gap-2 mt-4">
            <a href="{{ route('admin.tables.edit', $table->id) }}" class="bg-amber-500 text-white px-3 py-1 rounded-lg hover:bg-amber-600 transition-colors shadow-sm hover:shadow active:scale-95">Edit</a>
            <form action="{{ route('admin.tables.destroy', $table->id) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-rose-600 text-white px-3 py-1 rounded-lg hover:bg-rose-700 transition-colors shadow-sm hover:shadow active:scale-95" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endsection
