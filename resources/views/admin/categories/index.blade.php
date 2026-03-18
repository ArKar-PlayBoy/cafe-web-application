@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Categories</h1>
    @can('categories.create')
    <a href="{{ route('admin.categories.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Category</a>
    @endcan
</div>

<div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-gray-100 dark:border-slate-700 rounded-2xl shadow-lg overflow-hidden transition-all duration-300">
    <table class="min-w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Slug</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Menu Items</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($categories as $category)
            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                <td class="px-6 py-4 text-gray-900 dark:text-gray-100">{{ $category->name }}</td>
                <td class="px-6 py-4 text-gray-900 dark:text-gray-100">{{ $category->slug }}</td>
                <td class="px-6 py-4 text-gray-900 dark:text-gray-100">
                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                        {{ $category->menu_items_count }} items
                    </span>
                </td>
                <td class="px-6 py-4">
                    @can('categories.edit')
                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="text-yellow-600 dark:text-yellow-400 hover:underline mr-3">Edit</a>
                    @endcan
                    @can('categories.delete')
                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline" onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                    </form>
                    @endcan
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4">
        {{ $categories->links() }}
    </div>
</div>
@endsection
