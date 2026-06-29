<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use Illuminate\Http\Request;

class ClasseController extends Controller
{
    // Lister toutes les classes
    public function index()
    {
        return response()->json(Classe::all());
    }

    // Créer une nouvelle classe
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'annee_academique' => 'required|string|max:255',
        ]);

        $classe = Classe::create($validated);

        return response()->json(['message' => 'Classe créée avec succès', 'classe' => $classe], 201);
    }

    // Afficher une classe spécifique
    public function show(Classe $classe)
    {
        return response()->json($classe);
    }

    // Modifier une classe
    public function update(Request $request, Classe $classe)
    {
        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'annee_academique' => 'sometimes|required|string|max:255',
        ]);

        $classe->update($validated);

        return response()->json(['message' => 'Classe mise à jour avec succès', 'classe' => $classe]);
    }

    // Supprimer une classe
    public function destroy(Classe $classe)
    {
        $classe->delete();
        return response()->json(['message' => 'Classe supprimée avec succès']);
    }
}