<?php

namespace App\Controller;

use App\Service\ListeCoursesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ListeCoursesController extends AbstractController
{
    public function __construct(
        private readonly ListeCoursesService $listeCoursesService,
    ) {}

    /**
     * Consultation de la liste de courses 
     */
    #[Route('/api/liste-courses', name: 'api_liste_courses_consultation', methods: ['GET'])]
    public function consulterListe(): JsonResponse
    {
        $utilisateur = $this->getUser();
        $liste = $this->listeCoursesService->consulterListe($utilisateur);

        return new JsonResponse($liste, 200);
    }

    /**
     * Ajout d'un article à la liste 
     */
    #[Route('/api/liste-courses', name: 'api_liste_courses_ajout', methods: ['POST'])]
    public function ajouterArticle(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        $champsRequis = ['nom', 'quantite'];
        foreach ($champsRequis as $champ) {
            if (!isset($donnees[$champ]) || $donnees[$champ] === '') {
                return new JsonResponse(['message' => "Le champ '$champ' est obligatoire."], 422);
            }
        }

        $utilisateur = $this->getUser();

        try {
            $this->listeCoursesService->ajouterArticle(
                utilisateur: $utilisateur,
                nom: $donnees['nom'],
                quantite: $donnees['quantite'],
                categorieAchat: $donnees['categorieAchat'] ?? null,
                codeBarres: $donnees['codeBarres'] ?? null,
                photo: $donnees['photo'] ?? null,
                categorieProduit: $donnees['categorieProduit'] ?? null,
            );
        } catch (\ValueError $e) {
            return new JsonResponse(['message' => 'Catégorie invalide.'], 422);
        }

        return new JsonResponse(['message' => 'Article ajouté à la liste.'], 201);
    }

    /**
     * Coche/décoche un article 
     */
    #[Route('/api/liste-courses/{id}/basculer', name: 'api_liste_courses_basculer', methods: ['POST'])]
    public function basculerCoche(int $id): JsonResponse
    {
        $utilisateur = $this->getUser();

        try {
            $coche = $this->listeCoursesService->basculerCoche($utilisateur, $id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        return new JsonResponse(['coche' => $coche], 200);
    }

    /**
     * Suppression d'un article
     */
    #[Route('/api/liste-courses/{id}', name: 'api_liste_courses_suppression', methods: ['DELETE'])]
    public function supprimerArticle(int $id): JsonResponse
    {
        $utilisateur = $this->getUser();

        try {
            $this->listeCoursesService->supprimerArticle($utilisateur, $id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        return new JsonResponse(['message' => 'Article supprimé.'], 200);
    }

    /**
     * Archivage des articles cochés 
     */
    #[Route('/api/liste-courses/archiver', name: 'api_liste_courses_archivage', methods: ['POST'])]
    public function archiverCoches(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);
        $ajouterAuStock = $donnees['ajouterAuStock'] ?? false;

        $utilisateur = $this->getUser();
        $this->listeCoursesService->archiverCoches($utilisateur, $ajouterAuStock);

        return new JsonResponse(['message' => 'Articles cochés archivés.'], 200);
    }
}