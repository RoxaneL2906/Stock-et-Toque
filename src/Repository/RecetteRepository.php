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

    /**
     * Récupère une recette publique par son ID 
     * Une recette publique reste consultable même si son auteur a été anonymisé.
     */
    public function findOnePublique(int $id): ?Recette
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.id = :id')
            ->andWhere('r.visibilite = :visibilite')
            ->setParameter('id', $id)
            ->setParameter('visibilite', VisibiliteEnum::PUBLIQUE)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Recherche de recettes publiques avec filtres combinables 
     * Ne renvoie jamais les brouillons 
     *
     * @param string[] $ingredientsInclus Noms d'ingrédients à inclure (recherche partielle, insensible à la casse/accents)
     * @param string[] $ingredientsExclus Noms d'ingrédients à exclure
     *
     * @return Recette[]
     */
    public function rechercherPubliques(
        ?string $recherche = null,
        array $ingredientsInclus = [],
        array $ingredientsExclus = [],
        ?int $tempsMax = null,
        ?int $nbPersonnes = null,
        ?string $difficulte = null,
        ?string $budgetMax = null,
        string $tri = 'recent',
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.visibilite = :visibilite')
            ->setParameter('visibilite', VisibiliteEnum::PUBLIQUE);

        if ($recherche !== null && trim($recherche) !== '') {
            // insensible aux accents : on compare via une fonction native MySQL (collation par défaut déjà insensible aux accents)
            $qb->andWhere('LOWER(r.titre) LIKE LOWER(:recherche)')
                ->setParameter('recherche', '%' . $recherche . '%');
        }

        foreach ($ingredientsInclus as $index => $nomIngredient) {
            $alias = 'ingInclus' . $index;
            $qb->join('r.ingredients', $alias)
                ->join($alias . '.produit', $alias . 'Produit')
                ->andWhere("LOWER({$alias}Produit.nom) LIKE LOWER(:{$alias})")
                ->setParameter($alias, '%' . $nomIngredient . '%');
        }

        if (!empty($ingredientsExclus)) {
            $sousRequete = $this->getEntityManager()->createQueryBuilder()
                ->select('IDENTITY(ingExclu.recette)')
                ->from('App\Entity\Ingredient', 'ingExclu')
                ->join('ingExclu.produit', 'produitExclu')
                ->where('LOWER(produitExclu.nom) IN (:nomsExclus)');

            $qb->andWhere($qb->expr()->notIn('r.id', $sousRequete->getDQL()))
                ->setParameter('nomsExclus', array_map('strtolower', $ingredientsExclus));
        }

        if ($tempsMax !== null) {
            $qb->andWhere('(COALESCE(r.tempsPreparation, 0) + COALESCE(r.tempsCuisson, 0)) <= :tempsMax')
                ->setParameter('tempsMax', $tempsMax);
        }

        if ($nbPersonnes !== null) {
            $qb->andWhere('r.nbPersonnes = :nbPersonnes')
                ->setParameter('nbPersonnes', $nbPersonnes);
        }

        if ($difficulte !== null) {
            $qb->andWhere('r.difficulte = :difficulte')
                ->setParameter('difficulte', \App\Enum\DifficulteEnum::from($difficulte));
        }

        if ($budgetMax !== null) {
            $qb->andWhere('r.budgetEstime <= :budgetMax')
                ->setParameter('budgetMax', $budgetMax);
        }

        match ($tri) {
            'recent' => $qb->orderBy('r.createdAt', 'DESC'),
            'rapide' => $qb->orderBy('r.tempsPreparation', 'ASC'),
            default => $qb->orderBy('r.createdAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }
}