<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\StockRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockService
{
    public function __construct(
        private readonly StockRepository $stockRepository,
        private readonly ProduitRepository $produitRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Retourne le stock de l'utilisateur, avec un statut d'alerte calculé pour chaque produit selon sa DLC/DDM.
     *
     * @param string|null $emplacement 'frigo', 'placard', ou null pour tout afficher
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
                'unite' => $stock->getUnite()?->value,
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

    /**
     * Modifie la quantité et/ou l'emplacement d'un stock.
     *
     * @throws \InvalidArgumentException si le stock n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function modifierStock(Utilisateur $utilisateur, int $stockId, int $quantite, string $emplacement): void
    {
        $stock = $this->stockRepository->findOneByIdEtUtilisateur($stockId, $utilisateur);

        if ($stock === null) {
            throw new \InvalidArgumentException('Ce produit ne fait pas partie de votre stock.');
        }

        $stock->setQuantite($quantite);
        $stock->setEmplacement(\App\Enum\EmplacementEnum::from($emplacement));

        $this->entityManager->flush();
    }

    /**
     * Incrémente ou décrémente la quantité d'un stock de 1.
     * Ne descend jamais en dessous de 0.
     *
     * @return int La nouvelle quantité, pour que le front sache si elle atteint 0 (proposer suppression)
     *
     * @throws \InvalidArgumentException si le stock n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function ajusterQuantite(Utilisateur $utilisateur, int $stockId, int $delta): int
    {
        $stock = $this->stockRepository->findOneByIdEtUtilisateur($stockId, $utilisateur);

        if ($stock === null) {
            throw new \InvalidArgumentException('Ce produit ne fait pas partie de votre stock.');
        }

        $nouvelleQuantite = max(0, $stock->getQuantite() + $delta);
        $stock->setQuantite($nouvelleQuantite);

        $this->entityManager->flush();

        return $nouvelleQuantite;
    }

    /**
     * Supprime un produit du stock.
     *
     * @throws \InvalidArgumentException si le stock n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function supprimerStock(Utilisateur $utilisateur, int $stockId): void
    {
        $stock = $this->stockRepository->findOneByIdEtUtilisateur($stockId, $utilisateur);

        if ($stock === null) {
            throw new \InvalidArgumentException('Ce produit ne fait pas partie de votre stock.');
        }

        $this->entityManager->remove($stock);
        $this->entityManager->flush();
    }

    /**
     * Ajoute un produit au stock. Si le produit (même nom) existe déjà
     * au même emplacement, sa quantité est incrémentée plutôt qu'une nouvelle entrée créée.
     */
    public function ajouterAuStock(
        \App\Entity\Utilisateur $utilisateur,
        string $nom,
        int $quantite,
        string $emplacement,
        ?string $unite = null,
        ?string $dlc = null,
        ?string $ddm = null,
        ?string $codeBarres = null,
        ?string $photo = null,
        ?string $categorie = null,
    ): void {
        $emplacementEnum = \App\Enum\EmplacementEnum::from($emplacement);
        $uniteEnum = $unite !== null ? \App\Enum\UniteEnum::from($unite) : null;

        // Recherche du produit générique par nom (créé s'il n'existe pas)
        $produit = $this->produitRepository->findOneByNom($nom);
        if ($produit === null) {
            $produit = new \App\Entity\Produit();
            $produit->setNom($nom);
            $produit->setCodeBarres($codeBarres);
            $produit->setPhoto($photo);
            $produit->setCategorie($categorie);
            $this->entityManager->persist($produit);
            $this->entityManager->flush();
        }

        // CA4 : fusion si ce produit est déjà dans le stock de l'utilisateur, au même emplacement
        $stockExistant = $this->stockRepository->findOneByUtilisateurProduitEtEmplacement($utilisateur, $produit, $emplacementEnum);

        if ($stockExistant !== null) {
            $stockExistant->setQuantite($stockExistant->getQuantite() + $quantite);
            $this->entityManager->flush();
            return;
        }

        $stock = new \App\Entity\Stock();
        $stock->setUtilisateur($utilisateur);
        $stock->setProduit($produit);
        $stock->setQuantite($quantite);
        $stock->setEmplacement($emplacementEnum);
        $stock->setUnite($uniteEnum);
        $stock->setDlc($dlc !== null ? new \DateTime($dlc) : null);
        $stock->setDdm($ddm !== null ? new \DateTime($ddm) : null);

        $this->entityManager->persist($stock);
        $this->entityManager->flush();
    }
}