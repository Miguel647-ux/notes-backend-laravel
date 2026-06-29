<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnseignantMatiereClasse extends Model
{
    // Indique à Laravel le nom exact de ta table pivot si elle diffère du pluriel automatique
    protected $table = 'enseignant_matiere_classe'; 

    protected $fillable = [
        'enseignant_id',
        'classe_id',
        'matiere_id',
        'annee'
    ];

    // Relation avec la classe
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    // Relation avec la matière
    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class, 'matiere_id');
    }

    // Relation optionnelle vers l'utilisateur/enseignant si besoin
    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }
}