<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    /**
     Création de compte
     */
    #[Route('/api/inscription', name: 'api_inscription', methods: ['POST'])]
    public function inscription(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        // Vérifie que le JSON envoyé contient bien tous les champs attendus
        $champsRequis = ['prenom', 'nom', 'email', 'motDePasse', 'confirmationMotDePasse'];
        foreach ($champsRequis as $champ) {
            if (empty($donnees[$champ])) {
                return new JsonResponse(
                    ['message' => "Le champ '$champ' est obligatoire."],
                    422
                );
            }
        }

        try {
            $resultat = $this->authService->inscrire(
                prenom: $donnees['prenom'],
                nom: $donnees['nom'],
                email: $donnees['email'],
                motDePasse: $donnees['motDePasse'],
                confirmationMotDePasse: $donnees['confirmationMotDePasse'],
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        $utilisateur = $resultat['utilisateur'];

        return new JsonResponse([
            'accessToken' => $resultat['accessToken'],
            'utilisateur' => [
                'id' => $utilisateur->getId(),
                'prenom' => $utilisateur->getPrenom(),
                'nom' => $utilisateur->getNom(),
                'email' => $utilisateur->getEmail(),
            ],
        ], 201);
    }
}