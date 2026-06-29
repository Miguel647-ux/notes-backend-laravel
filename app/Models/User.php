<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['nom', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    // Si l'utilisateur est un étudiant : récupère ses classes (via la table pivot)
    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'etudiant_classe', 'etudiant_id', 'classe_id')
                    ->withPivot('annee')
                    ->withTimestamps();
    }

    // Si l'utilisateur est un enseignant : récupère ses affectations de cours
    public function affectations()
    {
        return $this->hasMany(EnseignantMatiereClasse::class, 'enseignant_id');
    }

    // Récupère les notes (soit reçues si étudiant, soit attribuées si enseignant)
    public function notes()
    {
        return $this->hasMany(Note::class, $this->role === 'etudiant' ? 'etudiant_id' : 'enseignant_id');
    }
}
