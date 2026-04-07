@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-serif font-bold mb-6">Profile Settings</h1>

    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-semibold mb-4">Profile Information</h2>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')
                <div class="grid gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-4 py-2" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-4 py-2" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Address</label>
                        <textarea name="address" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-4 py-2" rows="3">{{ old('address', $user->address) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">Save Changes</button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-semibold mb-4">
                {{ $user->hasCustomPassword() ? 'Update Password' : 'Set Password' }}
            </h2>

            @if(!$user->hasCustomPassword())
            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800">
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    You signed up with Google. Set a password to also log in with your email.
                </p>
            </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('put')
                <div class="grid gap-6 mb-6">
                    @if($user->hasCustomPassword())
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Current Password</label>
                        <input type="password" name="current_password" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-4 py-2">
                        @error('current_password', 'updatePassword')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">New Password</label>
                        <input type="password" name="password" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-4 py-2">
                        @error('password', 'updatePassword')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-4 py-2">
                    </div>
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    {{ $user->hasCustomPassword() ? 'Update Password' : 'Set Password' }}
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold mb-1">Connected Accounts</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage your linked social login accounts</p>
                </div>
                <a href="{{ route('user.social.accounts') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                    Manage Accounts
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold mb-1">Payment Methods</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage your saved cards for faster checkout</p>
                </div>
                <a href="{{ route('payment-methods.index') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                    Manage Cards
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
