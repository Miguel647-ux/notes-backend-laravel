<?php

namespace App\Http\Controllers;

use App\Models\Matiere;
use Illuminate\Http\Request;

class MatiereController extends Controller
{
    // Lister toutes les matières
    public function index()
    {
        return response()->json(Matiere::all());
    }

    // Créer une matière
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|unique:matieres,code|max:50',
            'coefficient_defaut' => 'required|integer|min:1',
        ]);

        $matiere = Matiere::create($validated);

        return response()->json(['message' => 'Matière créée avec succès', 'matiere' => $matiere], 201);
    }

    // Afficher une matière spécifique
    public function show(Matiere $matiere)
    {
        return response()->json($matiere);
    }

    // Modifier une matière
    public function update(Request $request, Matiere $matiere)
    {
        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:matieres,code,' . $matiere->id,
            'coefficient_defaut' => 'sometimes|required|integer|min:1',
        ]);

        $matiere->update($validated);

        return response()->json(['message' => 'Matière mise à jour avec succès', 'matiere' => $matiere]);
    }
    // Dans app/Http/Controllers/MatiereController.php

public function getCount()
{
    // Compte dynamiquement toutes les lignes de la table matieres
    return response()->json(['total' => \App\Models\Matiere::count()], 200);
}

    // Supprimer une matière
    public function destroy($id)
    {
        $matiere = Matiere::findOrFail($id);
        $matiere->delete();
        return response()->json(['message' => 'Matière supprimée avec succès'], 200);
    }
}