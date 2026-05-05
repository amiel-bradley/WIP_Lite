<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $assignments = Assignment::with(['employee', 'campaign', 'manager', 'position'])
            ->latest()
            ->paginate(20);

        return response()->json($assignments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'manager_id' => 'nullable|exists:employees,id',
            'position_id' => 'required|exists:positions,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $existingActive = Assignment::where('employee_id', $validated['employee_id'])
            ->where('status', 'active')
            ->where(function ($query) use ($validated) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $validated['start_date']);
            })
            ->exists();

        if ($existingActive) {
            return response()->json([
                'error' => "L'employé a déjà une affectation active sur cette période."
            ], 422);
        }

        $assignment = Assignment::create([
            'employee_id' => $validated['employee_id'],
            'campaign_id' => $validated['campaign_id'],
            'manager_id' => $validated['manager_id'] ?? null,
            'position_id' => $validated['position_id'],
            'status' => 'active',
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
        ]);

        $this->logHistory($assignment, 'assign', null, $request->input('reason'));

        return response()->json(
            $assignment->load(['employee', 'campaign', 'manager', 'position']),
            201
        );
    }

    public function show(Assignment $assignment)
    {
        return response()->json(
            $assignment->load(['employee', 'campaign', 'manager', 'position'])
        );
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'manager_id' => 'nullable|exists:employees,id',
            'position_id' => 'nullable|exists:positions,id',
            'status' => 'in:active,terminated,suspended',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $oldData = $assignment->only(['manager_id', 'campaign_id', 'status']);
        $actionType = 'transfer';

        if (isset($validated['status']) && $validated['status'] === 'terminated' && $oldData['status'] !== 'terminated') {
            $actionType = 'release';
        }

        $assignment->update($validated);

        $this->logHistory(
            $assignment,
            $actionType,
            $oldData,
            $request->input('reason')
        );

        return response()->json(
            $assignment->load(['employee', 'campaign', 'manager', 'position'])
        );
    }

    public function destroy(Assignment $assignment)
    {
        $this->logHistory($assignment, 'release', null, 'Suppression de l\'affectation');
        $assignment->delete();

        return response()->json([
            'message' => 'Affectation supprimée'
        ]);
    }

    protected function logHistory(Assignment $assignment, string $actionType, ?array $oldData, ?string $reason = null)
    {
        AssignmentHistory::create([
            'assignment_id' => $assignment->id,
            'employee_id' => $assignment->employee_id,
            'old_manager_id' => $oldData['manager_id'] ?? null,
            'new_manager_id' => $assignment->manager_id,
            'old_campaign_id' => $oldData['campaign_id'] ?? null,
            'new_campaign_id' => $assignment->campaign_id,
            'action_type' => $actionType,
            'changed_by' => Auth::id(),
            'reason' => $reason,
        ]);
    }
}
