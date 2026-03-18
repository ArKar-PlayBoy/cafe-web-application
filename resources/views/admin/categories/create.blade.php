@extends('layouts.admin')

@section('title', 'Create Category')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.categories.index') }}" class="p-2 rounded-xl glass-card hover:bg-white dark:hover:bg-slate-800 transition-all">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Create Category</h1>
        </div>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Add a new category for your menu items.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.categories.store') }}">
    @csrf

    <div class="glass-card rounded-xl p-6 max-w-2xl">
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Category Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autocomplete="off"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="e.g., Coffee, Tea, Pastries">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required autocomplete="off"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="e.g., coffee, tea, pastries">
                <p class="mt-1 text-xs text-slate-500">URL-friendly version of the name</p>
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="px-8 py-3 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-600/30 transition-all hover:-translate-y-0.5">
                Create Category
            </button>
            <a href="{{ route('admin.categories.index') }}" class="px-8 py-3 rounded-2xl glass-card text-slate-700 dark:text-slate-300 font-semibold hover:bg-white dark:hover:bg-slate-800 transition-all">
                Cancel
            </a>
        </div>
    </div>
</form>

<script>
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('slug').value = slug;
});
</script>
@endsection
