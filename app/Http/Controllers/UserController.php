<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Lister tous les utilisateurs
    public function index()
    {
        return response()->json(User::all());
    }

    // Filtrer les utilisateurs par rôle (Ex: récupérer uniquement les enseignants)
    public function getUsersByRole($role)
    {
        if (!in_array($role, ['admin', 'enseignant', 'etudiant'])) {
            return response()->json(['message' => 'Rôle invalide'], 400);
        }
        
        $users = User::where('role', $role)->get();
        return response()->json($users);
    }

    // Créer un nouvel utilisateur (Enseignant ou Étudiant)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,enseignant,etudiant',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $user], 201);
    }
}