<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\MenuItem;
use App\Services\StockService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to view your cart.');
        }

        $cartItems = Cart::with('menuItem')
            ->where('user_id', auth()->id())
            ->whereHas('menuItem')
            ->get();

        $total = $cartItems->sum(function ($item) {
            return (int) $item->quantity * $item->menuItem->price;
        });

        return view('customer.cart.index', compact('cartItems', 'total'));
    }

    public function add(Request $request, MenuItem $menuItem)
    {
        if (!auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Please login to add items to cart.'], 401);
            }
            return redirect()->route('login')->with('error', 'Please login to add items to cart.');
        }

        if (! $menuItem->is_available) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'This item is not available.'], 400);
            }

            return back()->with('error', 'This item is not available.');
        }

        $quantity = max(1, (int) $request->input('quantity', 1));

        try {
            $cartItem = Cart::where('user_id', auth()->id())
                ->where('menu_item_id', $menuItem->id)
                ->first();

            $menuItem->loadMissing('stockItems');

            $newQuantity = $cartItem ? ((int) $cartItem->quantity + $quantity) : $quantity;
            $fakeCart = (object) [
                'menuItem' => $menuItem,
                'quantity' => $newQuantity,
            ];

            $unavailable = StockService::checkStockAvailability([$fakeCart]);

            if (! empty($unavailable)) {
                $messages = array_map(function ($item) {
                    return "{$item['menu_item']} (needs {$item['required']}, only {$item['available']} available)";
                }, $unavailable);

                $errorMsg = 'Cannot add item: out of stock - '.implode(', ', $messages);
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 400);
                }

                return back()->with('error', $errorMsg);
            }

            if ($cartItem) {
                $cartItem->quantity = (int) $cartItem->quantity + (int) $quantity;
                $cartItem->notes = $request->input('notes', $cartItem->notes);
                $cartItem->save();
            } else {
                Cart::create([
                    'user_id' => auth()->id(),
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'notes' => $request->input('notes'),
                ]);
            }

            if ($request->wantsJson()) {
                $cartCount = Cart::where('user_id', auth()->id())->sum('quantity');

                return response()->json([
                    'success' => true,
                    'message' => 'Item(s) added to cart.',
                    'cartCount' => $cartCount,
                ]);
            }

            return redirect()->route('cart')->with('success', 'Item(s) added to cart.');
            
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error adding item to cart: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Cart $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $cartItem->update([
            'quantity' => (int) $request->quantity,
            'notes' => $request->input('notes', $cartItem->notes),
        ]);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Cart $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized.');
        }

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart.');
    }
}
