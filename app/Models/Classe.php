<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'annee_academique'];

    // Récupère les étudiants inscrits dans cette classe
    public function etudiants()
    {
        return $this->belongsToMany(User::class, 'etudiant_classe', 'classe_id', 'etudiant_id')
                    ->withPivot('annee')
                    ->withTimestamps();
    }

    // Récupère les matières et enseignants affectés à cette classe
    public function enseignements()
    {
        return $this->hasMany(EnseignantMatiereClasse::class, 'classe_id');
    }
}