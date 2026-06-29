<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use App\Models\EnseignantMatiereClasse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    // 1. Saisie de notes (Enseignant) - Enregistrement individuel ou en masse
    public function store(Request $request)
    {
        $request->validate([
            'etudiant_id' => 'required|exists:users,id',
            'matiere_id' => 'required|exists:matieres,id',
            'valeur' => 'required|numeric|min:0|max:20', // Note entre 0 et 20
            'coefficient' => 'required|integer|min:1',
            'type' => 'required|in:devoir,examen',
            'date_evaluation' => 'required|date',
        ]);

        // L'enseignant connecté est celui qui saisit la note
        $enseignantId = $request->user()->id;

        // Vérifier si l'enseignant est bien affecté à cette matière
        $verification = EnseignantMatiereClasse::where('enseignant_id', $enseignantId)
            ->where('matiere_id', $request->matiere_id)
            ->exists();

        if (!$verification) {
            return response()->json(['message' => "Vous n'êtes pas autorisé à saisir des notes pour cette matière."], 403);
        }

        // Utilisation de updateOrCreate pour éviter les doublons accidentels sur le même type d'évaluation
        $note = Note::updateOrCreate(
            [
                'etudiant_id' => $request->etudiant_id,
                'matiere_id' => $request->matiere_id,
                'type' => $request->type,
            ],
            [
                'enseignant_id' => $enseignantId,
                'valeur' => $request->valeur,
                'coefficient' => $request->coefficient,
                'date_evaluation' => $request->date_evaluation,
            ]
        );

        return response()->json(['message' => 'Note enregistrée avec succès', 'note' => $note], 201);
    }

    // 2. Récupérer les notes d'une classe pour une matière spécifique (Vue Enseignant)
    public function getNotesParAffectationId($id)
    {
        $affectation = EnseignantMatiereClasse::with(['matiere', 'classe'])->find($id);

        if (!$affectation) {
            return response()->json(['message' => 'Affectation introuvable'], 404);
        }

        $etudiantIds = DB::table('etudiant_classe')
            ->where('classe_id', $affectation->classe_id)
            ->pluck('etudiant_id');

        $notes = Note::where('matiere_id', $affectation->matiere_id)
            ->whereIn('etudiant_id', $etudiantIds)
            ->get();

        $bulletins = User::whereIn('id', $etudiantIds)
            ->get(['id', 'nom'])
            ->map(function($etudiant) use ($notes) {
                $noteEtudiant = $notes->where('etudiant_id', $etudiant->id)->first();
                return [
                    'id' => $etudiant->id,
                    'nom' => $etudiant->nom,
                    'note' => $noteEtudiant ? (float)$noteEtudiant->valeur : null
                ];
            });

        return response()->json([
            'matiere' => $affectation->matiere->nom,
            'classe' => $affectation->classe->nom,
            'bulletins' => $bulletins,    
            'etudiants' => $bulletins     
        ]);
    }

    // 3. Calculer et afficher les moyennes d'un étudiant (Vue Étudiant / Bulletin)
    public function getMoyennesEtudiant($etudiantId)
    {
        // Récupération uniforme des données de classe
        $etudiantClasse = DB::table('etudiant_classe')
            ->join('classes', 'etudiant_classe.classe_id', '=', 'classes.id')
            ->where('etudiant_id', $etudiantId)
            ->select('classes.nom as classe_nom', 'etudiant_classe.annee')
            ->first();

        $notesParMatiere = Note::where('etudiant_id', $etudiantId)
            ->with('matiere')
            ->get()
            ->groupBy('matiere_id');

        $listeMoyennes = [];
        $sommeMoyennesMatieres = 0;
        $nombreMatieres = 0;

        foreach ($notesParMatiere as $matiereId => $notes) {
            $sommeNotesCoefficients = 0;
            $sommeCoefficients = 0;
            $nomMatiere = $notes->first()->matiere->nom;
            $codeMatiere = $notes->first()->matiere->code;

            foreach ($notes as $note) {
                $sommeNotesCoefficients += ($note->valeur * $note->coefficient);
                $sommeCoefficients += $note->coefficient;
            }

            // Moyenne de la matière
            $moyenneMatiere = $sommeCoefficients > 0 ? round($sommeNotesCoefficients / $sommeCoefficients, 2) : 0;

            $listeMoyennes[] = [
                'matiere_id' => $matiereId,
                'nom' => $nomMatiere,
                'code' => $codeMatiere,
                'moyenne' => $moyenneMatiere,
                'coefficient_total' => $sommeCoefficients
            ];

            $sommeMoyennesMatieres += $moyenneMatiere;
            $nombreMatieres++;
        }

        // ✅ Vraie moyenne générale d'établissement : Moyenne des matières (Donnera bien 18.00 pour 20 et 16)
        $moyenneGenerale = $nombreMatieres > 0 ? round($sommeMoyennesMatieres / $nombreMatieres, 2) : 0;

        return response()->json([
            'classe_nom' => $etudiantClasse ? $etudiantClasse->classe_nom : 'Classe non définie',
            'annee_academique' => $etudiantClasse ? $etudiantClasse->annee : '2025-2026',
            'moyenne_generale' => $moyenneGenerale, 
            'moyennes_par_matiere' => $listeMoyennes 
        ]);
    }

    // 4. Générer et télécharger le bulletin au format PDF
    public function telechargerBulletin($etudiantId)
    {
        $etudiant = User::findOrFail($etudiantId);
        
        // Récupération sécurisée via le Query Builder (comme dans getMoyennesEtudiant) pour s'affranchir des soucis de relations
        $etudiantClasse = DB::table('etudiant_classe')
            ->join('classes', 'etudiant_classe.classe_id', '=', 'classes.id')
            ->where('etudiant_id', $etudiantId)
            ->select('classes.nom as classe_nom', 'etudiant_classe.annee')
            ->first();

        $notesParMatiere = Note::where('etudiant_id', $etudiantId)
            ->with('matiere')
            ->get()
            ->groupBy('matiere_id');

        $listeMoyennes = [];
        $sommeMoyennesMatieres = 0;
        $nombreMatieres = 0;

        foreach ($notesParMatiere as $matiereId => $notes) {
            $sommeNotesCoefficients = 0;
            $sommeCoefficients = 0;
            $nomMatiere = $notes->first()->matiere->nom;
            $codeMatiere = $notes->first()->matiere->code;

            foreach ($notes as $note) {
                $sommeNotesCoefficients += ($note->valeur * $note->coefficient);
                $sommeCoefficients += $note->coefficient;
            }

            $moyenneMatiere = $sommeCoefficients > 0 ? round($sommeNotesCoefficients / $sommeCoefficients, 2) : 0;

            $listeMoyennes[] = [
                'nom' => $nomMatiere,
                'code' => $codeMatiere,
                'moyenne' => $moyenneMatiere,
                'coefficient_total' => $sommeCoefficients,
                'notes' => $notes 
            ];

            $sommeMoyennesMatieres += $moyenneMatiere;
            $nombreMatieres++;
        }

        $moyenneGenerale = $nombreMatieres > 0 ? round($sommeMoyennesMatieres / $nombreMatieres, 2) : 0;

        $data = [
            'etudiant' => $etudiant,
            'classe_nom' => $etudiantClasse ? $etudiantClasse->classe_nom : 'Classe non définie',
            'annee_academique' => $etudiantClasse ? $etudiantClasse->annee : '2025-2026',
            'moyennes_par_matiere' => $listeMoyennes,
            'moyenne_generale' => $moyenneGenerale,
        ];

        // Charger la vue et générer le téléchargement
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.bulletin', $data);
        
        return $pdf->download('bulletin_' . str_replace(' ', '_', $etudiant->nom) . '.pdf');
    }

    public function getEtudiantsPourSaisie($classeId)
    {
        $etudiantIds = DB::table('etudiant_classe')
            ->where('classe_id', $classeId)
            ->pluck('etudiant_id');

        $etudiants = User::whereIn('id', $etudiantIds)->get(['id', 'nom', 'email']);

        return response()->json($etudiants);
    }
}