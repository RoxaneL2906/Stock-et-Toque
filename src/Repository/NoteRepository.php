<?php

namespace App\Repository;

use App\Entity\Note;
use App\Entity\Recette;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * Retourne la note moyenne d'une recette ou null si aucune note.
     */
    public function calculerMoyenne(Recette $recette): ?float
    {
        $resultat = $this->createQueryBuilder('n')
            ->select('AVG(n.valeur) as moyenne')
            ->andWhere('n.recette = :recette')
            ->setParameter('recette', $recette)
            ->getQuery()
            ->getSingleScalarResult();

        return $resultat !== null ? (float) $resultat : null;
    }

    /**
     * Recherche si l'utilisateur a déjà noté cette recette 
     */
    public function findOneByUtilisateurEtRecette(Utilisateur $utilisateur, Recette $recette): ?Note
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.utilisateur = :utilisateur')
            ->andWhere('n.recette = :recette')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('recette', $recette)
            ->getQuery()
            ->getOneOrNullResult();
    }
}