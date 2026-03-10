<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CafeTable;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $this->authorize('tables.view');

        $tables = CafeTable::all();

        return view('admin.tables.index', compact('tables'));
    }

    public function create()
    {
        $this->authorize('tables.create');

        return view('admin.tables.create');
    }

    public function store(Request $request)
    {
        $this->authorize('tables.create');

        $validated = $request->validate([
            'table_number' => 'required|string|unique:cafe_tables,table_number',
            'capacity' => 'required|integer|min:1',
        ]);

        CafeTable::create($validated);

        // Log the action
        AuditLog::create([
            'user_id' => auth('admin')->id(),
            'action' => 'table.created',
            'resource_type' => 'CafeTable',
            'new_values' => $validated,
            'is_critical' => false,
        ]);

        return redirect()->route('admin.tables.index')->with('success', 'Table created successfully.');
    }

    public function edit(CafeTable $table)
    {
        $this->authorize('tables.edit');

        return view('admin.tables.edit', compact('table'));
    }

    public function update(Request $request, CafeTable $table)
    {
        $this->authorize('tables.edit');

        $validated = $request->validate([
            'table_number' => 'required|string|unique:cafe_tables,table_number,'.$table->id,
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved',
        ]);

        $table->update($validated);

        return redirect()->route('admin.tables.index')->with('success', 'Table updated successfully.');
    }

    public function destroy(CafeTable $table)
    {
        $this->authorize('tables.delete');

        AuditLog::create([
            'user_id' => auth('admin')->id(),
            'action' => 'table.deleted',
            'resource_type' => 'CafeTable',
            'resource_id' => $table->id,
            'old_values' => $table->toArray(),
            'is_critical' => false,
        ]);

        $table->delete();

        return redirect()->route('admin.tables.index')->with('success', 'Table deleted successfully.');
    }
}
