@extends('layouts.staff')

@section('title', 'Reservations')

@section('content')
<h1 class="text-2xl font-bold mb-6 dark:text-white">Reservations</h1>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Customer</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Phone</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Time</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Size</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Notes</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 dark:text-gray-300 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($reservations as $reservation)
            <tr class="text-slate-800 dark:text-white">
                <td class="px-4 py-4">#{{ $reservation->id }}</td>
                <td class="px-4 py-4">
                    <div class="font-medium">{{ $reservation->customer_name ?: $reservation->user->name }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $reservation->user->email }}</div>
                </td>
                <td class="px-4 py-4">{{ $reservation->customer_phone ?: '-' }}</td>
                <td class="px-4 py-4">{{ $reservation->reservation_date }}</td>
                <td class="px-4 py-4">{{ $reservation->reservation_time }}</td>
                <td class="px-4 py-4">{{ $reservation->party_size }}</td>
                <td class="px-4 py-4">
                    <span class="px-2 py-1 text-xs rounded {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : ($reservation->status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($reservation->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200')) }}">
                        {{ ucfirst($reservation->status) }}
                    </span>
                    @if($reservation->confirmed_at)
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">Confirmed {{ $reservation->confirmed_at->format('m/d H:i') }}</div>
                    @endif
                </td>
                <td class="px-4 py-4">
                    @if($reservation->status === 'cancelled' && $reservation->cancellation_reason)
                    <div class="text-red-600 dark:text-red-400 text-xs">
                        <span class="font-medium">Rejected:</span> {{ $reservation->cancellation_reason }}
                    </div>
                    @elseif($reservation->notes)
                    <div class="text-slate-600 dark:text-slate-400 text-xs">
                        {{ $reservation->notes }}
                    </div>
                    @else
                    <span class="text-slate-400 dark:text-slate-500 text-xs">-</span>
                    @endif
                </td>
                <td class="px-4 py-4">
                    <div class="flex items-center gap-2">
                        @if($reservation->status === 'pending')
                        <form action="{{ route('staff.reservations.confirm', $reservation->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700" onclick="return confirm('Confirm this reservation?')">
                                Confirm
                            </button>
                        </form>
                        <button type="button" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700" onclick="openRejectModal({{ $reservation->id }})">
                            Reject
                        </button>
                        @elseif($reservation->status === 'confirmed')
                        <button type="button" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700" data-id="{{ $reservation->id }}" data-status="{{ $reservation->status }}" onclick="openStatusModal(this)">
                            Complete
                        </button>
                        @else
                        <span class="text-slate-400 dark:text-slate-500 text-xs">-</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No reservations found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">Change Reservation Status</h3>
        <form id="statusForm" method="POST">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Status</label>
                <select name="status" id="modalStatus" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" onchange="toggleCancellationReason()">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div id="cancellationReasonDiv" class="mb-4 hidden">
                <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Cancellation Reason</label>
                <textarea name="cancellation_reason" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" rows="3" placeholder="Why is this reservation cancelled?"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">Reject Reservation</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Rejection Reason</label>
                <textarea name="cancellation_reason" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" rows="3" placeholder="Why are you rejecting this reservation?" required></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(button) {
    var id = button.getAttribute('data-id');
    var currentStatus = button.getAttribute('data-status');
    document.getElementById('statusForm').action = '/staff/reservations/' + id + '/status';
    document.getElementById('modalStatus').value = currentStatus;
    toggleCancellationReason();
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

function toggleCancellationReason() {
    const status = document.getElementById('modalStatus').value;
    const reasonDiv = document.getElementById('cancellationReasonDiv');
    reasonDiv.classList.toggle('hidden', status !== 'cancelled');
}

document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});

function openRejectModal(id) {
    document.getElementById('rejectForm').action = '/staff/reservations/' + id + '/reject';
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});
</script>
@endsection
