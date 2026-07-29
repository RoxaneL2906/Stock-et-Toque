<?php

namespace App\Controller;

use App\Service\RecetteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RecetteController extends AbstractController
{
    public function __construct(
        private readonly RecetteService $recetteService,
    ) {}

    /**
     * Création d'une recette 
     */
    #[Route('/api/recettes', name: 'api_recettes_creation', methods: ['POST'])]
    public function creerRecette(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        $champsRequis = ['titre', 'nbPersonnes', 'visibilite'];
        foreach ($champsRequis as $champ) {
            if (!isset($donnees[$champ]) || $donnees[$champ] === '') {
                return new JsonResponse(['message' => "Le champ '$champ' est obligatoire."], 422);
            }
        }

        $utilisateur = $this->getUser();

        try {
            $recette = $this->recetteService->creerRecette(
                utilisateur: $utilisateur,
                titre: $donnees['titre'],
                description: $donnees['description'] ?? null,
                tempsPreparation: $donnees['tempsPreparation'] ?? null,
                tempsCuisson: $donnees['tempsCuisson'] ?? null,
                nbPersonnes: $donnees['nbPersonnes'],
                difficulte: $donnees['difficulte'] ?? null,
                budgetEstime: $donnees['budgetEstime'] ?? null,
                visibilite: $donnees['visibilite'],
                brouillon: $donnees['brouillon'] ?? false,
                ingredients: $donnees['ingredients'] ?? [],
                etapes: $donnees['etapes'] ?? [],
                equipementIds: $donnees['equipementIds'] ?? [],
            );
        } catch (\ValueError $e) {
            return new JsonResponse(['message' => 'Difficulté ou visibilité invalide.'], 422);
        }

        return new JsonResponse(['id' => $recette->getId(), 'message' => 'Recette créée avec succès.'], 201);
    }

    /**
     * Liste de mes recettes 
     */
    #[Route('/api/recettes/mes-recettes', name: 'api_recettes_liste', methods: ['GET'])]
    public function listerMesRecettes(Request $request): JsonResponse
    {
        $utilisateur = $this->getUser();
        $visibilite = $request->query->get('visibilite');
        $brouillonParam = $request->query->get('brouillon');
        $brouillon = $brouillonParam !== null ? filter_var($brouillonParam, FILTER_VALIDATE_BOOLEAN) : null;

        $recettes = $this->recetteService->listerMesRecettes($utilisateur, $visibilite, $brouillon);

        return new JsonResponse($recettes, 200);
    }

    /**
     * Détail d'une de mes recettes
     */
    #[Route('/api/recettes/{id}', name: 'api_recettes_detail', methods: ['GET'])]
    public function consulterMaRecette(int $id): JsonResponse
    {
        $utilisateur = $this->getUser();

        try {
            $recette = $this->recetteService->consulterMaRecette($utilisateur, $id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        }

        return new JsonResponse($recette, 200);
    }

    /**
     * Modification d'une recette
     */
    #[Route('/api/recettes/{id}', name: 'api_recettes_modification', methods: ['PUT'])]
    public function modifierRecette(int $id, Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        $champsRequis = ['titre', 'nbPersonnes', 'visibilite'];
        foreach ($champsRequis as $champ) {
            if (!isset($donnees[$champ]) || $donnees[$champ] === '') {
                return new JsonResponse(['message' => "Le champ '$champ' est obligatoire."], 422);
            }
        }

        $utilisateur = $this->getUser();

        try {
            $this->recetteService->modifierRecette(
                utilisateur: $utilisateur,
                recetteId: $id,
                titre: $donnees['titre'],
                description: $donnees['description'] ?? null,
                tempsPreparation: $donnees['tempsPreparation'] ?? null,
                tempsCuisson: $donnees['tempsCuisson'] ?? null,
                nbPersonnes: $donnees['nbPersonnes'],
                difficulte: $donnees['difficulte'] ?? null,
                budgetEstime: $donnees['budgetEstime'] ?? null,
                visibilite: $donnees['visibilite'],
                brouillon: $donnees['brouillon'] ?? false,
                ingredients: $donnees['ingredients'] ?? [],
                etapes: $donnees['etapes'] ?? [],
                equipementIds: $donnees['equipementIds'] ?? [],
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        } catch (\ValueError $e) {
            return new JsonResponse(['message' => 'Difficulté ou visibilité invalide.'], 422);
        }

        return new JsonResponse(['message' => 'Recette modifiée avec succès.'], 200);
    }

    /**
     * Suppression d'une recette, avec anonymisation si publique
     */
    #[Route('/api/recettes/{id}', name: 'api_recettes_suppression', methods: ['DELETE'])]
    public function supprimerRecette(int $id): JsonResponse
    {
        $utilisateur = $this->getUser();

        try {
            $this->recetteService->supprimerRecette($utilisateur, $id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        }

        return new JsonResponse(['message' => 'Recette supprimée.'], 200);
    }

    /**
     * Upload/modification de la photo d'une recette (upload Vich)
     */
    #[Route('/api/recettes/{id}/photo', name: 'api_recettes_photo', methods: ['POST'])]
    public function modifierPhoto(int $id, Request $request): JsonResponse
    {
        $fichier = $request->files->get('photo');

        if ($fichier === null) {
            return new JsonResponse(['message' => "Aucun fichier 'photo' reçu."], 422);
        }

        $utilisateur = $this->getUser();

        try {
            $this->recetteService->modifierPhoto($utilisateur, $id, $fichier);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        }

        return new JsonResponse(['message' => 'Photo mise à jour avec succès.'], 200);
    }

    /**
     * Liste des équipements disponibles (référentiel fixe, pour le formulaire de création de recette)
     */
    #[Route('/api/equipements', name: 'api_equipements_liste', methods: ['GET'])]
    public function listerEquipements(\App\Repository\EquipementRepository $equipementRepository): JsonResponse
    {
        $equipements = $equipementRepository->findAll();

        $resultat = array_map(fn ($e) => ['id' => $e->getId(), 'nom' => $e->getNom()], $equipements);

        return new JsonResponse($resultat, 200);
    }
}