<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\StockRepository;

class StockService
{
    public function __construct(
        private readonly StockRepository $stockRepository,
    ) {}

    /**
     * Retourne le stock de l'utilisateur, avec un statut d'alerte calculé pour chaque produit selon sa DLC/DDM.
     *
     * @param string|null $emplacement 'frigo', 'placard', ou null pour tout afficher (CA2)
     */
    public function consulterStock(Utilisateur $utilisateur, ?string $emplacement = null): array
    {
        $stocks = $this->stockRepository->findByUtilisateurEtEmplacement($utilisateur, $emplacement);

        $resultat = [];
        foreach ($stocks as $stock) {
            $resultat[] = [
                'id' => $stock->getId(),
                'nom' => $stock->getProduit()->getNom(),
                'photo' => $stock->getProduit()->getPhoto(),
                'quantite' => $stock->getQuantite(),
                'emplacement' => $stock->getEmplacement()->value,
                'dlc' => $stock->getDlc()?->format('Y-m-d'),
                'ddm' => $stock->getDdm()?->format('Y-m-d'),
                'alerte' => $this->calculerAlerte($stock),
            ];
        }

        return $resultat;
    }

    /**
     * Calcule le niveau d'alerte d'un produit selon sa DLC/DDM.
     * 'rouge' = date atteinte ou dépassée, 'orange' = proche (5j DLC / 7j DDM), 'aucune' = pas d'alerte.
     */
    private function calculerAlerte(\App\Entity\Stock $stock): string
    {
        $aujourdhui = new \DateTimeImmutable('today');

        if ($stock->getDlc() !== null) {
            $dlc = \DateTimeImmutable::createFromInterface($stock->getDlc());
            $joursRestants = $aujourdhui->diff($dlc)->days * ($dlc >= $aujourdhui ? 1 : -1);

            if ($joursRestants <= 0) {
                return 'rouge';
            }
            if ($joursRestants <= 5) {
                return 'orange';
            }
        }

        if ($stock->getDdm() !== null) {
            $ddm = \DateTimeImmutable::createFromInterface($stock->getDdm());
            $joursRestants = $aujourdhui->diff($ddm)->days * ($ddm >= $aujourdhui ? 1 : -1);

            if ($joursRestants <= 0) {
                return 'rouge';
            }
            if ($joursRestants <= 7) {
                return 'orange';
            }
        }

        return 'aucune';
    }
}