<?php

namespace App\DataFixtures;

use App\Entity\Allergie;
use App\Entity\Categorie;
use App\Entity\Equipement;
use App\Entity\IngredientRef;
use App\Entity\RegimeAlimentaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ReferentielFixtures extends Fixture
{
    public const REGIME_REFERENCE = 'regime_';
    public const ALLERGIE_REFERENCE = 'allergie_';
    public const EQUIPEMENT_REFERENCE = 'equipement_';
    public const CATEGORIE_REFERENCE = 'categorie_';
    public const INGREDIENT_REFERENCE = 'ingredient_';

    public function load(ObjectManager $manager): void
    {
        // ===== RÉGIMES ALIMENTAIRES =====
        $regimes = [
            'Végétarien', 'Végétalien / Végan', 'Pescétarien', 'Flexitarien',
            'Sans gluten', 'Sans lactose', 'Sans FODMAP', 'Hyposodique', 'Diabétique / IG contrôlé',
            'Halal', 'Casher',
            'Hyperprotéiné', 'Paléo', 'Keto / Cétogène', 'Low-carb', 'Régime méditerranéen',
        ];

        foreach ($regimes as $i => $libelle) {
            $regime = new RegimeAlimentaire();
            $regime->setLibelle($libelle);
            $manager->persist($regime);
            $this->addReference(self::REGIME_REFERENCE . $i, $regime);
        }

        // ===== ALLERGIES (14 allergènes officiels + intolérances courantes) =====
        $allergies = [
            'Gluten', 'Lupin', 'Crustacés', 'Mollusques', 'Œufs', 'Poissons', 'Arachides',
            'Fruits à coque', 'Sésame', 'Soja', 'Lait', 'Céleri', 'Moutarde', 'Sulfites',
            'Intolérance au lactose', 'Intolérance au fructose', 'Intolérance à l\'histamine',
            'Intolérance au sorbitol', 'Sensibilité au gluten non cœliaque',
        ];

        foreach ($allergies as $i => $nom) {
            $allergie = new Allergie();
            $allergie->setNom($nom);
            $manager->persist($allergie);
            $this->addReference(self::ALLERGIE_REFERENCE . $i, $allergie);
        }

        // ===== ÉQUIPEMENTS =====
        $equipements = [
            'Four', 'Micro-ondes', 'Plaques de cuisson', 'Autocuiseur', 'Cocotte-minute',
            'Robot cuiseur', 'Blender', 'Mixeur plongeant', 'Batteur électrique', 'Friteuse',
            'Airfryer', 'Machine à pain', 'Grill / Plancha', 'Wok', 'Cuiseur vapeur',
        ];

        foreach ($equipements as $i => $nom) {
            $equipement = new Equipement();
            $equipement->setNom($nom);
            $manager->persist($equipement);
            $this->addReference(self::EQUIPEMENT_REFERENCE . $i, $equipement);
        }

        // ===== CATÉGORIES ALIMENTAIRES =====
        $categories = [
            'Viandes', 'Charcuterie', 'Poissons', 'Fruits de mer', 'Légumes', 'Fruits',
            'Légumineuses', 'Féculents & céréales', 'Champignons', 'Produits laitiers et fromages',
            'Œufs', 'Épices et aromates', 'Condiments et sauces', 'Fruits secs & oléagineux',
            'Sucré & desserts',
        ];

        $categorieEntities = [];
        foreach ($categories as $i => $titre) {
            $categorie = new Categorie();
            $categorie->setTitre($titre);
            $manager->persist($categorie);
            $categorieEntities[$titre] = $categorie;
            $this->addReference(self::CATEGORIE_REFERENCE . $i, $categorie);
        }

        // ===== INGRÉDIENTS DE RÉFÉRENCE (liste simplifiée pour aller vite en fixtures) =====
        $ingredientsParCategorie = [
            'Viandes' => ['Bœuf', 'Porc', 'Poulet', 'Agneau', 'Dinde'],
            'Poissons' => ['Saumon', 'Thon', 'Cabillaud', 'Truite'],
            'Légumes' => ['Tomate', 'Carotte', 'Courgette', 'Épinards', 'Oignon', 'Poivron'],
            'Fruits' => ['Pomme', 'Banane', 'Citron', 'Fraise'],
            'Légumineuses' => ['Lentilles vertes', 'Pois chiches', 'Haricots rouges'],
            'Féculents & céréales' => ['Riz', 'Pâtes', 'Pomme de terre', 'Quinoa'],
            'Produits laitiers et fromages' => ['Lait', 'Beurre', 'Crème', 'Emmental', 'Chèvre frais'],
            'Œufs' => ['Œuf'],
            'Épices et aromates' => ['Ail', 'Oignon nouveau', 'Persil', 'Cumin', 'Curry'],
            'Condiments et sauces' => ['Huile d\'olive', 'Sauce soja', 'Moutarde'],
        ];

        $ingredientEntities = [];
        $index = 0;
        foreach ($ingredientsParCategorie as $nomCategorie => $noms) {
            foreach ($noms as $nom) {
                $ingredient = new IngredientRef();
                $ingredient->setNom($nom);
                $ingredient->addCategorie($categorieEntities[$nomCategorie]);
                $manager->persist($ingredient);
                $ingredientEntities[$nom] = $ingredient;
                $this->addReference(self::INGREDIENT_REFERENCE . $index, $ingredient);
                $index++;
            }
        }

        // ===== EXCLUSIONS RÉGIME <-> INGRÉDIENT (quelques exemples pour tester la logique) =====
        $regimeVegetarien = $this->getReference(self::REGIME_REFERENCE . '0', RegimeAlimentaire::class); // Végétarien
        $regimeVegan = $this->getReference(self::REGIME_REFERENCE . '1', RegimeAlimentaire::class); // Végétalien
        $regimeSansGluten = $this->getReference(self::REGIME_REFERENCE . '4', RegimeAlimentaire::class); // Sans gluten
        $regimeSansLactose = $this->getReference(self::REGIME_REFERENCE . '5', RegimeAlimentaire::class); // Sans lactose

        foreach (['Bœuf', 'Porc', 'Poulet', 'Agneau', 'Dinde', 'Saumon', 'Thon', 'Cabillaud', 'Truite'] as $viandePoisson) {
            $ingredientEntities[$viandePoisson]->addRegimeExclu($regimeVegetarien);
            $ingredientEntities[$viandePoisson]->addRegimeExclu($regimeVegan);
        }
        foreach (['Lait', 'Beurre', 'Crème', 'Emmental', 'Chèvre frais', 'Œuf'] as $produitAnimal) {
            $ingredientEntities[$produitAnimal]->addRegimeExclu($regimeVegan);
        }
        foreach (['Lait', 'Beurre', 'Crème', 'Emmental', 'Chèvre frais'] as $laitier) {
            $ingredientEntities[$laitier]->addRegimeExclu($regimeSansLactose);
        }
        foreach (['Pâtes'] as $glutenIngredient) {
            $ingredientEntities[$glutenIngredient]->addRegimeExclu($regimeSansGluten);
        }

        $manager->flush();
    }
}