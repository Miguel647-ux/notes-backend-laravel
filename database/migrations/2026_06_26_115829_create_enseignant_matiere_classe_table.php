<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enseignant_matiere_classe', function (Blueprint $table) {
            $table->id();
            // ID de l'enseignant (qui pointe vers la table users)
            $table->foreignId('enseignant_id')->constrained('users')->onDelete('cascade');
            // ID de la matière
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('cascade');
            // ID de la classe
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->string('annee'); // Exemple: 2025-2026
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignant_matiere_classe');
    }
};
