<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $this->authorize('categories.view');

        $categories = Category::withCount('menuItems')->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize('categories.create');

        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $this->authorize('categories.create');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->whereNull('deleted_at'),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->whereNull('deleted_at'),
            ],
        ]);

        $validated['name'] = strip_tags($validated['name']);
        $validated['slug'] = strip_tags($validated['slug']);

        $category = Category::create($validated);

        AuditLog::create([
            'user_id' => auth('admin')->id(),
            'action' => 'category.created',
            'resource_type' => 'Category',
            'resource_id' => $category->id,
            'new_values' => $validated,
            'is_critical' => false,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $this->authorize('categories.edit');

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('categories.edit');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->whereNull('deleted_at')->ignore($category->id),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->whereNull('deleted_at')->ignore($category->id),
            ],
        ]);

        $validated['name'] = strip_tags($validated['name']);
        $validated['slug'] = strip_tags($validated['slug']);

        $oldValues = $category->toArray();
        $category->update($validated);

        AuditLog::create([
            'user_id' => auth('admin')->id(),
            'action' => 'category.updated',
            'resource_type' => 'Category',
            'resource_id' => $category->id,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'is_critical' => false,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('categories.delete');

        $currentUser = auth('admin')->user();

        if (PermissionService::requiresApproval('categories.delete', 'Category', $category->id)) {
            PermissionService::requestApproval(
                $currentUser,
                'categories.delete',
                'Category',
                $category->id,
                $category->toArray(),
                'Delete category: '.$category->name.' (has '.$category->menuItems()->count().' menu items)'
            );

            return back()->with('info', 'Delete request submitted. Waiting for superadmin approval.');
        }

        $oldValues = $category->toArray();
        $category->delete();

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'category.deleted',
            'resource_type' => 'Category',
            'resource_id' => $category->id,
            'old_values' => $oldValues,
            'is_critical' => true,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
