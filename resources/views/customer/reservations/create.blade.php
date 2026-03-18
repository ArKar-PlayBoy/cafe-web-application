@extends('layouts.app')

@section('title', 'Make Reservation')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('reservations.index') }}" class="text-green-600 dark:text-green-400 hover:underline mb-4 inline-block">&larr; Back to Reservations</a>
    
    <h1 class="text-3xl font-serif font-bold mb-6">Make a Reservation</h1>

    <form action="{{ route('reservations.store') }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 max-w-2xl">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Your Name</label>
                <input type="text" name="customer_name" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required>
            </div>
            <div>
                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Phone Number</label>
                <input type="tel" name="customer_phone" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2" value="{{ old('customer_phone', auth()->user()->phone ?? '') }}" placeholder="09xxxxxxxxx" required>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <div>
                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Date</label>
                <input type="date" name="reservation_date" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2" min="{{ date('Y-m-d') }}" required>
            </div>
            <div>
                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Time</label>
                <input type="time" name="reservation_time" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2" required>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Party Size</label>
            <select name="party_size" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2" required>
                @for($i = 1; $i <= 20; $i++)
                <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'person' : 'persons' }}</option>
                @endfor
            </select>
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Table (Optional)</label>
            <select name="table_id" id="tableSelect" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2">
                <option value="">Auto-assign</option>
                @foreach($tables as $table)
                <option value="{{ $table->id }}" data-capacity="{{ $table->capacity }}">Table {{ $table->table_number }} (Capacity: {{ $table->capacity }})</option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" id="tableCapacityHint"></p>
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Notes</label>
            <textarea name="notes" class="w-full border dark:border-gray-600 dark:bg-gray-700 rounded-lg px-3 py-2" rows="3" placeholder="Special requests..."></textarea>
        </div>
        <div id="availabilityStatus" class="mt-4 hidden">
            <div class="flex items-center gap-2">
                <span id="availabilityIcon"></span>
                <span id="availabilityMessage" class="text-sm"></span>
            </div>
        </div>
        <button type="submit" id="submitBtn" class="mt-6 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors font-semibold">Confirm Reservation</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tableSelect = document.getElementById('tableSelect');
    var partySizeSelect = document.querySelector('select[name="party_size"]');
    var dateInput = document.querySelector('input[name="reservation_date"]');
    var timeInput = document.querySelector('input[name="reservation_time"]');
    var capacityHint = document.getElementById('tableCapacityHint');
    var availabilityStatus = document.getElementById('availabilityStatus');
    var availabilityIcon = document.getElementById('availabilityIcon');
    var availabilityMessage = document.getElementById('availabilityMessage');
    var submitBtn = document.getElementById('submitBtn');
    var maxPartySize = 20;
    var checkTimeout = null;

    function updatePartySizeOptions() {
        var selectedOption = tableSelect.options[tableSelect.selectedIndex];
        var capacity = selectedOption.getAttribute('data-capacity');
        var currentValue = partySizeSelect.value;

        partySizeSelect.innerHTML = '';

        if (!capacity) {
            for (var i = 1; i <= maxPartySize; i++) {
                var option = document.createElement('option');
                option.value = i;
                option.textContent = i + ' ' + (i === 1 ? 'person' : 'persons');
                partySizeSelect.appendChild(option);
            }
            capacityHint.textContent = '';
        } else {
            capacity = parseInt(capacity);
            for (var i = 1; i <= capacity; i++) {
                var option = document.createElement('option');
                option.value = i;
                option.textContent = i + ' ' + (i === 1 ? 'person' : 'persons');
                partySizeSelect.appendChild(option);
            }
            capacityHint.textContent = 'Maximum ' + capacity + ' persons for this table';
            capacityHint.className = 'text-sm text-green-600 dark:text-green-400 mt-1';

            if (currentValue > capacity) {
                partySizeSelect.value = capacity;
            }
        }
        
        checkAvailability();
    }

    function checkAvailability() {
        var tableId = tableSelect.value;
        var date = dateInput.value;
        var time = timeInput.value;
        var partySize = partySizeSelect.value;

        if (!tableId || !date || !time || !partySize) {
            availabilityStatus.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            return;
        }

        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(function() {
            availabilityStatus.classList.remove('hidden');
            availabilityIcon.innerHTML = '<svg class="animate-spin h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            availabilityMessage.textContent = 'Checking availability...';
            availabilityMessage.className = 'text-sm text-gray-500';

            var url = '{{ route("reservations.check-availability") }}?table_id=' + tableId + '&reservation_date=' + date + '&reservation_time=' + time + '&party_size=' + partySize;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        availabilityIcon.innerHTML = '<svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                        availabilityMessage.textContent = data.message;
                        availabilityMessage.className = 'text-sm text-green-600 dark:text-green-400';
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    } else {
                        availabilityIcon.innerHTML = '<svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                        availabilityMessage.textContent = data.message;
                        availabilityMessage.className = 'text-sm text-red-600 dark:text-red-400';
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                })
                .catch(error => {
                    availabilityStatus.classList.add('hidden');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
        }, 500);
    }

    tableSelect.addEventListener('change', updatePartySizeOptions);
    partySizeSelect.addEventListener('change', checkAvailability);
    dateInput.addEventListener('change', checkAvailability);
    timeInput.addEventListener('change', checkAvailability);
});
</script>
@endsection
