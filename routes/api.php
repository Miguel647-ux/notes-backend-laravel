<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\MatiereController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// 1. Route publique pour se connecter
Route::post('/login', [AuthController::class, 'login']);

// 2. Routes TOUTES protégées (l'utilisateur doit être connecté avec son Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('affectations', [AffectationController::class, 'index']);
    Route::get('affectations/form-data', [AffectationController::class, 'getFormData']);
    Route::post('affectations/enseignant', [AffectationController::class, 'affecterEnseignant']);
    Route::delete('affectations/{id}', [AffectationController::class, 'destroy']);

Route::get('matieres-count', [MatiereController::class, 'getCount']);
    
    Route::apiResource('matieres', MatiereController::class);
Route::post('/logout', [AuthController::class, 'logout']);
    
    // Vérifier si le token est valide et récupérer le profil
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

    // CRUD Classes et Matières
    Route::apiResource('classes', ClasseController::class);
    Route::apiResource('matieres', MatiereController::class);

    // Gestion des utilisateurs (Admin)
    Route::get('users/role/{role}', [UserController::class, 'getUsersByRole']);
    Route::apiResource('users', UserController::class)->only(['index', 'store']);

    // Affectations de l'administrateur
    Route::post('affectations/enseignant', [AffectationController::class, 'affecterEnseignant']);
    Route::post('affectations/etudiant', [AffectationController::class, 'inscrireEtudiant']);

    // --- ESPACE ENSEIGNANT & NOTES ---
    Route::post('notes', [NoteController::class, 'store']); // Saisie d'une note
    Route::get('notes/classe/{classeId}/matiere/{matiereId}', [NoteController::class, 'getNotesParClasseEtMatiere']); // Consulter les notes de sa classe
    
    // Récupérer les classes et matières attribuées à l'enseignant connecté
    Route::get('enseignant/affectations', function () {
        $userId = Auth::id();
        
        $affectations = App\Models\EnseignantMatiereClasse::with(['classe', 'matiere'])
            ->where('enseignant_id', $userId)
            ->get();

        return response()->json($affectations);
    }); // ✅ Fermeture correcte de la route ici !

    // ✅ Ces routes sont désormais bien déclarées au même niveau !
    Route::get('enseignant/affectations/{id}/notes', [NoteController::class, 'getNotesParAffectationId']);
    Route::get('enseignant/affectations/{id}/etudiants', [NoteController::class, 'getNotesParAffectationId']);


    // --- ESPACE ÉTUDIANT & CALCULS ---
    Route::get('etudiants/{etudiantId}/moyennes', [NoteController::class, 'getMoyennesEtudiant']); // Moyennes par matière et générale
    Route::get('etudiants/{etudiantId}/bulletin/pdf', [NoteController::class, 'telechargerBulletin']); // Bulletin PDF
    
});