<?php

namespace App\Repository;

use App\Entity\Commentaire;
use App\Entity\Recette;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentaire>
 */
class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    /**
     * Retourne les commentaires d'une recette, du plus récent au plus ancien.
     * Les commentaires modérés (bonus) ne sont pas exclus ici, à gérer plus tard si besoin.
     *
     * @return Commentaire[]
     */
    public function findByRecette(Recette $recette): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.recette = :recette')
            ->setParameter('recette', $recette)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère un commentaire précis en vérifiant qu'il appartient bien à l'utilisateur
     * (sécurité : empêche de modifier/supprimer le commentaire de quelqu'un d'autre).
     */
    public function findOneByIdEtAuteur(int $id, Utilisateur $utilisateur): ?Commentaire
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->andWhere('c.auteur = :auteur')
            ->setParameter('id', $id)
            ->setParameter('auteur', $utilisateur)
            ->getQuery()
            ->getOneOrNullResult();
    }
}