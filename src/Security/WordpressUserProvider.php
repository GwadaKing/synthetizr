<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

// NOTE: Symfony utilise cette classe pour charger l'utilisateur à partir du token JWT.
class WordpressUserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // L'identifiant est l'ID WordPress passé par le JWT.
        // On crée simplement notre objet User avec cet ID.
        return new User($identifier);
    }

    // Méthodes pour la compatibilité Doctrine (non utilisées dans ce contexte JWT simple)
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        // Le JWT est la source de vérité, donc nous ne faisons rien ici.
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
    
    // Pour la compatibilité Lexik JWT dans les anciennes versions :
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }
}