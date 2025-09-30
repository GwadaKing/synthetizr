<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

// NOTE: Cette classe sert de proxy pour les données du token JWT.
class User implements UserInterface
{
    private string $id; // Cet ID sera l'ID WordPress

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }

    // --- Méthodes minimales de UserInterface ---

    public function getRoles(): array
    {
        // Tous les utilisateurs authentifiés via JWT sont considérés comme ROLE_USER
        return ['ROLE_USER']; 
    }

    public function eraseCredentials(): void
    {
        // Non utilisé pour l'authentification sans mot de passe
    }

    // Le Lexik JWT Bundle exige souvent une méthode getUsername() pour la compatibilité
    public function getUsername(): string
    {
        return $this->id;
    }
}