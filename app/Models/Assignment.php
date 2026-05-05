<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'employee_id',
        'campaign_id',
        'manager_id',
        'position_id',
        'parent_assignment_id',
        'assignment_type',
        'status',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function parentAssignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'parent_assignment_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Assignment::class, 'parent_assignment_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AssignmentHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeType($query, string $type)
    {
        return $query->where('assignment_type', $type);
    }

    public function isCp(): bool
    {
        return $this->assignment_type === 'cp';
    }

    public function isSupervisor(): bool
    {
        return $this->assignment_type === 'supervisor';
    }

    public function isTc(): bool
    {
        return $this->assignment_type === 'tc';
    }
}
