<?php

namespace App\DataFixtures;

use App\Entity\Stock;
use App\Entity\Utilisateur;
use App\Enum\EmplacementEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class StockFixtures extends Fixture implements DependentFixtureInterface
{
    private const NB_PRODUITS = 39;
    private const NB_UTILISATEURS = 6;

    public function getDependencies(): array
    {
        return [UtilisateurFixtures::class, ProduitFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $emplacements = EmplacementEnum::cases();

        // Chaque utilisateur standard a entre 5 et 10 produits en stock
        for ($u = 0; $u < self::NB_UTILISATEURS; $u++) {
            $utilisateur = $this->getReference(UtilisateurFixtures::USER_REFERENCE . $u, Utilisateur::class);
            $nbProduitsStock = $faker->numberBetween(5, 10);
            $indicesProduits = $faker->randomElements(range(0, self::NB_PRODUITS - 1), $nbProduitsStock);

            foreach ($indicesProduits as $indexProduit) {
                $stock = new Stock();
                $stock->setUtilisateur($utilisateur);
                $stock->setProduit($this->getReference(ProduitFixtures::PRODUIT_REFERENCE . $indexProduit, \App\Entity\Produit::class));
                $stock->setQuantite($faker->numberBetween(1, 5));
                $stock->setEmplacement($faker->randomElement($emplacements));

                // Certains produits ont une DLC ou DDM (proche pour tester les alertes)
                if ($faker->boolean(60)) {
                    $stock->setDlc($faker->dateTimeBetween('now', '+10 days'));
                }
                if ($faker->boolean(40)) {
                    $stock->setDdm($faker->dateTimeBetween('+2 days', '+30 days'));
                }

                $manager->persist($stock);
            }
        }

        $manager->flush();
    }
}