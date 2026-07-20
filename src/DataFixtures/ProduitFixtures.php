<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProduitFixtures extends Fixture
{
    public const PRODUIT_REFERENCE = 'produit_';

    public function load(ObjectManager $manager): void
    {
        $produits = [
            ['Bœuf haché', 'Viandes'], ['Escalope de porc', 'Viandes'], ['Blanc de poulet', 'Viandes'],
            ['Gigot d\'agneau', 'Viandes'], ['Filet de dinde', 'Viandes'],
            ['Pavé de saumon', 'Poissons'], ['Thon en boîte', 'Poissons'], ['Filet de cabillaud', 'Poissons'], ['Truite fumée', 'Poissons'],
            ['Tomates', 'Légumes'], ['Carottes', 'Légumes'], ['Courgettes', 'Légumes'], ['Épinards frais', 'Légumes'], ['Oignons', 'Légumes'], ['Poivrons', 'Légumes'],
            ['Pommes', 'Fruits'], ['Bananes', 'Fruits'], ['Citrons', 'Fruits'], ['Fraises', 'Fruits'],
            ['Lentilles vertes', 'Légumineuses'], ['Pois chiches', 'Légumineuses'], ['Haricots rouges', 'Légumineuses'],
            ['Riz basmati', 'Féculents & céréales'], ['Pâtes penne', 'Féculents & céréales'], ['Pommes de terre', 'Féculents & céréales'], ['Quinoa', 'Féculents & céréales'],
            ['Lait demi-écrémé', 'Produits laitiers et fromages'], ['Beurre doux', 'Produits laitiers et fromages'], ['Crème fraîche', 'Produits laitiers et fromages'], ['Emmental râpé', 'Produits laitiers et fromages'], ['Chèvre frais', 'Produits laitiers et fromages'],
            ['Œufs', 'Œufs'],
            ['Ail', 'Épices et aromates'], ['Persil frais', 'Épices et aromates'], ['Cumin en poudre', 'Épices et aromates'], ['Curry en poudre', 'Épices et aromates'],
            ['Huile d\'olive', 'Condiments et sauces'], ['Sauce soja', 'Condiments et sauces'], ['Moutarde', 'Condiments et sauces'],
        ];

        foreach ($produits as $i => [$nom, $categorie]) {
            $produit = new Produit();
            $produit->setNom($nom);
            $produit->setCategorie($categorie);
            $manager->persist($produit);
            $this->addReference(self::PRODUIT_REFERENCE . $i, $produit);
        }

        $manager->flush();
    }
}