<?php

namespace App\DataFixtures;

use App\Entity\EtapeRecette;
use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\Utilisateur;
use App\Enum\DifficulteEnum;
use App\Enum\VisibiliteEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RecetteFixtures extends Fixture implements DependentFixtureInterface
{
    private const NB_PRODUITS = 39;
    private const NB_EQUIPEMENTS = 15;
    private const NB_UTILISATEURS = 6;

    public const RECETTE_PUBLIQUE_REFERENCE = 'recette_publique_';

    public function getDependencies(): array
    {
        return [UtilisateurFixtures::class, ProduitFixtures::class, ReferentielFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $titresRecettes = [
            'Poulet rôti aux herbes', 'Curry de légumes', 'Saumon grillé au citron', 'Pâtes à la carbonara',
            'Salade de quinoa', 'Bœuf bourguignon', 'Soupe de légumes', 'Tarte aux pommes', 'Risotto aux champignons',
            'Wok de poulet', 'Gratin dauphinois', 'Chili con carne', 'Ratatouille', 'Poêlée de légumes d\'été',
            'Tajine d\'agneau', 'Blanquette de veau', 'Gaspacho', 'Croque-monsieur', 'Omelette aux fines herbes',
            'Poisson en papillote', 'Riz sauté aux légumes', 'Pizza maison', 'Lasagnes', 'Tarte salée',
        ];

        $difficultes = DifficulteEnum::cases();

        // ===== 200 RECETTES PUBLIQUES =====
        for ($i = 0; $i < 200; $i++) {
            $recette = new Recette();
            $recette->setTitre($faker->randomElement($titresRecettes) . ' ' . $faker->numberBetween(1, 999));
            $recette->setDescription($faker->paragraph(3));
            $recette->setTempsPreparation($faker->numberBetween(10, 60));
            $recette->setTempsCuisson($faker->optional(0.8)->numberBetween(5, 90));
            $recette->setNbPersonnes($faker->numberBetween(1, 8));
            $recette->setDifficulte($faker->randomElement($difficultes));
            $recette->setBudgetEstime((string) $faker->randomFloat(2, 3, 40));
            $recette->setVisibilite(VisibiliteEnum::PUBLIQUE);
            $recette->setBrouillon(false);

            // Auteur aléatoire parmi les 6 utilisateurs standard
            $auteurIdx = $faker->numberBetween(0, self::NB_UTILISATEURS - 1);
            $recette->setAuteur($this->getReference(UtilisateurFixtures::USER_REFERENCE . $auteurIdx, Utilisateur::class));

            $manager->persist($recette);
            $this->addReference(self::RECETTE_PUBLIQUE_REFERENCE . $i, $recette);

            // Étapes (3 à 6 par recette)
            $nbEtapes = $faker->numberBetween(3, 6);
            for ($ordre = 1; $ordre <= $nbEtapes; $ordre++) {
                $etape = new EtapeRecette();
                $etape->setRecette($recette);
                $etape->setOrdre($ordre);
                $etape->setDescription($faker->sentence(12));
                $manager->persist($etape);
            }

            // Ingrédients (3 à 8 par recette, produits aléatoires sans doublon)
            $nbIngredients = $faker->numberBetween(3, 8);
            $indicesProduits = $faker->randomElements(range(0, self::NB_PRODUITS - 1), $nbIngredients);
            foreach ($indicesProduits as $indexProduit) {
                $ingredient = new Ingredient();
                $ingredient->setRecette($recette);
                $ingredient->setProduit($this->getReference(ProduitFixtures::PRODUIT_REFERENCE . $indexProduit, \App\Entity\Produit::class));
                $ingredient->setQuantite((string) $faker->numberBetween(1, 500));
                $ingredient->setUnite($faker->randomElement(['g', 'kg', 'ml', 'l', 'pièce(s)', 'cuillère(s) à soupe']));
                $manager->persist($ingredient);
            }

            // Équipements (0 à 2 par recette)
            $nbEquipements = $faker->numberBetween(0, 2);
            $indicesEquipements = $faker->randomElements(range(0, self::NB_EQUIPEMENTS - 1), $nbEquipements);
            foreach ($indicesEquipements as $indexEquipement) {
                $recette->addEquipement($this->getReference(ReferentielFixtures::EQUIPEMENT_REFERENCE . $indexEquipement, \App\Entity\Equipement::class));
            }
        }

        // ===== QUELQUES RECETTES PRIVÉES =====
        for ($i = 0; $i < 5; $i++) {
            $recette = new Recette();
            $recette->setTitre('Recette privée de test ' . ($i + 1));
            $recette->setDescription($faker->paragraph(2));
            $recette->setTempsPreparation($faker->numberBetween(10, 45));
            $recette->setNbPersonnes($faker->numberBetween(1, 4));
            $recette->setVisibilite(VisibiliteEnum::PRIVEE);
            $recette->setBrouillon(false);
            $recette->setAuteur($this->getReference(UtilisateurFixtures::USER_REFERENCE . ($i % self::NB_UTILISATEURS), Utilisateur::class));
            $manager->persist($recette);
        }

        // ===== QUELQUES BROUILLONS =====
        for ($i = 0; $i < 3; $i++) {
            $recette = new Recette();
            $recette->setTitre('Brouillon ' . ($i + 1));
            $recette->setNbPersonnes(2);
            $recette->setVisibilite(VisibiliteEnum::PRIVEE);
            $recette->setBrouillon(true);
            $recette->setAuteur($this->getReference(UtilisateurFixtures::USER_REFERENCE . ($i % self::NB_UTILISATEURS), Utilisateur::class));
            $manager->persist($recette);
        }

        $manager->flush();
    }
}