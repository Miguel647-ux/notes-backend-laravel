<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validation des champs saisis
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Recherche de l'utilisateur par son email
        $user = User::where('email', $request->email)->first();

        // 3. Vérification du mot de passe
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Les identifiants sont incorrects.'
            ], 401);
        }

        // 4. Création du token Sanctum avec son rôle inclus
        $token = $user->createToken('auth_token', [$user->role])->plainTextToken;

        // 5. Réponse envoyée au Frontend Next.js
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'email' => $user->email,
                'role' => $user->role, // Très important pour les redirections sur Next.js !
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Supprime le token actuel de l'utilisateur déconnecté
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ]);
    }
}