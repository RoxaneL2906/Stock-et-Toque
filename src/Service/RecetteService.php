<?php

namespace App\Service;

use App\Entity\EtapeRecette;
use App\Entity\Ingredient;
use App\Entity\Produit;
use App\Entity\Recette;
use App\Entity\Utilisateur;
use App\Enum\DifficulteEnum;
use App\Enum\VisibiliteEnum;
use App\Repository\EquipementRepository;
use App\Repository\ProduitRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RecetteService
{
    public function __construct(
        private readonly RecetteRepository $recetteRepository,
        private readonly EquipementRepository $equipementRepository,
        private readonly ProduitRepository $produitRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Crée une recette. 
     * Si $brouillon est true, la recette est enregistrée en brouillon 
     *
     * @param array $ingredients Liste de ['nom' => string, 'quantite' => ?float, 'unite' => ?string]
     * @param array $etapes Liste de descriptions (string), l'ordre suit l'index du tableau
     * @param array $equipementIds Liste d'IDs d'équipements existants
     */
    public function creerRecette(
        Utilisateur $utilisateur,
        string $titre,
        ?string $description,
        ?int $tempsPreparation,
        ?int $tempsCuisson,
        int $nbPersonnes,
        ?string $difficulte,
        ?string $budgetEstime,
        string $visibilite,
        bool $brouillon,
        array $ingredients,
        array $etapes,
        array $equipementIds,
    ): Recette {
        $recette = new Recette();
        $recette->setAuteur($utilisateur);
        $recette->setTitre($titre);
        $recette->setDescription($description);
        $recette->setTempsPreparation($tempsPreparation);
        $recette->setTempsCuisson($tempsCuisson);
        $recette->setNbPersonnes($nbPersonnes);
        $recette->setDifficulte($difficulte !== null ? DifficulteEnum::from($difficulte) : null);
        $recette->setBudgetEstime($budgetEstime);
        $recette->setVisibilite(VisibiliteEnum::from($visibilite));
        $recette->setBrouillon($brouillon);

        // Important : on persiste la recette AVANT d'ajouter ses ingrédients/étapes,
        // pour que Doctrine la connaisse déjà si un flush intermédiaire survient (ex: création d'un nouveau Produit)
        $this->entityManager->persist($recette);

        $this->appliquerEquipements($recette, $equipementIds);
        $this->appliquerIngredients($recette, $ingredients);
        $this->appliquerEtapes($recette, $etapes);

        $this->entityManager->flush();

        return $recette;
    }

    /**
     * Liste les recettes de l'utilisateur avec filtre optionnel par visibilité ('privee'/'publique') ou par brouillon.
     */
    public function listerMesRecettes(Utilisateur $utilisateur, ?string $visibilite = null, ?bool $brouillon = null): array
    {
        $recettes = $this->recetteRepository->findByAuteur($utilisateur, $visibilite, $brouillon);

        return array_map(fn (Recette $r) => $this->formaterRecetteResume($r), $recettes);
    }

    /**
     * Consulte le détail d'une recette appartenant à l'utilisateur 
     *
     * @throws \InvalidArgumentException si la recette n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function consulterMaRecette(Utilisateur $utilisateur, int $recetteId): array
    {
        $recette = $this->recetteRepository->findOneByIdEtAuteur($recetteId, $utilisateur);

        if ($recette === null) {
            throw new \InvalidArgumentException('Cette recette ne fait pas partie de vos recettes.');
        }

        return $this->formaterRecetteDetail($recette);
    }

    /**
     * Modifie une recette existante (seul l'auteur peut modifier).
     * Remplace entièrement les ingrédients/étapes/équipements par les nouvelles listes fournies.
     *
     * @throws \InvalidArgumentException si la recette n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function modifierRecette(
        Utilisateur $utilisateur,
        int $recetteId,
        string $titre,
        ?string $description,
        ?int $tempsPreparation,
        ?int $tempsCuisson,
        int $nbPersonnes,
        ?string $difficulte,
        ?string $budgetEstime,
        string $visibilite,
        bool $brouillon,
        array $ingredients,
        array $etapes,
        array $equipementIds,
    ): void {
        $recette = $this->recetteRepository->findOneByIdEtAuteur($recetteId, $utilisateur);

        if ($recette === null) {
            throw new \InvalidArgumentException('Cette recette ne fait pas partie de vos recettes.');
        }

        $recette->setTitre($titre);
        $recette->setDescription($description);
        $recette->setTempsPreparation($tempsPreparation);
        $recette->setTempsCuisson($tempsCuisson);
        $recette->setNbPersonnes($nbPersonnes);
        $recette->setDifficulte($difficulte !== null ? DifficulteEnum::from($difficulte) : null);
        $recette->setBudgetEstime($budgetEstime);
        $recette->setVisibilite(VisibiliteEnum::from($visibilite));
        $recette->setBrouillon($brouillon);

        foreach ($recette->getIngredients() as $ingredient) {
            $this->entityManager->remove($ingredient);
        }
        foreach ($recette->getEtapes() as $etape) {
            $this->entityManager->remove($etape);
        }
        $recette->getEquipements()->clear();

        $this->appliquerEquipements($recette, $equipementIds);
        $this->appliquerIngredients($recette, $ingredients);
        $this->appliquerEtapes($recette, $etapes);

        $this->entityManager->flush();
    }

    /**
     * Supprime une recette, ou la repasse en privé.
     * Si elle était publique, une version anonymisée est conservée pour la communauté,
     * et l'auteur récupère automatiquement une copie privée dans "Mes recettes".
     *
     * @throws \InvalidArgumentException si la recette n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function supprimerRecette(Utilisateur $utilisateur, int $recetteId): void
    {
        $recette = $this->recetteRepository->findOneByIdEtAuteur($recetteId, $utilisateur);

        if ($recette === null) {
            throw new \InvalidArgumentException('Cette recette ne fait pas partie de vos recettes.');
        }

        if ($recette->getVisibilite() === VisibiliteEnum::PUBLIQUE) {
            $this->anonymiserEtDupliquer($utilisateur, $recette);
            return;
        }

        $this->entityManager->remove($recette);
        $this->entityManager->flush();
    }

    /**
     * Met à jour la photo d'une recette (upload Vich).
     *
     * @throws \InvalidArgumentException si la recette n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function modifierPhoto(Utilisateur $utilisateur, int $recetteId, UploadedFile $fichier): void
    {
        $recette = $this->recetteRepository->findOneByIdEtAuteur($recetteId, $utilisateur);

        if ($recette === null) {
            throw new \InvalidArgumentException('Cette recette ne fait pas partie de vos recettes.');
        }

        $recette->setImageFile($fichier);
        $this->entityManager->flush();
    }

    /**
     * Associe les équipements existants (par ID) à la recette.
     */
    private function appliquerEquipements(Recette $recette, array $equipementIds): void
    {
        if (empty($equipementIds)) {
            return;
        }

        $equipements = $this->equipementRepository->findBy(['id' => $equipementIds]);
        foreach ($equipements as $equipement) {
            $recette->addEquipement($equipement);
        }
    }

    /**
     * Crée les lignes d'ingrédients. Le produit générique est recherché par nom
     * (créé s'il n'existe pas), même logique que pour le Stock et la Liste de courses.
     * Pas de flush() intermédiaire ici : tout est flushé en une seule fois à la fin
     * de creerRecette()/modifierRecette(), une fois la Recette déjà persistée.
     */
    private function appliquerIngredients(Recette $recette, array $ingredients): void
    {
        foreach ($ingredients as $donneesIngredient) {
            if (empty($donneesIngredient['nom'])) {
                continue;
            }

            $produit = $this->produitRepository->findOneByNom($donneesIngredient['nom']);
            if ($produit === null) {
                $produit = new Produit();
                $produit->setNom($donneesIngredient['nom']);
                $this->entityManager->persist($produit);
            }

            $ingredient = new Ingredient();
            $ingredient->setRecette($recette);
            $ingredient->setProduit($produit);
            $ingredient->setQuantite($donneesIngredient['quantite'] ?? null);
            $ingredient->setUnite($donneesIngredient['unite'] ?? null);

            $this->entityManager->persist($ingredient);
        }
    }

    /**
     * Crée les étapes de préparation, dans l'ordre du tableau reçu.
     */
    private function appliquerEtapes(Recette $recette, array $etapes): void
    {
        foreach ($etapes as $index => $description) {
            if (trim($description) === '') {
                continue;
            }

            $etape = new EtapeRecette();
            $etape->setRecette($recette);
            $etape->setOrdre($index);
            $etape->setDescription($description);

            $this->entityManager->persist($etape);
        }
    }

    /**
     * Anonymise la recette publique originale (reste visible à la communauté, auteur retiré),
     * et crée une copie privée identique pour l'auteur, dans "Mes recettes".
     */
    private function anonymiserEtDupliquer(Utilisateur $utilisateur, Recette $recette): void
    {
        $recette->setAuteur(null);
        $recette->setAnonymisee(true);

        $copie = new Recette();
        $copie->setAuteur($utilisateur);
        $copie->setTitre($recette->getTitre());
        $copie->setDescription($recette->getDescription());
        $copie->setTempsPreparation($recette->getTempsPreparation());
        $copie->setTempsCuisson($recette->getTempsCuisson());
        $copie->setNbPersonnes($recette->getNbPersonnes());
        $copie->setDifficulte($recette->getDifficulte());
        $copie->setBudgetEstime($recette->getBudgetEstime());
        $copie->setPhoto($recette->getPhoto());
        $copie->setVisibilite(VisibiliteEnum::PRIVEE);
        $copie->setBrouillon(false);

        $this->entityManager->persist($copie);

        foreach ($recette->getEquipements() as $equipement) {
            $copie->addEquipement($equipement);
        }

        foreach ($recette->getIngredients() as $ingredientOriginal) {
            $ingredientCopie = new Ingredient();
            $ingredientCopie->setRecette($copie);
            $ingredientCopie->setProduit($ingredientOriginal->getProduit());
            $ingredientCopie->setQuantite($ingredientOriginal->getQuantite());
            $ingredientCopie->setUnite($ingredientOriginal->getUnite());
            $this->entityManager->persist($ingredientCopie);
        }

        foreach ($recette->getEtapes() as $etapeOriginale) {
            $etapeCopie = new EtapeRecette();
            $etapeCopie->setRecette($copie);
            $etapeCopie->setOrdre($etapeOriginale->getOrdre());
            $etapeCopie->setDescription($etapeOriginale->getDescription());
            $this->entityManager->persist($etapeCopie);
        }

        $this->entityManager->flush();
    }

    /**
     * Format résumé (pour les listes "Mes recettes").
     */
    private function formaterRecetteResume(Recette $recette): array
    {
        return [
            'id' => $recette->getId(),
            'titre' => $recette->getTitre(),
            'photo' => $recette->getPhoto(),
            'tempsPreparation' => $recette->getTempsPreparation(),
            'tempsCuisson' => $recette->getTempsCuisson(),
            'visibilite' => $recette->getVisibilite()->value,
            'brouillon' => $recette->isBrouillon(),
        ];
    }

    /**
     * Format détaillé (pour la fiche recette complète).
     */
    private function formaterRecetteDetail(Recette $recette): array
    {
        $ingredients = [];
        foreach ($recette->getIngredients() as $ingredient) {
            $ingredients[] = [
                'id' => $ingredient->getId(),
                'nom' => $ingredient->getProduit()->getNom(),
                'quantite' => $ingredient->getQuantite(),
                'unite' => $ingredient->getUnite(),
            ];
        }

        $etapes = [];
        foreach ($recette->getEtapes() as $etape) {
            $etapes[] = [
                'ordre' => $etape->getOrdre(),
                'description' => $etape->getDescription(),
            ];
        }

        $equipements = [];
        foreach ($recette->getEquipements() as $equipement) {
            $equipements[] = [
                'id' => $equipement->getId(),
                'nom' => $equipement->getNom(),
            ];
        }

        return [
            'id' => $recette->getId(),
            'titre' => $recette->getTitre(),
            'description' => $recette->getDescription(),
            'photo' => $recette->getPhoto(),
            'tempsPreparation' => $recette->getTempsPreparation(),
            'tempsCuisson' => $recette->getTempsCuisson(),
            'nbPersonnes' => $recette->getNbPersonnes(),
            'difficulte' => $recette->getDifficulte()?->value,
            'budgetEstime' => $recette->getBudgetEstime(),
            'visibilite' => $recette->getVisibilite()->value,
            'brouillon' => $recette->isBrouillon(),
            'equipements' => $equipements,
            'ingredients' => $ingredients,
            'etapes' => $etapes,
        ];
    }
}