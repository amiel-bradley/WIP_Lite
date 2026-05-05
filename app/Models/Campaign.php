<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status'
    ];

    /**
     * Affectations liées à la campagne
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}