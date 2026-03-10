<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\StockAlert;
use App\Models\StockItem;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $this->authorize('stock.view');

        $stockItems = StockItem::with(['alerts' => fn ($q) => $q->where('is_read', false)])->orderBy('name')->get();

        return view('admin.stock.index', compact('stockItems'));
    }

    public function create()
    {
        $this->authorize('stock.manage');

        return view('admin.stock.create');
    }

    public function store(Request $request)
    {
        $this->authorize('stock.manage');

        $request->validate([
            'name' => 'required|string|max:255|unique:stock_items,name',
            'current_quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:stock_items,barcode',
            'bin_location' => 'nullable|string|max:255',
            'category' => 'required|in:ingredient,supply',
        ]);

        StockItem::create($request->only([
            'name', 'current_quantity', 'min_quantity', 'barcode', 'bin_location', 'category',
        ]));

        return redirect()->route('admin.stock.index')->with('success', 'Stock item created.');
    }

    public function edit(StockItem $stock)
    {
        $this->authorize('stock.manage');

        return view('admin.stock.edit', compact('stock'));
    }

    public function update(Request $request, StockItem $stock)
    {
        $this->authorize('stock.manage');

        $request->validate([
            'name' => 'required|string|max:255|unique:stock_items,name,'.$stock->id,
            'current_quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:stock_items,barcode,'.$stock->id,
            'bin_location' => 'nullable|string|max:255',
            'category' => 'required|in:ingredient,supply',
        ]);

        try {
            $stock->update($request->only([
                'name', 'current_quantity', 'min_quantity', 'barcode', 'bin_location', 'category',
            ]));

            return redirect()->route('admin.stock.index')->with('success', 'Stock item updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update stock item. Please try again.')->withInput();
        }
    }

    public function destroy(StockItem $stock)
    {
        $this->authorize('stock.manage');

        $stock->delete();

        return redirect()->route('admin.stock.index')->with('success', 'Stock item deleted.');
    }

    public function movements(StockItem $stock)
    {
        $this->authorize('stock.view');

        $movements = StockService::getStockHistory($stock->id);

        return view('admin.stock.movements', compact('stock', 'movements'));
    }

    public function recipe(StockItem $stock)
    {
        $this->authorize('stock.manage');

        $menuItems = MenuItem::with('stockItems')->get();

        return view('admin.stock.recipe', compact('stock', 'menuItems'));
    }

    public function updateRecipe(Request $request, StockItem $stock)
    {
        $this->authorize('stock.manage');

        $request->validate(['recipes' => 'required|array', 'recipes.*' => 'nullable|integer|min:0']);

        $stock->menuItems()->detach();
        foreach ($request->recipes as $menuItemId => $quantity) {
            if ($quantity > 0) {
                $stock->menuItems()->attach($menuItemId, ['quantity_needed' => $quantity]);
            }
        }

        return redirect()->route('admin.stock.index')->with('success', 'Recipe updated.');
    }

    public function batches()
    {
        return view('admin.stock.batches', ['batches' => StockService::getAllBatches()]);
    }

    public function alerts()
    {
        $alerts = StockAlert::with('stockItem')->orderBy('created_at', 'desc')->get();

        return view('admin.stock.alerts', compact('alerts'));
    }

    public function markAlertRead(StockAlert $alert)
    {
        StockService::markAlertAsRead($alert->id);

        return back()->with('success', 'Alert marked as read.');
    }

    public function expiring()
    {
        return view('admin.stock.expiring', ['expiringItems' => StockService::checkExpiring(7)]);
    }

    public function addStock(Request $request, StockItem $stock)
    {
        $this->authorize('stock.manage');

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'cost' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today',
            'note' => 'nullable|string|max:255',
        ]);

        StockService::addStock($stock->id, $request->quantity, $request->cost, $request->expiry_date ? \Carbon\Carbon::parse($request->expiry_date) : null, $request->note);

        return back()->with('success', 'Stock added.');
    }

    public function adjustStock(Request $request, StockItem $stock)
    {
        $this->authorize('stock.adjust');

        $request->validate([
            'current_quantity' => 'required|integer|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        StockService::adjustStock($stock->id, $request->current_quantity, $request->note);

        return back()->with('success', 'Stock adjusted.');
    }
}
