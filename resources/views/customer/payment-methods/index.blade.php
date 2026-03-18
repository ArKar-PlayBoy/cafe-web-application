@extends('layouts.app')

@section('title', 'My Payment Methods')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8 py-6 sm:py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl sm:text-3xl font-serif font-bold">My Payment Methods</h1>
        <a href="{{ route('dashboard') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">
            &larr; Back to Dashboard
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
        <p class="text-green-600 dark:text-green-400 text-sm">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
        <p class="text-red-600 dark:text-red-400 text-sm">{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-6">
        @if(!$hasStripeCustomer)
        <div class="text-center py-8">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <h3 class="text-lg font-semibold mb-2">No Payment Methods</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">You haven't saved any payment methods yet.</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Complete a purchase with Stripe to save your card for future use.</p>
        </div>
        @elseif(count($paymentMethods) === 0)
        <div class="text-center py-8">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <h3 class="text-lg font-semibold mb-2">No Saved Cards</h3>
            <p class="text-gray-500 dark:text-gray-400">You don't have any saved cards.</p>
        </div>
        @else
        <div class="space-y-4">
            @foreach($paymentMethods as $pm)
            <div class="flex items-center justify-between p-4 border dark:border-gray-600 rounded-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-8 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                        @switch($pm['brand'])
                            @case('visa')
                            <svg class="h-6" viewBox="0 0 50 20" fill="none">
                                <path d="M19 1L13 19H8L12 1H19ZM26 1L24 11H31L30 1H26ZM35 1L32 11H37L40 1H35ZM21 1L18 19H23L26 1H21Z" fill="#1A1F71"/>
                            </svg>
                            @break
                            @case('mastercard')
                            <svg class="h-6" viewBox="0 0 50 30" fill="none">
                                <circle cx="18" cy="15" r="10" fill="#EB001B"/>
                                <circle cx="32" cy="15" r="10" fill="#F79E1B"/>
                            </svg>
                            @break
                            @default
                            <svg class="w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        @endswitch
                    </div>
                    <div>
                        <p class="font-medium">{{ ucfirst($pm['brand']) }} **** **** **** {{ $pm['last4'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Expires {{ $pm['exp_month'] }}/{{ $pm['exp_year'] }}</p>
                    </div>
                </div>
                <form action="{{ route('payment-methods.destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this card?')">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="payment_method_id" value="{{ $pm['id'] }}">
                    <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium px-3 py-1">
                        Remove
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Need to add a new card? 
            <a href="{{ route('checkout') }}" class="text-green-600 hover:text-green-700 font-medium">Make a purchase</a>
        </p>
    </div>
</div>
@endsection
