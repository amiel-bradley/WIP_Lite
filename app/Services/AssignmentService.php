<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssignmentService
{
    const ROLE_CP = 'Chef de Plateau';
    const ROLE_SUP = 'Superviseur';
    const ROLE_TC = 'Téléconseiller';
    const ROLE_ADMIN = 'admin';

    public function assignCpToCampaign(Employee $cp, Campaign $campaign, ?Employee $manager = null, ?string $reason = null): Assignment
    {
        $this->ensureIsCp($cp);
        $this->ensureIsAdmin();

        return DB::transaction(function () use ($cp, $campaign, $manager, $reason) {
            $assignment = Assignment::create([
                'employee_id' => $cp->id,
                'campaign_id' => $campaign->id,
                'manager_id' => $manager?->id,
                'position_id' => $cp->position_id,
                'assignment_type' => 'cp',
                'status' => 'active',
                'start_date' => now()->toDateString(),
            ]);

            $this->logHistory($assignment, 'assign', $reason);

            return $assignment->load(['employee', 'campaign', 'manager', 'position']);
        });
    }

    public function assignSupervisorToCp(Employee $supervisor, Assignment $cpAssignment, ?string $reason = null): Assignment
    {
        $this->ensureIsSupervisor($supervisor);
        $this->ensureIsAdmin();
        $this->ensureAssignmentType($cpAssignment, 'cp');
        $this->ensureAssignmentActive($cpAssignment);

        $campaign = $cpAssignment->campaign;

        $this->ensureNoActiveSupervisorAssignment($supervisor);

        return DB::transaction(function () use ($supervisor, $cpAssignment, $campaign, $reason) {
            $assignment = Assignment::create([
                'employee_id' => $supervisor->id,
                'campaign_id' => $campaign->id,
                'manager_id' => $cpAssignment->employee_id,
                'position_id' => $supervisor->position_id,
                'parent_assignment_id' => $cpAssignment->id,
                'assignment_type' => 'supervisor',
                'status' => 'active',
                'start_date' => now()->toDateString(),
            ]);

            $this->logHistory($assignment, 'assign', $reason);

            return $assignment->load(['employee', 'campaign', 'manager', 'position', 'parentAssignment']);
        });
    }

    public function assignTcToSupervisor(Employee $tc, Assignment $supervisorAssignment, ?string $reason = null): Assignment
    {
        $this->ensureIsTc($tc);
        $this->ensureIsAdmin();
        $this->ensureAssignmentType($supervisorAssignment, 'supervisor');
        $this->ensureAssignmentActive($supervisorAssignment);

        $campaign = $supervisorAssignment->campaign;

        return DB::transaction(function () use ($tc, $supervisorAssignment, $campaign, $reason) {
            $assignment = Assignment::create([
                'employee_id' => $tc->id,
                'campaign_id' => $campaign->id,
                'manager_id' => $supervisorAssignment->employee_id,
                'position_id' => $tc->position_id,
                'parent_assignment_id' => $supervisorAssignment->id,
                'assignment_type' => 'tc',
                'status' => 'active',
                'start_date' => now()->toDateString(),
            ]);

            $this->logHistory($assignment, 'assign', $reason);

            return $assignment->load(['employee', 'campaign', 'manager', 'position', 'parentAssignment']);
        });
    }

    public function unassign(Assignment $assignment, ?string $reason = null): void
    {
        $this->ensureIsAdmin();

        DB::transaction(function () use ($assignment, $reason) {
            $this->unassignRecursive($assignment, $reason);
        });
    }

    protected function unassignRecursive(Assignment $assignment, ?string $reason): void
    {
        foreach ($assignment->children as $child) {
            $this->unassignRecursive($child, $reason);
        }

        $this->logHistory($assignment, 'release', $reason);
        $assignment->update(['status' => 'terminated', 'end_date' => now()->toDateString()]);
    }

    protected function ensureIsCp(Employee $employee): void
    {
        $position = $employee->position;
        if (!$position || stripos($position->name, 'Chef de Plateau') === false && stripos($position->name, 'CP') === false) {
            throw new InvalidArgumentException("L'employé n'est pas un Chef de Plateau.");
        }
    }

    protected function ensureIsSupervisor(Employee $employee): void
    {
        $position = $employee->position;
        if (!$position || stripos($position->name, 'Superviseur') === false && stripos($position->name, 'SUP') === false) {
            throw new InvalidArgumentException("L'employé n'est pas un Superviseur.");
        }
    }

    protected function ensureIsTc(Employee $employee): void
    {
        $position = $employee->position;
        if (!$position || stripos($position->name, 'Téléconseiller') === false && stripos($position->name, 'TC') === false) {
            throw new InvalidArgumentException("L'employé n'est pas un Téléconseiller.");
        }
    }

    protected function ensureIsAdmin(): void
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            throw new InvalidArgumentException("Seul un administrateur peut gérer les affectations.");
        }
    }

    protected function ensureAssignmentType(Assignment $assignment, string $type): void
    {
        if ($assignment->assignment_type !== $type) {
            throw new InvalidArgumentException("L'affectation n'est pas du type attendu ({$type}).");
        }
    }

    protected function ensureAssignmentActive(Assignment $assignment): void
    {
        if ($assignment->status !== 'active') {
            throw new InvalidArgumentException("L'affectation doit être active.");
        }
    }

    protected function ensureNoActiveSupervisorAssignment(Employee $supervisor): void
    {
        $hasActive = Assignment::where('employee_id', $supervisor->id)
            ->where('assignment_type', 'supervisor')
            ->where('status', 'active')
            ->exists();

        if ($hasActive) {
            throw new InvalidArgumentException("Le superviseur a déjà une affectation active.");
        }
    }

    protected function logHistory(Assignment $assignment, string $actionType, ?string $reason): void
    {
        \App\Models\AssignmentHistory::create([
            'assignment_id' => $assignment->id,
            'employee_id' => $assignment->employee_id,
            'old_manager_id' => null,
            'new_manager_id' => $assignment->manager_id,
            'old_campaign_id' => null,
            'new_campaign_id' => $assignment->campaign_id,
            'action_type' => $actionType,
            'changed_by' => auth()->id(),
            'reason' => $reason,
        ]);
    }
}
