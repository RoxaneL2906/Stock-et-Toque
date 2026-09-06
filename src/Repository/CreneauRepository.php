<?php

namespace App\Repository;

use App\Entity\Creneau;
use App\Entity\Planning;
use App\Entity\Utilisateur;
use App\Enum\JourSemaineEnum;
use App\Enum\MomentEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Creneau>
 */
class CreneauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Creneau::class);
    }

    /**
     * Retrouve un créneau précis (jour + moment) dans un planning donné,
     * pour savoir s'il faut le créer ou le remplacer.
     */
    public function findOneByPlanningJourEtMoment(Planning $planning, JourSemaineEnum $jour, MomentEnum $moment): ?Creneau
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.planning = :planning')
            ->andWhere('c.jour = :jour')
            ->andWhere('c.moment = :moment')
            ->setParameter('planning', $planning)
            ->setParameter('jour', $jour)
            ->setParameter('moment', $moment)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère un créneau précis en vérifiant qu'il appartient bien à l'utilisateur
     * (sécurité : empêche de modifier/supprimer le créneau de quelqu'un d'autre).
     */
    public function findOneByIdEtUtilisateur(int $id, Utilisateur $utilisateur): ?Creneau
    {
        return $this->createQueryBuilder('c')
            ->join('c.planning', 'p')
            ->andWhere('c.id = :id')
            ->andWhere('p.utilisateur = :utilisateur')
            ->setParameter('id', $id)
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getOneOrNullResult();
    }
}