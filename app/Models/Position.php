<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    /**
     * Récupérer tous les employés associés à ce poste
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Récupérer toutes les affectations associées à ce poste
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
