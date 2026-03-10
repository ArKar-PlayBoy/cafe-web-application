<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MenuController extends Controller
{
    public function index()
    {
        $this->authorize('menu.view');

        $menuItems = MenuItem::with('category')->latest()->get();

        return view('admin.menu.index', compact('menuItems'));
    }

    public function create()
    {
        $this->authorize('menu.create');

        $categories = Category::all();

        return view('admin.menu.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('menu.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'featured_image' => 'required|url|max:2048',
            'is_available' => 'sometimes|boolean',
        ]);
        $validated['name'] = strip_tags($validated['name']);
        $validated['description'] = strip_tags($validated['description'] ?? '');
        $validated['is_available'] = $request->boolean('is_available');

        $menuItem = MenuItem::create($validated);

        // Log the action
        AuditLog::create([
            'user_id' => auth('admin')->id(),
            'action' => 'menu.created',
            'resource_type' => 'MenuItem',
            'resource_id' => $menuItem->id,
            'new_values' => $validated,
            'is_critical' => false,
        ]);

        return redirect()->route('admin.menu.index')->with('success', 'Menu item created successfully.');
    }

    public function edit(MenuItem $menu)
    {
        $this->authorize('menu.edit');

        $categories = Category::all();

        return view('admin.menu.edit', compact('menu', 'categories'));
    }

    public function update(Request $request, MenuItem $menu)
    {
        $this->authorize('menu.edit');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'featured_image' => 'required|url|max:2048',
            'is_available' => 'sometimes|boolean',
        ]);
        $validated['name'] = strip_tags($validated['name']);
        $validated['description'] = strip_tags($validated['description'] ?? '');
        $validated['is_available'] = $request->boolean('is_available');

        $oldValues = $menu->toArray();
        $menu->update($validated);

        // Log the action
        AuditLog::create([
            'user_id' => auth('admin')->id(),
            'action' => 'menu.updated',
            'resource_type' => 'MenuItem',
            'resource_id' => $menu->id,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'is_critical' => false,
        ]);

        return redirect()->route('admin.menu.index')->with('success', 'Menu item updated successfully.');
    }

    public function show(MenuItem $menu)
    {
        $this->authorize('menu.view');

        return view('admin.menu.show', compact('menu'));
    }

    public function destroy(MenuItem $menu)
    {
        $this->authorize('menu.delete');

        $currentUser = auth('admin')->user();

        // Use Gate for complex permission checking
        $canDelete = Gate::allows('delete-menu-item', $menu);

        if (!$canDelete) {
            if ($menu->hasActiveOrders()) {
                return back()->with('error', 'Cannot delete menu item with active orders. Please complete or cancel all orders first.');
            }
            return back()->with('error', 'You do not have permission to delete this menu item.');
        }

        $oldValues = $menu->toArray();

        // Soft delete
        $menu->delete();

        // Log the action
        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'menu.deleted',
            'resource_type' => 'MenuItem',
            'resource_id' => $menu->id,
            'old_values' => $oldValues,
            'is_critical' => false,
        ]);

        return redirect()->route('admin.menu.index')->with('success', 'Menu item deleted successfully.');
    }
}
