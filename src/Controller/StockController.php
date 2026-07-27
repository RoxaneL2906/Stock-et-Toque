<?php

namespace App\Controller;

use App\Service\StockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

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
}