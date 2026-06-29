<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\EnseignantMatiereClasse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffectationController extends Controller
{
    // 1. Liste toutes les affectations des enseignants pour le tableau Next.js
    public function index()
    {
        $affectations = DB::table('enseignant_matiere_classe') 
            ->join('users', 'enseignant_matiere_classe.enseignant_id', '=', 'users.id')
            ->join('matieres', 'enseignant_matiere_classe.matiere_id', '=', 'matieres.id')
            ->join('classes', 'enseignant_matiere_classe.classe_id', '=', 'classes.id')
            ->select(
                'enseignant_matiere_classe.id',
                'users.nom as enseignant_nom',
                'matieres.nom as matiere_nom',
                'matieres.code as matiere_code',
                'classes.nom as classe_nom',
                'classes.annee_academique as annee' // Récupération de l'année de la classe
            )
            ->get();

        return response()->json($affectations, 200);
    }

    // 2. Récupère les données pour remplir les sélecteurs (Select) du formulaire Next.js
    public function getFormData()
    {
        return response()->json([
            'enseignants' => User::where('role', 'enseignant')->select('id', 'nom')->get(),
            'matieres' => Matiere::select('id', 'nom', 'code')->get(),
            'classes' => Classe::select('id', 'nom', 'annee_academique')->get()
        ], 200);
    }

    // 3. Affecter un enseignant à une matière et une classe (Strictement 3 attributs)
    public function affecterEnseignant(Request $request)
{
    $request->validate([
        'enseignant_id' => 'required',
        'matiere_id'    => 'required',
        'classe_id'     => 'required',
    ]);

    // 1. Récupérer la classe pour extraire son année académique
    $classe = DB::table('classes')->where('id', $request->classe_id)->first();
    
    if (!$classe) {
        return response()->json(['message' => "Classe introuvable"], 404);
    }

    // 2. Insertion SQL brute directe pour forcer le passage de l'année
    DB::table('enseignant_matiere_classe')->insert([
        'enseignant_id' => $request->enseignant_id,
        'matiere_id'    => $request->matiere_id,
        'classe_id'     => $request->classe_id,
        'annee'         => $classe->annee_academique, // Récupéré de la table classes
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    return response()->json(['message' => 'Enseignant affecté avec succès'], 201);
}
    // 4. Affecter un étudiant à une classe (Inscription)
    public function inscrireEtudiant(Request $request)
    {
        $request->validate([
            'etudiant_id' => 'required|exists:users,id',
            'classe_id' => 'required|exists:classes,id',
            'annee' => 'required|string',
        ]);

        $etudiant = User::find($request->etudiant_id);
        if ($etudiant->role !== 'etudiant') {
            return response()->json(['message' => "Cet utilisateur n'est pas un étudiant"], 400);
        }

        $etudiant->classes()->attach($request->classe_id, ['annee' => $request->annee]);

        return response()->json(['message' => 'Étudiant inscrit dans la classe avec succès']);
    }

    // 5. Annuler/Supprimer une affectation d'enseignant
    public function destroy($id)
    {
    
        DB::table('enseignant_matiere_classe')->where('id', $id)->delete();
        return response()->json(['message' => 'Affectation annulée avec succès'], 200);
    }
}