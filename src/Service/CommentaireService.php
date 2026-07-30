<?php

namespace App\Service;

use App\Entity\Commentaire;
use App\Entity\Utilisateur;
use App\Repository\CommentaireRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommentaireService
{
    public function __construct(
        private readonly CommentaireRepository $commentaireRepository,
        private readonly RecetteRepository $recetteRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Liste les commentaires d'une recette publique.
     *
     * @throws \InvalidArgumentException si la recette n'existe pas ou n'est pas publique
     */
    public function listerCommentaires(int $recetteId): array
    {
        $recette = $this->recetteRepository->findOnePublique($recetteId);

        if ($recette === null) {
            throw new \InvalidArgumentException('Cette recette est introuvable ou n\'est plus publique.');
        }

        $commentaires = $this->commentaireRepository->findByRecette($recette);

        return array_map(fn (Commentaire $c) => $this->formaterCommentaire($c), $commentaires);
    }

    /**
     * Ajoute un commentaire sur une recette publique.
     *
     * @throws \InvalidArgumentException si la recette n'existe pas ou n'est pas publique
     */
    public function ajouterCommentaire(Utilisateur $utilisateur, int $recetteId, string $contenu): Commentaire
    {
        $recette = $this->recetteRepository->findOnePublique($recetteId);

        if ($recette === null) {
            throw new \InvalidArgumentException('Cette recette est introuvable ou n\'est plus publique.');
        }

        $commentaire = new Commentaire();
        $commentaire->setRecette($recette);
        $commentaire->setAuteur($utilisateur);
        $commentaire->setContenu($contenu);

        $this->entityManager->persist($commentaire);
        $this->entityManager->flush();

        return $commentaire;
    }

    /**
     * Modifie un commentaire (seul son auteur peut le faire).
     *
     * @throws \InvalidArgumentException si le commentaire n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function modifierCommentaire(Utilisateur $utilisateur, int $commentaireId, string $contenu): void
    {
        $commentaire = $this->commentaireRepository->findOneByIdEtAuteur($commentaireId, $utilisateur);

        if ($commentaire === null) {
            throw new \InvalidArgumentException('Ce commentaire ne vous appartient pas.');
        }

        $commentaire->setContenu($contenu);
        $this->entityManager->flush();
    }

    /**
     * Supprime un commentaire (seul son auteur peut le faire).
     *
     * @throws \InvalidArgumentException si le commentaire n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function supprimerCommentaire(Utilisateur $utilisateur, int $commentaireId): void
    {
        $commentaire = $this->commentaireRepository->findOneByIdEtAuteur($commentaireId, $utilisateur);

        if ($commentaire === null) {
            throw new \InvalidArgumentException('Ce commentaire ne vous appartient pas.');
        }

        $this->entityManager->remove($commentaire);
        $this->entityManager->flush();
    }

    private function formaterCommentaire(Commentaire $commentaire): array
    {
        return [
            'id' => $commentaire->getId(),
            'contenu' => $commentaire->getContenu(),
            'auteur' => $commentaire->getAuteur() !== null
                ? $commentaire->getAuteur()->getPrenom() . ' ' . $commentaire->getAuteur()->getNom()
                : 'Utilisateur anonyme',
            'auteurId' => $commentaire->getAuteur()?->getId(),
            'createdAt' => $commentaire->getCreatedAt()->format('Y-m-d H:i'),
        ];
    }
}