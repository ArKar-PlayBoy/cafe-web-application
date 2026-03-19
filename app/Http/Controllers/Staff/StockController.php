<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderRejection;
use App\Models\StockItem;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        return view('staff.stock.index', [
            'stockItems' => StockItem::orderBy('name')->get(),
            'alerts' => StockService::getUnreadAlerts(),
        ]);
    }

    public function addStockForm(StockItem $stock)
    {
        $this->authorize('stock.manage');

        return view('staff.stock.in', compact('stock'));
    }

    public function addStock(Request $request, StockItem $stock)
    {
        $this->authorize('stock.manage');
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'cost' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'note' => 'nullable|string|max:255',
        ]);

        StockService::addStock($stock->id, $request->quantity, $request->cost, $request->expiry_date ? \Carbon\Carbon::parse($request->expiry_date) : null, $request->note);

        return redirect()->route('staff.stock.index')->with('success', 'Stock added.');
    }

    public function wasteForm(StockItem $stock)
    {
        $this->authorize('stock.waste');

        return view('staff.stock.waste', compact('stock'));
    }

    public function logWaste(Request $request, StockItem $stock)
    {
        $this->authorize('stock.waste');
        $request->validate([
            'quantity' => 'required|integer|min:1|max:'.$stock->current_quantity,
            'reason' => 'required|string|max:255',
            'note' => 'nullable|string|max:255',
        ]);

        StockService::logWaste($stock->id, $request->quantity, $request->reason, $request->note);

        return redirect()->route('staff.stock.index')->with('success', 'Waste logged.');
    }

    public function adjustForm(StockItem $stock)
    {
        $this->authorize('stock.adjust');

        return view('staff.stock.adjust', compact('stock'));
    }

    public function adjustStock(Request $request, StockItem $stock)
    {
        $this->authorize('stock.adjust');
        $request->validate([
            'current_quantity' => 'required|integer|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        StockService::adjustStock($stock->id, $request->current_quantity, $request->note);

        return redirect()->route('staff.stock.index')->with('success', 'Stock quantity adjusted.');
    }

    public function alerts()
    {
        return view('staff.stock.alerts', ['alerts' => StockService::getUnreadAlerts()]);
    }

    public function rejectOrder(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        OrderRejection::create([
            'order_id' => $order->id,
            'user_id' => auth('staff')->id(),
            'reason' => $request->reason,
            'note' => $request->note,
        ]);

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Order rejected.');
    }
}
