<?php

namespace App\Repository;

use App\Entity\Planning;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Planning>
 */
class PlanningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planning::class);
    }

    /**
     * Retourne le planning de l'utilisateur pour une semaine précise
     */
    public function findOneByUtilisateurEtSemaine(Utilisateur $utilisateur, \DateTimeInterface $semaineDebut): ?Planning
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur = :utilisateur')
            ->andWhere('p.semaineDebut = :semaineDebut')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('semaineDebut', $semaineDebut)
            ->getQuery()
            ->getOneOrNullResult();
    }
}