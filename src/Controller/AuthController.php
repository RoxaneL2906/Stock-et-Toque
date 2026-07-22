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
    ) {}

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

    /**
     Connexion
     */
    #[Route('/api/connexion', name: 'api_connexion', methods: ['POST'])]
    public function connexion(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        // Vérifie que le JSON envoyé contient bien tous les champs attendus
        $champsRequis = ['email', 'motDePasse'];
        foreach ($champsRequis as $champ) {
            if (empty($donnees[$champ])) {
                return new JsonResponse(
                    ['message' => "Le champ '$champ' est obligatoire."],
                    422
                );
            }
        }

        // "Se souvenir de moi" est optionnel, false par défaut si absent
        $seSouvenirDeMoi = $donnees['seSouvenirDeMoi'] ?? false;

        try {
            $resultat = $this->authService->connecter(
                email: $donnees['email'],
                motDePasse: $donnees['motDePasse'],
                seSouvenirDeMoi: $seSouvenirDeMoi,
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 401);
        }

        $utilisateur = $resultat['utilisateur'];

        return new JsonResponse([
            'accessToken' => $resultat['accessToken'],
            'refreshToken' => $resultat['refreshToken'],
            'utilisateur' => [
                'id' => $utilisateur->getId(),
                'prenom' => $utilisateur->getPrenom(),
                'nom' => $utilisateur->getNom(),
                'email' => $utilisateur->getEmail(),
            ],
        ], 200);
    }

    /**
     Déconnexion
     */
    #[Route('/api/deconnexion', name: 'api_deconnexion', methods: ['POST'])]
    public function deconnexion(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        // Le refreshToken est optionnel : un utilisateur sans "se souvenir de moi" n'en a pas
        $refreshToken = $donnees['refreshToken'] ?? null;

        $this->authService->deconnecter($refreshToken);

        return new JsonResponse(['message' => 'Déconnexion réussie.'], 200);
    }

    /**
     Demande de réinitialisation du mot de passe
     */
    #[Route('/api/mot-de-passe-oublie', name: 'api_mot_de_passe_oublie', methods: ['POST'])]
    public function motDePasseOublie(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        if (empty($donnees['email'])) {
            return new JsonResponse(['message' => "Le champ 'email' est obligatoire."], 422);
        }

        $this->authService->demanderReinitialisation($donnees['email']);

        // Message générique : ne révèle jamais si l'email existe ou non en base
        return new JsonResponse(
            ['message' => 'Si cet email est associé à un compte, un lien de réinitialisation a été envoyé.'],
            200
        );
    }

    /**
     Réinitialisation du mot de passe
     */
    #[Route('/api/reinitialiser-mot-de-passe', name: 'api_reinitialiser_mot_de_passe', methods: ['POST'])]
    public function reinitialiserMotDePasse(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        $champsRequis = ['token', 'nouveauMotDePasse', 'confirmationNouveauMotDePasse'];
        foreach ($champsRequis as $champ) {
            if (empty($donnees[$champ])) {
                return new JsonResponse(
                    ['message' => "Le champ '$champ' est obligatoire."],
                    422
                );
            }
        }

        try {
            $this->authService->reinitialiserMotDePasse(
                token: $donnees['token'],
                nouveauMotDePasse: $donnees['nouveauMotDePasse'],
                confirmationNouveauMotDePasse: $donnees['confirmationNouveauMotDePasse'],
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        return new JsonResponse(
            ['message' => 'Votre mot de passe a bien été réinitialisé.'],
            200
        );
    }
}
