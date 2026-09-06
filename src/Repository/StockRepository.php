<?php

namespace App\Repository;

use App\Entity\Stock;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    /**
     * Retourne le stock d'un utilisateur, filtré par emplacement si précisé.
     *
     * @return Stock[]
     */
    public function findByUtilisateurEtEmplacement(Utilisateur $utilisateur, ?string $emplacement = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur);

        if ($emplacement !== null) {
            $qb->andWhere('s.emplacement = :emplacement')
                ->setParameter('emplacement', $emplacement);
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * Retourne un stock précis appartenant à l'utilisateur (vérifie qu'il ne modifie pas le stock d'un autre).
     */
    public function findOneByIdEtUtilisateur(int $id, Utilisateur $utilisateur): ?Stock
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.id = :id')
            ->andWhere('s.utilisateur = :utilisateur')
            ->setParameter('id', $id)
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Recherche si un utilisateur a déjà ce produit en stock à un emplacement donné
     */
    public function findOneByUtilisateurProduitEtEmplacement(
        Utilisateur $utilisateur,
        \App\Entity\Produit $produit,
        \App\Enum\EmplacementEnum $emplacement,
    ): ?Stock {
        return $this->createQueryBuilder('s')
            ->andWhere('s.utilisateur = :utilisateur')
            ->andWhere('s.produit = :produit')
            ->andWhere('s.emplacement = :emplacement')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('produit', $produit)
            ->setParameter('emplacement', $emplacement)
            ->getQuery()
            ->getOneOrNullResult();
    }
}