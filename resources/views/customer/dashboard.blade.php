@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-serif font-bold mb-2">Welcome back, {{ Auth::user()->name }}</h1>
    <p class="text-gray-600 dark:text-gray-400 mb-8">What would you like to do today?</p>

    @if(Auth::user()->total_orders > 0)
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-xl p-6 mb-8 text-white">
        <h2 class="text-lg font-semibold mb-4">My Stats</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white/10 rounded-lg p-4">
                <p class="text-sm opacity-80">Total Orders</p>
                <p class="text-2xl font-bold">{{ Auth::user()->total_orders }}</p>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
                <p class="text-sm opacity-80">Total Spent</p>
                <p class="text-2xl font-bold">${{ number_format(Auth::user()->total_spent, 2) }}</p>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
                <p class="text-sm opacity-80">Member Since</p>
                <p class="text-2xl font-bold">{{ Auth::user()->first_order_date ? \Carbon\Carbon::parse(Auth::user()->first_order_date)->format('M d, Y') : 'N/A' }}</p>
            </div>
            <div class="bg-white/10 rounded-lg p-4">
                <p class="text-sm opacity-80">Last Order</p>
                <p class="text-2xl font-bold">{{ Auth::user()->last_order_date ? \Carbon\Carbon::parse(Auth::user()->last_order_date)->format('M d, Y') : 'N/A' }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="{{ route('menu') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-2">Browse Menu</h2>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Explore our delicious coffee and pastries</p>
        </a>

        <a href="{{ route('cart') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-2">Your Cart</h2>
            <p class="text-gray-600 dark:text-gray-400 text-sm">View and manage your cart items</p>
        </a>

        <a href="{{ route('orders') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-2">My Orders</h2>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Track your order history</p>
        </a>

        <a href="{{ route('reservations.create') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-2">Book a Table</h2>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Reserve your spot for a cozy experience</p>
        </a>

        <a href="{{ route('reservations.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-2">Reservations</h2>
            <p class="text-gray-600 dark:text-gray-400 text-sm">View your upcoming reservations</p>
        </a>

        <a href="{{ route('profile.edit') }}" class="group bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-2">My Profile</h2>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Update your personal information</p>
        </a>
    </div>
</div>
@endsection
