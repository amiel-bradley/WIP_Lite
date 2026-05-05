<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Campaign;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class HierarchyAssignmentController extends Controller
{
    protected AssignmentService $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->middleware('auth');
        $this->assignmentService = $assignmentService;
    }

    public function index(Request $request)
    {
        $query = Assignment::with(['employee', 'campaign', 'manager', 'position', 'parentAssignment', 'children']);

        if ($request->has('type')) {
            $query->type($request->type);
        }

        if ($request->has('status') && $request->status === 'active') {
            $query->active();
        }

        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        $assignments = $query->latest()->paginate(20);

        return response()->json($assignments);
    }

    public function assignCpToCampaign(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'manager_id' => 'nullable|exists:employees,id',
            'reason' => 'nullable|string',
        ]);

        try {
            $cp = Employee::findOrFail($request->employee_id);
            $campaign = Campaign::findOrFail($request->campaign_id);
            $manager = $request->manager_id ? Employee::find($request->manager_id) : null;

            $assignment = $this->assignmentService->assignCpToCampaign(
                $cp,
                $campaign,
                $manager,
                $request->reason
            );

            return response()->json($assignment, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function assignSupervisorToCp(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'cp_assignment_id' => 'required|exists:assignments,id',
            'reason' => 'nullable|string',
        ]);

        try {
            $supervisor = Employee::findOrFail($request->employee_id);
            $cpAssignment = Assignment::findOrFail($request->cp_assignment_id);

            $assignment = $this->assignmentService->assignSupervisorToCp(
                $supervisor,
                $cpAssignment,
                $request->reason
            );

            return response()->json($assignment, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function assignTcToSupervisor(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'supervisor_assignment_id' => 'required|exists:assignments,id',
            'reason' => 'nullable|string',
        ]);

        try {
            $tc = Employee::findOrFail($request->employee_id);
            $supervisorAssignment = Assignment::findOrFail($request->supervisor_assignment_id);

            $assignment = $this->assignmentService->assignTcToSupervisor(
                $tc,
                $supervisorAssignment,
                $request->reason
            );

            return response()->json($assignment, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function unassign(Request $request, Assignment $assignment)
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        try {
            $this->assignmentService->unassign($assignment, $request->reason);
            return response()->json(['message' => 'Affectation résiliée avec succès.']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(Assignment $assignment)
    {
        return response()->json(
            $assignment->load(['employee', 'campaign', 'manager', 'position', 'parentAssignment', 'children', 'histories'])
        );
    }

    public function getHierarchy(Campaign $campaign)
    {
        $assignments = Assignment::with(['employee', 'manager', 'children.employee', 'children.children.employee'])
            ->where('campaign_id', $campaign->id)
            ->whereNull('parent_assignment_id')
            ->active()
            ->get();

        return response()->json($assignments);
    }
}
