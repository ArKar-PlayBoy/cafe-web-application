<?php

namespace App\Http\Controllers;

use App\Models\CafeTable;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with('table')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $tables = CafeTable::where('status', 'available')
            ->where('capacity', '>=', 1)
            ->get();

        return view('customer.reservations.create', compact('tables'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'party_size' => 'required|integer|min:1|max:20',
            'table_id' => 'nullable|exists:cafe_tables,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $reservationDateTime = \Carbon\Carbon::parse(
            $request->reservation_date.' '.$request->reservation_time
        );

        // Define conflict window: ±2 hours
        $windowStart = $reservationDateTime->copy()->subHours(2);
        $windowEnd = $reservationDateTime->copy()->addHours(2);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $windowStart, $windowEnd) {
            $tableId = null;

            if ($request->table_id) {
                // Prevent race condition using locking
                $table = CafeTable::where('id', $request->table_id)
                    ->lockForUpdate()
                    ->first();

                if (! $table || $table->status !== 'available' || $table->capacity < $request->party_size) {
                    return back()->with('error', 'Selected table is not available or too small for your party.')
                        ->withInput();
                }

                // Check for conflicting reservations on this specific table
                $conflict = Reservation::where('table_id', $table->id)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->where(function ($query) use ($windowStart, $windowEnd) {
                        $query->whereRaw(
                            "CONCAT(reservation_date, ' ', reservation_time) BETWEEN ? AND ?",
                            [$windowStart->format('Y-m-d H:i:s'), $windowEnd->format('Y-m-d H:i:s')]
                        );
                    })
                    ->exists();

                if ($conflict) {
                    return back()->with('error', 'This table is already reserved around that time. Please choose a different table or time.')
                        ->withInput();
                }

                $tableId = $table->id;
            } else {
                // Auto-assign: find an available table with no conflicts
                $conflictingTableIds = Reservation::whereIn('status', ['pending', 'confirmed'])
                    ->whereNotNull('table_id')
                    ->where(function ($query) use ($windowStart, $windowEnd) {
                        $query->whereRaw(
                            "CONCAT(reservation_date, ' ', reservation_time) BETWEEN ? AND ?",
                            [$windowStart->format('Y-m-d H:i:s'), $windowEnd->format('Y-m-d H:i:s')]
                        );
                    })
                    ->pluck('table_id');

                $availableTable = CafeTable::where('status', 'available')
                    ->where('capacity', '>=', $request->party_size)
                    ->whereNotIn('id', $conflictingTableIds)
                    ->lockForUpdate()
                    ->first();

                if ($availableTable) {
                    $tableId = $availableTable->id;
                }
                // If no table available, reservation is created without a table (waitlisted)
            }

            Reservation::create([
                'user_id' => auth()->id(),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'table_id' => $tableId,
                'reservation_date' => $request->reservation_date,
                'reservation_time' => $request->reservation_time,
                'party_size' => $request->party_size,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            $message = $tableId
                ? 'Reservation made successfully!'
                : 'Reservation submitted! No tables are currently available for that time — we will confirm once a table opens up.';

            return redirect()->route('reservations.index')->with('success', $message);
        });
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'table_id' => 'nullable|required_without:table_number|exists:cafe_tables,id',
            'table_number' => 'nullable|required_without:table_id|exists:cafe_tables,table_number',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'party_size' => 'required|integer|min:1|max:20',
        ]);

        $tableId = $request->table_id;

        if (! $tableId && $request->table_number) {
            $table = CafeTable::where('table_number', $request->table_number)->first();
            $tableId = $table?->id;
        }

        if (! $tableId) {
            return response()->json([
                'available' => false,
                'message' => 'Please select a table',
            ]);
        }

        $table = CafeTable::find($tableId);

        if ($table->status !== 'available') {
            return response()->json([
                'available' => false,
                'message' => 'This table is not available',
            ]);
        }

        if ($table->capacity < $request->party_size) {
            return response()->json([
                'available' => false,
                'message' => 'Table capacity is too small for '.$request->party_size.' persons',
            ]);
        }

        $reservationDateTime = \Carbon\Carbon::parse(
            $request->reservation_date.' '.$request->reservation_time
        );

        $windowStart = $reservationDateTime->copy()->subHours(2);
        $windowEnd = $reservationDateTime->copy()->addHours(2);

        $conflict = Reservation::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($query) use ($windowStart, $windowEnd) {
                $query->whereRaw(
                    "CONCAT(reservation_date, ' ', reservation_time) BETWEEN ? AND ?",
                    [$windowStart->format('Y-m-d H:i:s'), $windowEnd->format('Y-m-d H:i:s')]
                );
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'available' => false,
                'message' => 'This table is already reserved around that time',
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Table is available!',
        ]);
    }
}
