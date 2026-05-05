<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $campaigns = Campaign::with('assignments.employee', 'assignments.manager', 'assignments.position')->latest()->get();
        } else {
            $campaigns = Campaign::whereHas('assignments', function ($q) use ($user) {
                $q->where('employee_id', $user->id);
            })->with(['assignments' => function ($q) use ($user) {
                $q->where('employee_id', $user->id)->with('employee', 'manager', 'position');
            }])->get();
        }

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Campaigns/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive,finished',
        ]);

        $campaign = Campaign::create($validated);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campagne créée avec succès.');
    }

    public function show(Campaign $campaign): Response
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $campaign->load('assignments.employee', 'assignments.manager', 'assignments.position');
        } else {
            $campaign->load(['assignments' => function ($q) use ($user) {
                $q->where('employee_id', $user->id)->with('employee', 'manager', 'position');
            }]);
        }

        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
        ]);
    }

    public function edit(Campaign $campaign): Response
    {
        return Inertia::render('Campaigns/Edit', [
            'campaign' => $campaign,
        ]);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive,finished',
        ]);

        $campaign->update($validated);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campagne mise à jour.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')
            ->with('success', 'Campagne supprimée.');
    }
}
