<?php

namespace App\Controller;

use App\Service\StockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\OpenFoodFactsService;

class StockController extends AbstractController
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    /**
     * Consultation du stock
     */
    #[Route('/api/stock', name: 'api_stock_consultation', methods: ['GET'])]
    public function consulterStock(Request $request): JsonResponse
    {
        $utilisateur = $this->getUser();
        $emplacement = $request->query->get('emplacement');

        $stock = $this->stockService->consulterStock($utilisateur, $emplacement);

        return new JsonResponse($stock, 200);
    }

    /**
     * Modification de la quantité et de l'emplacement d'un stock
     */
    #[Route('/api/stock/{id}', name: 'api_stock_modification', methods: ['PUT'])]
    public function modifierStock(int $id, Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        if (!isset($donnees['quantite']) || empty($donnees['emplacement'])) {
            return new JsonResponse(['message' => "Les champs 'quantite' et 'emplacement' sont obligatoires."], 422);
        }

        $utilisateur = $this->getUser();

        try {
            $this->stockService->modifierStock($utilisateur, $id, $donnees['quantite'], $donnees['emplacement']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        } catch (\ValueError $e) {
            return new JsonResponse(['message' => 'Emplacement invalide.'], 422);
        }

        return new JsonResponse(['message' => 'Stock modifié avec succès.'], 200);
    }

    /**
     * Ajustement rapide de la quantité (+1 / -1)
     */
    #[Route('/api/stock/{id}/ajuster', name: 'api_stock_ajustement', methods: ['POST'])]
    public function ajusterQuantite(int $id, Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        if (!isset($donnees['delta']) || !in_array($donnees['delta'], [1, -1], true)) {
            return new JsonResponse(['message' => "Le champ 'delta' doit être 1 ou -1."], 422);
        }

        $utilisateur = $this->getUser();

        try {
            $nouvelleQuantite = $this->stockService->ajusterQuantite($utilisateur, $id, $donnees['delta']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        return new JsonResponse(['quantite' => $nouvelleQuantite], 200);
    }

    /**
     * Suppression d'un produit du stock
     */
    #[Route('/api/stock/{id}', name: 'api_stock_suppression', methods: ['DELETE'])]
    public function supprimerStock(int $id): JsonResponse
    {
        $utilisateur = $this->getUser();

        try {
            $this->stockService->supprimerStock($utilisateur, $id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        return new JsonResponse(['message' => 'Produit supprimé du stock.'], 200);
    }

    /**
     * Recherche de produits sur OpenFoodFacts
     */
    #[Route('/api/stock/recherche-produit', name: 'api_stock_recherche_produit', methods: ['GET'])]
    public function rechercherProduit(Request $request, OpenFoodFactsService $openFoodFactsService): JsonResponse
    {
        $recherche = $request->query->get('q');

        if (empty($recherche)) {
            return new JsonResponse(['message' => "Le paramètre 'q' est obligatoire."], 422);
        }

        $resultats = $openFoodFactsService->rechercherParNom($recherche);

        return new JsonResponse($resultats, 200);
    }

    /**
     * Ajout d'un produit au stock
     */
    #[Route('/api/stock', name: 'api_stock_ajout', methods: ['POST'])]
    public function ajouterAuStock(Request $request): JsonResponse
    {
        $donnees = json_decode($request->getContent(), true);

        $champsRequis = ['nom', 'quantite', 'emplacement'];
        foreach ($champsRequis as $champ) {
            if (!isset($donnees[$champ]) || $donnees[$champ] === '') {
                return new JsonResponse(['message' => "Le champ '$champ' est obligatoire."], 422);
            }
        }

        $utilisateur = $this->getUser();

        try {
            $this->stockService->ajouterAuStock(
                utilisateur: $utilisateur,
                nom: $donnees['nom'],
                quantite: $donnees['quantite'],
                emplacement: $donnees['emplacement'],
                unite: $donnees['unite'] ?? null,
                dlc: $donnees['dlc'] ?? null,
                ddm: $donnees['ddm'] ?? null,
                codeBarres: $donnees['codeBarres'] ?? null,
                photo: $donnees['photo'] ?? null,
                categorie: $donnees['categorie'] ?? null,
            );
        } catch (\ValueError $e) {
            return new JsonResponse(['message' => 'Emplacement ou unité invalide.'], 422);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Date invalide.'], 422);
        }

        return new JsonResponse(['message' => 'Produit ajouté au stock avec succès.'], 201);
    }
}