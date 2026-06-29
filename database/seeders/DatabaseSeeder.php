<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\EnseignantMatiereClasse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Création des Classes
        $sil = Classe::create(['nom' => 'Licence 2 SIL', 'annee_academique' => '2025-2026']);
        $rgl = Classe::create(['nom' => 'Licence 2 RGL', 'annee_academique' => '2025-2026']);

        // 2. Création des Matières
        $laravel = Matiere::create(['nom' => 'Développement Backend Laravel', 'code' => 'DEV401', 'coefficient_defaut' => 3]);
        $nextjs = Matiere::create(['nom' => 'Développement Frontend Next.js', 'code' => 'DEV402', 'coefficient_defaut' => 3]);
        $stats = Matiere::create(['nom' => 'Statistiques Inférentielles', 'code' => 'MATH401', 'coefficient_defaut' => 2]);

        // 3. CRÉATION DES COMPTES DE DÉMONSTRATION

        // --- 1 COMPTE ADMIN ---
        User::create([
            'nom' => 'AZIFAN Laurex',
            'email' => 'azifanmiguel647@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        // --- 2 COMPTES ENSEIGNANTS ---
        $prof1 = User::create([
            'nom' => 'Prof Laravel',
            'email' => 'enseignant1@demo.com',
            'password' => Hash::make('password'),
            'role' => 'enseignant'
        ]);

        $prof2 = User::create([
            'nom' => 'Prof NextJS & Stats',
            'email' => 'enseignant2@demo.com',
            'password' => Hash::make('password'),
            'role' => 'enseignant'
        ]);

        // --- 5 COMPTES ÉTUDIANTS ---
        $etudiants = [];
        $nomsEtudiants = ['Miguel Azifan', 'HOUNLEBA Harmonic', 'BIADJA Velmira', 'Sophie Gnahoui', 'Jean Hounnou'];
        
        foreach ($nomsEtudiants as $key => $nom) {
            $etudiants[] = User::create([
                'nom' => $nom,
                'email' => 'etudiant' . ($key + 1) . '@demo.com',
                'password' => Hash::make('password'),
                'role' => 'etudiant'
            ]);
        }

        // 4. AFFECTATIONS ET INSCRIPTIONS

        // Affectations des profs (Enseignant, Matière, Classe)
        EnseignantMatiereClasse::create([
            'enseignant_id' => $prof1->id,
            'matiere_id' => $laravel->id,
            'classe_id' => $sil->id,
            'annee' => '2025-2026'
        ]);

        EnseignantMatiereClasse::create([
            'enseignant_id' => $prof2->id,
            'matiere_id' => $nextjs->id,
            'classe_id' => $sil->id,
            'annee' => '2025-2026'
        ]);

        // Inscription des étudiants dans la classe "Licence 2 SIL"
        foreach ($etudiants as $etudiant) {
            $etudiant->classes()->attach($sil->id, ['annee' => '2025-2026']);
        }
    }
}