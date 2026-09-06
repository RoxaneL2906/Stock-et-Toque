<?php

namespace App\Repository;

use App\Entity\ListeCourses;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListeCourses>
 */
class ListeCoursesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeCourses::class);
    }

    /**
     * Retourne la liste de courses de l'utilisateur (US 4.1).
     * Un utilisateur n'a qu'une seule liste pour l'instant (gestion multi-listes = bonus US 8.7)
     */
    public function findOneByUtilisateur(Utilisateur $utilisateur): ?ListeCourses
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getOneOrNullResult();
    }
}