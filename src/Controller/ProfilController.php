<?php

namespace App\Controller;

use App\Service\ProfilService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    public function __construct(
        private readonly ProfilService $profilService,
    ) {}

    /**
     * Consultation du profil 
     */
    #[Route('/api/profil', name: 'api_profil', methods: ['GET'])]
    public function consulterProfil(): JsonResponse
    {
        $utilisateur = $this->getUser();

        $profil = $this->profilService->consulterProfil($utilisateur);

        return new JsonResponse($profil, 200);
    }
}