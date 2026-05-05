<?php

namespace App\Http\Controllers;

use App\Models\AssignmentHistory;
use Illuminate\Support\Facades\Auth;

class AssignmentHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $histories = AssignmentHistory::with([
                'assignment',
                'employee',
                'changer',
                'oldManager',
                'newManager',
                'oldCampaign',
                'newCampaign'
            ])
            ->latest()
            ->paginate(20);

        return response()->json($histories);
    }

    public function show(AssignmentHistory $assignmentHistory)
    {
        return response()->json(
            $assignmentHistory->load([
                'assignment',
                'employee',
                'changer',
                'oldManager',
                'newManager',
                'oldCampaign',
                'newCampaign'
            ])
        );
    }

    public function destroy(AssignmentHistory $assignmentHistory)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $assignmentHistory->delete();

        return response()->json([
            'message' => 'Historique supprimé avec succès'
        ]);
    }
}
