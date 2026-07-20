<?php

namespace App\DataFixtures;

use App\Entity\Creneau;
use App\Entity\Planning;
use App\Entity\Recette;
use App\Entity\Utilisateur;
use App\Enum\JourSemaineEnum;
use App\Enum\MomentEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PlanningFixtures extends Fixture implements DependentFixtureInterface
{
    private const NB_UTILISATEURS = 6;
    private const NB_RECETTES_PUBLIQUES = 200;

    public function getDependencies(): array
    {
        return [UtilisateurFixtures::class, RecetteFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $jours = JourSemaineEnum::cases();
        $moments = MomentEnum::cases();

        for ($u = 0; $u < self::NB_UTILISATEURS; $u++) {
            $utilisateur = $this->getReference(UtilisateurFixtures::USER_REFERENCE . $u, Utilisateur::class);

            // ===== Planning de la semaine PASSÉE (pour tester la notation J+1) =====
            $planningPasse = new Planning();
            $planningPasse->setUtilisateur($utilisateur);
            $planningPasse->setSemaineDebut($faker->dateTimeBetween('-14 days', '-8 days'));
            $manager->persist($planningPasse);

            // 3 créneaux avec recette de l'app (planifiés dans le passé = notables)
            for ($c = 0; $c < 3; $c++) {
                $creneau = new Creneau();
                $creneau->setPlanning($planningPasse);
                $creneau->setJour($faker->randomElement($jours));
                $creneau->setMoment($faker->randomElement($moments));
                $recetteIdx = $faker->numberBetween(0, self::NB_RECETTES_PUBLIQUES - 1);
                $creneau->setRecette($this->getReference(RecetteFixtures::RECETTE_PUBLIQUE_REFERENCE . $recetteIdx, Recette::class));
                $creneau->setNotificationEnvoyee(false);
                $manager->persist($creneau);
            }

            // ===== Planning de la semaine EN COURS =====
            $planningActuel = new Planning();
            $planningActuel->setUtilisateur($utilisateur);
            $planningActuel->setSemaineDebut($faker->dateTimeBetween('-2 days', 'now'));
            $manager->persist($planningActuel);

            for ($c = 0; $c < 4; $c++) {
                $creneau = new Creneau();
                $creneau->setPlanning($planningActuel);
                $creneau->setJour($faker->randomElement($jours));
                $creneau->setMoment($faker->randomElement($moments));

                // Alterne entre recette de l'app et plat libre
                if ($faker->boolean(70)) {
                    $recetteIdx = $faker->numberBetween(0, self::NB_RECETTES_PUBLIQUES - 1);
                    $creneau->setRecette($this->getReference(RecetteFixtures::RECETTE_PUBLIQUE_REFERENCE . $recetteIdx, Recette::class));
                } else {
                    $creneau->setPlatLibre($faker->randomElement(['Restes du frigo', 'Livraison', 'Resto entre amis', 'Sandwich rapide']));
                }

                $manager->persist($creneau);
            }
        }

        $manager->flush();
    }
}