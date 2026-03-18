<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuItemResource;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Get all menu items
     */
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::with('category')->where('is_available', true);

        if ($request->has('category')) {
            $categoryId = (int) $request->category;
            if ($categoryId > 0) {
                $query->where('category_id', $categoryId);
            }
        }

        $menuItems = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => MenuItemResource::collection($menuItems),
            'meta' => [
                'current_page' => $menuItems->currentPage(),
                'last_page' => $menuItems->lastPage(),
                'per_page' => $menuItems->perPage(),
                'total' => $menuItems->total(),
            ],
        ]);
    }

    /**
     * Get single menu item
     */
    public function show(int $id): JsonResponse
    {
        $menuItem = MenuItem::with('category')->find($id);

        if (! $menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new MenuItemResource($menuItem),
        ]);
    }
}
