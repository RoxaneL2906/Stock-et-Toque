<?php

namespace App\Controller;

use App\Service\PlanningService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class PlanningController extends AbstractController
{
    public function __construct(
        private readonly PlanningService $planningService,
    ) {}

    /**
     * Consultation du planning d'une semaine (identifiée par le lundi, format YYYY-MM-DD)
     */
    #[Route('/api/planning', name: 'api_planning_consultation', methods: ['GET'])]
    public function consulterSemaine(Request $request): JsonResponse
    {
        $semaineDebutParam = $request->query->get('semaineDebut');

        if (empty($semaineDebutParam)) {
            return new JsonResponse(['message' => "Le paramètre 'semaineDebut' est obligatoire."], 422);
        }

        try {
            $semaineDebut = new \DateTime($semaineDebutParam);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Format de date invalide.'], 422);
        }

        $utilisateur = $this->getUser();
        $planning = $this->planningService->consulterSemaine($utilisateur, $semaineDebut);

        return new JsonResponse($planning, 200);
    }

    /**
     * Ajout ou remplacement d'un créneau
     */
    #[Route('/api/planning/creneau', name: 'api_planning_creneau_ajout', methods: ['POST'])]
    public function definirCreneau(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        $champsRequis = ['semaineDebut', 'jour', 'moment'];
        foreach ($champsRequis as $champ) {
            if (empty($donnees[$champ])) {
                return new JsonResponse(['message' => "Le champ '$champ' est obligatoire."], 422);
            }
        }

        try {
            $semaineDebut = new \DateTime($donnees['semaineDebut']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Format de date invalide.'], 422);
        }

        $utilisateur = $this->getUser();

        try {
            $this->planningService->definirCreneau(
                utilisateur: $utilisateur,
                semaineDebut: $semaineDebut,
                jour: $donnees['jour'],
                moment: $donnees['moment'],
                recetteId: $donnees['recetteId'] ?? null,
                platLibre: $donnees['platLibre'] ?? null,
                urlSource: $donnees['urlSource'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        } catch (\ValueError $e) {
            return new JsonResponse(['message' => 'Jour ou moment invalide.'], 422);
        }

        return new JsonResponse(['message' => 'Créneau enregistré.'], 200);
    }

    /**
     * Suppression d'un créneau
     */
    #[Route('/api/planning/creneau/{id}', name: 'api_planning_creneau_suppression', methods: ['DELETE'])]
    public function supprimerCreneau(int $id): JsonResponse
    {
        $utilisateur = $this->getUser();

        try {
            $this->planningService->supprimerCreneau($utilisateur, $id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        }

        return new JsonResponse(['message' => 'Créneau supprimé.'], 200);
    }
}