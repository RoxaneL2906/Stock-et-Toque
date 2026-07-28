<?php

namespace App\Service;

use App\Entity\ArticleListe;
use App\Entity\ListeCourses;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Enum\CategorieAchatEnum;
use App\Enum\EmplacementEnum;
use App\Repository\ArticleListeRepository;
use App\Repository\ListeCoursesRepository;
use App\Repository\ProduitRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;

class ListeCoursesService
{
    public function __construct(
        private readonly ListeCoursesRepository $listeCoursesRepository,
        private readonly ArticleListeRepository $articleListeRepository,
        private readonly ProduitRepository $produitRepository,
        private readonly StockRepository $stockRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Retourne (et crée si besoin) la liste de courses de l'utilisateur, avec ses articles 
     */
    public function consulterListe(Utilisateur $utilisateur): array
    {
        $liste = $this->recupererOuCreerListe($utilisateur);

        $resultat = [];
        foreach ($liste->getArticles() as $article) {
            $resultat[] = $this->formaterArticle($article);
        }

        return $resultat;
    }

    /**
     * Ajoute un produit à la liste de courses. 
     * Si le produit y est déjà, la quantité est incrémentée plutôt qu'un doublon créé (même logique que le stock).
     */
    public function ajouterArticle(
        Utilisateur $utilisateur,
        string $nom,
        int $quantite,
        ?string $categorieAchat = null,
        ?string $codeBarres = null,
        ?string $photo = null,
        ?string $categorieProduit = null,
    ): void {
        $liste = $this->recupererOuCreerListe($utilisateur);
        $categorieAchatEnum = $categorieAchat !== null ? CategorieAchatEnum::from($categorieAchat) : null;

        $produit = $this->produitRepository->findOneByNom($nom);
        if ($produit === null) {
            $produit = new Produit();
            $produit->setNom($nom);
            $produit->setCodeBarres($codeBarres);
            $produit->setPhoto($photo);
            $produit->setCategorie($categorieProduit);
            $this->entityManager->persist($produit);
            $this->entityManager->flush();
        }

        $articleExistant = $this->articleListeRepository->findOneByListeEtProduit($liste, $produit);

        if ($articleExistant !== null) {
            $articleExistant->setQuantite($articleExistant->getQuantite() + $quantite);
            if ($categorieAchatEnum !== null) {
                $articleExistant->setCategorieAchat($categorieAchatEnum);
            }
            $this->entityManager->flush();
            return;
        }

        $article = new ArticleListe();
        $article->setListe($liste);
        $article->setProduit($produit);
        $article->setQuantite($quantite);
        $article->setCategorieAchat($categorieAchatEnum);

        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    /**
     * Coche ou décoche un article 
     *
     * @throws \InvalidArgumentException si l'article n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function basculerCoche(Utilisateur $utilisateur, int $articleId): bool
    {
        $article = $this->articleListeRepository->findOneByIdEtUtilisateur($articleId, $utilisateur);

        if ($article === null) {
            throw new \InvalidArgumentException("Cet article ne fait pas partie de votre liste.");
        }

        $article->setCoche(!$article->isCoche());
        $this->entityManager->flush();

        return $article->isCoche();
    }

    /**
     * Supprime un article de la liste.
     *
     * @throws \InvalidArgumentException si l'article n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function supprimerArticle(Utilisateur $utilisateur, int $articleId): void
    {
        $article = $this->articleListeRepository->findOneByIdEtUtilisateur($articleId, $utilisateur);

        if ($article === null) {
            throw new \InvalidArgumentException("Cet article ne fait pas partie de votre liste.");
        }

        $this->entityManager->remove($article);
        $this->entityManager->flush();
    }

    /**
     * Archive les articles cochés : les retire de la liste et,
     * si demandé, les ajoute au stock (emplacement "placard" par défaut).
     * Les articles non cochés restent dans la liste.
     */
    public function archiverCoches(Utilisateur $utilisateur, bool $ajouterAuStock): void
    {
        $liste = $this->recupererOuCreerListe($utilisateur);

        foreach ($liste->getArticles() as $article) {
            if (!$article->isCoche()) {
                continue;
            }

            if ($ajouterAuStock) {
                $this->ajouterArticleAuStock($utilisateur, $article);
            }

            $this->entityManager->remove($article);
        }

        $this->entityManager->flush();
    }

    /**
     * Ajoute un article archivé au stock de l'utilisateur (emplacement "placard" par défaut),
     * avec fusion de quantité si le produit y est déjà.
     */
    private function ajouterArticleAuStock(Utilisateur $utilisateur, ArticleListe $article): void
    {
        $produit = $article->getProduit();
        $emplacement = EmplacementEnum::PLACARD;

        $stockExistant = $this->stockRepository->findOneByUtilisateurProduitEtEmplacement($utilisateur, $produit, $emplacement);

        if ($stockExistant !== null) {
            $stockExistant->setQuantite($stockExistant->getQuantite() + $article->getQuantite());
            return;
        }

        $stock = new \App\Entity\Stock();
        $stock->setUtilisateur($utilisateur);
        $stock->setProduit($produit);
        $stock->setQuantite($article->getQuantite());
        $stock->setEmplacement($emplacement);

        $this->entityManager->persist($stock);
    }

    /**
     * Récupère la liste de courses de l'utilisateur, ou la crée automatiquement si elle n'existe pas encore.
     */
    private function recupererOuCreerListe(Utilisateur $utilisateur): ListeCourses
    {
        $liste = $this->listeCoursesRepository->findOneByUtilisateur($utilisateur);

        if ($liste === null) {
            $liste = new ListeCourses();
            $liste->setUtilisateur($utilisateur);
            $this->entityManager->persist($liste);
            $this->entityManager->flush();
        }

        return $liste;
    }

    private function formaterArticle(ArticleListe $article): array
    {
        return [
            'id' => $article->getId(),
            'nom' => $article->getProduit()->getNom(),
            'photo' => $article->getProduit()->getPhoto(),
            'quantite' => $article->getQuantite(),
            'coche' => $article->isCoche(),
            'categorieAchat' => $article->getCategorieAchat()?->value,
        ];
    }
}