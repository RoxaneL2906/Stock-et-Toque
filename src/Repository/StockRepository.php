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
}