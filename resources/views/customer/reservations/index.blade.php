@extends('layouts.app')

@section('title', 'My Reservations')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-serif font-bold">My Reservations</h1>
        <a href="{{ route('reservations.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-full hover:bg-green-700 transition-colors">Make Reservation</a>
    </div>

    @if($reservations->isEmpty())
    <div class="text-center py-12">
        <svg class="w-24 h-24 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-gray-500 dark:text-gray-400 text-xl">You haven't made any reservations yet</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($reservations as $reservation)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-semibold text-lg">Reservation #{{ $reservation->id }}</h3>
                    <p class="text-gray-500 dark:text-gray-400">{{ $reservation->reservation_date }} at {{ $reservation->reservation_time }}</p>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Party Size: {{ $reservation->party_size }} persons</p>
                    @if($reservation->table)
                    <p class="text-gray-600 dark:text-gray-400">Table: {{ $reservation->table->table_number }}</p>
                    @endif
                    @if($reservation->customer_phone)
                    <p class="text-gray-600 dark:text-gray-400">Phone: {{ $reservation->customer_phone }}</p>
                    @endif
                    @if($reservation->notes)
                    <p class="text-gray-600 dark:text-gray-400">Notes: {{ $reservation->notes }}</p>
                    @endif
                    @if($reservation->status === 'cancelled' && $reservation->cancellation_reason)
                    <p class="mt-2 text-red-600 dark:text-red-400">Reason: {{ $reservation->cancellation_reason }}</p>
                    @endif
                </div>
                <span class="px-3 py-1 text-sm rounded-full {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : ($reservation->status === 'confirmed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($reservation->status === 'cancelled' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300')) }}">
                    {{ ucfirst($reservation->status) }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
