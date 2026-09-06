<?php

namespace App\Repository;

use App\Entity\Favori;
use App\Entity\Recette;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favori>
 */
class FavoriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favori::class);
    }

    /**
     * Vérifie si l'utilisateur a déjà mis cette recette en favori 
     */
    public function findOneByUtilisateurEtRecette(Utilisateur $utilisateur, Recette $recette): ?Favori
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.utilisateur = :utilisateur')
            ->andWhere('f.recette = :recette')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('recette', $recette)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne les recettes favorites de l'utilisateur (section "Mes favoris" du profil).
     *
     * @return Favori[]
     */
    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}