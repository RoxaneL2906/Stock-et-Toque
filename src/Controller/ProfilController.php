<?php

namespace App\Controller;

use App\Service\ProfilService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
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

    /**
     * Modification du prénom et du nom 
     */
    #[Route('/api/profil/informations', name: 'api_profil_informations', methods: ['POST'])]
    public function modifierInformations(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        $champsRequis = ['prenom', 'nom'];
        foreach ($champsRequis as $champ) {
            if (empty($donnees[$champ])) {
                return new JsonResponse(['message' => "Le champ '$champ' est obligatoire."], 422);
            }
        }

        $utilisateur = $this->getUser();

        try {
            $this->profilService->modifierInformations($utilisateur, $donnees['prenom'], $donnees['nom']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        return new JsonResponse(['message' => 'Vos informations ont bien été modifiées.'], 200);
    }

    /**
     * Modification de l'email
     */
    #[Route('/api/profil/email', name: 'api_profil_email', methods: ['POST'])]
    public function modifierEmail(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        $champsRequis = ['nouvelEmail', 'motDePasseActuel'];
        foreach ($champsRequis as $champ) {
            if (empty($donnees[$champ])) {
                return new JsonResponse(['message' => "Le champ '$champ' est obligatoire."], 422);
            }
        }

        $utilisateur = $this->getUser();

        try {
            $this->profilService->modifierEmail($utilisateur, $donnees['nouvelEmail'], $donnees['motDePasseActuel']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        $response = new JsonResponse(['message' => 'Votre adresse email a bien été modifiée. Veuillez vous reconnecter.'], 200);
        $response->headers->clearCookie('access_token', '/', null, true, true, 'lax');
        $response->headers->clearCookie('refresh_token', '/', null, true, true, 'lax');

        return $response;
    }

    /**
     * Modification du mot de passe 
     */
    #[Route('/api/profil/mot-de-passe', name: 'api_profil_mot_de_passe', methods: ['POST'])]
    public function modifierMotDePasse(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        $champsRequis = ['motDePasseActuel', 'nouveauMotDePasse', 'confirmationNouveauMotDePasse'];
        foreach ($champsRequis as $champ) {
            if (empty($donnees[$champ])) {
                return new JsonResponse(['message' => "Le champ '$champ' est obligatoire."], 422);
            }
        }

        $utilisateur = $this->getUser();

        try {
            $this->profilService->modifierMotDePasse(
                $utilisateur,
                $donnees['motDePasseActuel'],
                $donnees['nouveauMotDePasse'],
                $donnees['confirmationNouveauMotDePasse'],
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        $response = new JsonResponse(['message' => 'Votre mot de passe a bien été modifié. Un email de confirmation vous a été envoyé. Veuillez vous reconnecter.'], 200);
        $response->headers->clearCookie('access_token', '/', null, true, true, 'lax');
        $response->headers->clearCookie('refresh_token', '/', null, true, true, 'lax');

        return $response;
    }

    /**
     * Suppression du compte
     */
    #[Route('/api/profil/suppression', name: 'api_profil_suppression', methods: ['POST'])]
    public function supprimerCompte(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        if (empty($donnees['motDePasse'])) {
            return new JsonResponse(['message' => "Le champ 'motDePasse' est obligatoire."], 422);
        }

        $utilisateur = $this->getUser();

        try {
            $this->profilService->supprimerCompte($utilisateur, $donnees['motDePasse']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        $response = new JsonResponse(['message' => 'Votre compte a bien été supprimé.'], 200);
        $response->headers->clearCookie('access_token', '/', null, true, true, 'lax');
        $response->headers->clearCookie('refresh_token', '/', null, true, true, 'lax');

        return $response;
    }
}
