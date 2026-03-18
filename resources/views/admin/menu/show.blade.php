@extends('layouts.admin')

@section('title', $menu->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <img src="{{ $menu->featured_image }}" alt="{{ $menu->name }}" class="w-full h-64 object-cover">
        <div class="p-6">
            <h1 class="text-3xl font-bold mb-2">{{ $menu->name }}</h1>
            <p class="text-gray-600 mb-4">{{ $menu->description }}</p>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h3 class="font-semibold text-gray-600">Category</h3>
                    <p class="text-gray-700">{{ $menu->category->name }}</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-600">Price</h3>
                    <p class="text-2xl font-bold">${{ number_format($menu->price, 2) }}</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-600">Availability</h3>
                    <span class="px-2 py-1 rounded {{ $menu->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $menu->is_available ? 'Available' : 'Unavailable' }}
                    </span>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.menu.edit', $menu->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">Edit</a>
                <form action="{{ route('admin.menu.destroy', $menu->id) }}" method="POST" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
