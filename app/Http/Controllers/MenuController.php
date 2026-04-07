<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category');
        $search = $request->get('search');

        $query = MenuItem::with('category')
            ->where('is_available', true);

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($search) {
            $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('name', 'like', "%{$escapedSearch}%")
                    ->orWhere('description', 'like', "%{$escapedSearch}%");
            });
        }

        $menuItems = $query->get();
        $categories = Category::all();
        $initialCartCount = auth()->check() ? Cart::where('user_id', auth()->id())->sum('quantity') : 0;

        return view('customer.menu.index', compact('menuItems', 'categories', 'initialCartCount'));
    }
}
