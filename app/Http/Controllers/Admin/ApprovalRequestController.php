<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApprovalRequestController extends Controller
{
    public function index()
    {
        $this->authorize('system.approve_critical');

        $requests = ApprovalRequest::with(['requester', 'approver'])
            ->latest()
            ->paginate(20);

        $pendingCount = ApprovalRequest::where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();

        return view('admin.approval_requests.index', compact('requests', 'pendingCount'));
    }

    public function pending()
    {
        $this->authorize('system.approve_critical');

        $requests = ApprovalRequest::with(['requester', 'approver'])
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->paginate(20);

        $pendingCount = ApprovalRequest::where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();

        return view('admin.approval_requests.pending', compact('requests', 'pendingCount'));
    }

    public function myRequests()
    {
        $requests = ApprovalRequest::with(['approver'])
            ->where('requested_by', auth('admin')->id())
            ->latest()
            ->paginate(20);

        return view('admin.approval_requests.my_requests', compact('requests'));
    }

    public function show(ApprovalRequest $approvalRequest)
    {
        // Allow viewing if user is super admin or is the requester
        if (!auth('admin')->user()->isSuperAdmin() && $approvalRequest->requested_by !== auth('admin')->id()) {
            abort(403, 'Unauthorized.');
        }

        $approvalRequest->load(['requester', 'approver']);

        return view('admin.approval_requests.show', compact('approvalRequest'));
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest)
    {
        $this->authorize('system.approve_critical');

        if (!$approvalRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $currentUser = auth('admin')->user();

        // Cannot approve your own request
        if ($approvalRequest->requested_by === $currentUser->id) {
            return back()->with('error', 'You cannot approve your own request.');
        }

        $approvalRequest->approve($currentUser, $request->input('notes'));

        // Execute the approved action
        $this->executeApprovedAction($approvalRequest);

        // Log the approval
        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'approval_request.approved',
            'resource_type' => 'ApprovalRequest',
            'resource_id' => $approvalRequest->id,
            'new_values' => [
                'action' => $approvalRequest->action,
                'resource_type' => $approvalRequest->resource_type,
                'resource_id' => $approvalRequest->resource_id,
            ],
            'is_critical' => true,
        ]);

        return redirect()->route('admin.approval-requests.index')
            ->with('success', 'Request approved successfully.');
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest)
    {
        $this->authorize('system.approve_critical');

        if (!$approvalRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $currentUser = auth('admin')->user();

        // Cannot reject your own request
        if ($approvalRequest->requested_by === $currentUser->id) {
            return back()->with('error', 'You cannot reject your own request.');
        }

        $approvalRequest->reject($currentUser, $request->input('rejection_reason'));

        // Log the rejection
        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'approval_request.rejected',
            'resource_type' => 'ApprovalRequest',
            'resource_id' => $approvalRequest->id,
            'new_values' => [
                'action' => $approvalRequest->action,
                'rejection_reason' => $request->input('rejection_reason'),
            ],
            'is_critical' => true,
        ]);

        return redirect()->route('admin.approval-requests.index')
            ->with('success', 'Request rejected successfully.');
    }

    public function cancel(ApprovalRequest $approvalRequest)
    {
        $currentUser = auth('admin')->user();

        // Can only cancel your own pending requests
        if ($approvalRequest->requested_by !== $currentUser->id) {
            return back()->with('error', 'You can only cancel your own requests.');
        }

        if (!$approvalRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $approvalRequest->update(['status' => 'expired']);

        // Log the cancellation
        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'approval_request.cancelled',
            'resource_type' => 'ApprovalRequest',
            'resource_id' => $approvalRequest->id,
            'is_critical' => false,
        ]);

        return redirect()->route('admin.approval-requests.my')
            ->with('success', 'Request cancelled successfully.');
    }

    /**
     * Execute the action that was approved
     */
    protected function executeApprovedAction(ApprovalRequest $approvalRequest): void
    {
        $action = $approvalRequest->action;
        $payload = $approvalRequest->payload;
        $resourceType = $approvalRequest->resource_type;
        $resourceId = $approvalRequest->resource_id;

        switch ($action) {
            case 'categories.delete':
                if ($resourceType === 'Category' && $resourceId) {
                    $category = Category::withTrashed()->find($resourceId);
                    if ($category) {
                        // Force delete the category
                        $category->forceDelete();
                    }
                }
                break;

            case 'menu.delete':
                // Handle menu item deletion
                break;

            case 'users.delete':
                // Handle user deletion
                break;

            default:
                // Log unknown action
                break;
        }
    }
}
