@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<h1 class="text-2xl font-bold mb-6">Edit User</h1>

@if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow max-w-lg" autocomplete="on">
    @csrf @method('PUT')
    <div class="mb-4">
        <label for="name" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Name</label>
        <input type="text" name="name" id="name" value="{{ $user->name }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required autocomplete="name">
    </div>
    <div class="mb-4">
        <label for="email" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Email</label>
        <input type="email" name="email" id="email" value="{{ $user->email }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required autocomplete="email">
    </div>
    <div class="mb-4">
        <label for="password" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">New Password (leave blank)</label>
        <input type="password" name="password" id="password" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" autocomplete="new-password">
    </div>
    <div class="mb-4">
        <label for="role_id" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Role</label>
        <select name="role_id" id="role_id" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-4">
        <label for="phone" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Phone</label>
        <input type="text" name="phone" id="phone" value="{{ $user->phone }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" autocomplete="tel">
    </div>
    <div class="mb-4">
        <label for="address" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Address</label>
        <textarea name="address" id="address" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" rows="2" autocomplete="street-address">{{ $user->address }}</textarea>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update</button>
    <a href="{{ route('admin.users.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
</form>
@endsection
