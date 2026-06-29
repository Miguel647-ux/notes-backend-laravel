<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'code', 'coefficient_defaut'];

    public function affectations()
    {
        return $this->hasMany(EnseignantMatiereClasse::class, 'matiere_id');
    }
}