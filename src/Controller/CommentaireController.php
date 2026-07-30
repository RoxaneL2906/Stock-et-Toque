<?php

namespace App\Controller;

use App\Service\CommentaireService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class CommentaireController extends AbstractController
{
    public function __construct(
        private readonly CommentaireService $commentaireService,
    ) {}

    /**
     * Liste des commentaires d'une recette
     */
    #[Route('/api/recettes/{id}/commentaires', name: 'api_commentaires_liste', methods: ['GET'])]
    public function listerCommentaires(int $id): JsonResponse
    {
        try {
            $commentaires = $this->commentaireService->listerCommentaires($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        }

        return new JsonResponse($commentaires, 200);
    }

    /**
     * Ajout d'un commentaire sur une recette
     */
    #[Route('/api/recettes/{id}/commentaires', name: 'api_commentaires_ajout', methods: ['POST'])]
    public function ajouterCommentaire(int $id, Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        if (empty($donnees['contenu'])) {
            return new JsonResponse(['message' => "Le champ 'contenu' est obligatoire."], 422);
        }

        if (strlen($donnees['contenu']) > 500) {
            return new JsonResponse(['message' => 'Le commentaire ne doit pas dépasser 500 caractères.'], 422);
        }

        $utilisateur = $this->getUser();

        try {
            $commentaire = $this->commentaireService->ajouterCommentaire($utilisateur, $id, $donnees['contenu']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        }

        return new JsonResponse(['id' => $commentaire->getId(), 'message' => 'Commentaire ajouté.'], 201);
    }

    /**
     * Modification d'un commentaire
     */
    #[Route('/api/commentaires/{id}', name: 'api_commentaires_modification', methods: ['PUT'])]
    public function modifierCommentaire(int $id, Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        if (empty($donnees['contenu'])) {
            return new JsonResponse(['message' => "Le champ 'contenu' est obligatoire."], 422);
        }

        if (strlen($donnees['contenu']) > 500) {
            return new JsonResponse(['message' => 'Le commentaire ne doit pas dépasser 500 caractères.'], 422);
        }

        $utilisateur = $this->getUser();

        try {
            $this->commentaireService->modifierCommentaire($utilisateur, $id, $donnees['contenu']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        }

        return new JsonResponse(['message' => 'Commentaire modifié.'], 200);
    }

    /**
     * Suppression d'un commentaire
     */
    #[Route('/api/commentaires/{id}', name: 'api_commentaires_suppression', methods: ['DELETE'])]
    public function supprimerCommentaire(int $id): JsonResponse
    {
        $utilisateur = $this->getUser();

        try {
            $this->commentaireService->supprimerCommentaire($utilisateur, $id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        }

        return new JsonResponse(['message' => 'Commentaire supprimé.'], 200);
    }
}