<?php

namespace App\Repository;

use App\Entity\Recette;
use App\Entity\Utilisateur;
use App\Enum\VisibiliteEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recette>
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    /**
     * Retourne les recettes de l'utilisateur, triées de la plus récente à la plus ancienne.
     * Filtre optionnel par visibilité ('privee', 'publique') ou par brouillon.
     *
     * @return Recette[]
     */
    public function findByAuteur(Utilisateur $utilisateur, ?string $visibilite = null, ?bool $brouillon = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.auteur = :auteur')
            ->setParameter('auteur', $utilisateur)
            ->orderBy('r.createdAt', 'DESC');

        if ($visibilite !== null) {
            $qb->andWhere('r.visibilite = :visibilite')
                ->setParameter('visibilite', VisibiliteEnum::from($visibilite));
        }

        if ($brouillon !== null) {
            $qb->andWhere('r.brouillon = :brouillon')
                ->setParameter('brouillon', $brouillon);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère une recette précise en vérifiant qu'elle appartient bien à l'utilisateur
     * (sécurité : empêche de modifier/supprimer la recette de quelqu'un d'autre).
     */
    public function findOneByIdEtAuteur(int $id, Utilisateur $utilisateur): ?Recette
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.id = :id')
            ->andWhere('r.auteur = :auteur')
            ->setParameter('id', $id)
            ->setParameter('auteur', $utilisateur)
            ->getQuery()
            ->getOneOrNullResult();
    }
}