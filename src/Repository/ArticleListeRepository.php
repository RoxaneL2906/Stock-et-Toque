<?php

namespace App\Repository;

use App\Entity\ArticleListe;
use App\Entity\ListeCourses;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleListe>
 */
class ArticleListeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleListe::class);
    }

    /**
     * Recherche si ce produit est déjà présent dans la liste (Évite les doublons,
     * même logique que la fusion de quantité sur le stock).
     */
    public function findOneByListeEtProduit(ListeCourses $liste, Produit $produit): ?ArticleListe
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.liste = :liste')
            ->andWhere('a.produit = :produit')
            ->setParameter('liste', $liste)
            ->setParameter('produit', $produit)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère un article précis en vérifiant qu'il appartient bien à la liste de l'utilisateur
     * (sécurité : empêche de modifier/supprimer l'article de quelqu'un d'autre).
     */
    public function findOneByIdEtUtilisateur(int $id, \App\Entity\Utilisateur $utilisateur): ?ArticleListe
    {
        return $this->createQueryBuilder('a')
            ->join('a.liste', 'l')
            ->andWhere('a.id = :id')
            ->andWhere('l.utilisateur = :utilisateur')
            ->setParameter('id', $id)
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getOneOrNullResult();
    }
}