<?php

namespace App\Service;

use App\Entity\Utilisateur;

class ProfilService
{
    /**
     * Retourne les informations du profil de l'utilisateur connecté.
     */
    public function consulterProfil(Utilisateur $utilisateur): array
    {
        return [
            'id' => $utilisateur->getId(),
            'prenom' => $utilisateur->getPrenom(),
            'nom' => $utilisateur->getNom(),
            'email' => $utilisateur->getEmail(),
            'photoProfil' => $utilisateur->getPhotoProfil(),
            'dateInscription' => $utilisateur->getDateInscription()?->format('Y-m-d'),
        ];
    }
}