<?php

namespace App\DataFixtures;

use App\Entity\Preferences;
use App\Entity\PreferencesAllergie;
use App\Entity\PreferencesEquipement;
use App\Entity\PreferencesRegime;
use App\Entity\PreferenceGout;
use App\Entity\Utilisateur;
use App\Enum\JourSemaineEnum;
use App\Enum\SmileyEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * IMPORTANT — mot de passe de test unique pour tous les comptes générés : "Test1234!"
 * Choix assumé pour simplifier les tests manuels et via Bruno (un seul mot de passe à retenir,
 * peu importe le compte utilisé). Ce mot de passe respecte volontairement les contraintes de
 * l'application (8 caractères min, majuscule, minuscule, chiffre, caractère spécial) afin de
 * rester cohérent avec les règles de validation même si les fixtures ne passent pas par le
 * Validator. Uniquement utilisé en environnement de développement/démo, jamais en production.
 */
class UtilisateurFixtures extends Fixture implements DependentFixtureInterface
{
    public const MOT_DE_PASSE_TEST = 'Test1234!';

    public const SUPER_ADMIN_REFERENCE = 'super_admin';
    public const ADMIN_REFERENCE = 'admin_';
    public const USER_REFERENCE = 'user_';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function getDependencies(): array
    {
        return [ReferentielFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // ===== SUPER ADMIN =====
        $superAdmin = $this->creerUtilisateur($manager, 'Roxane', 'Admin', 'superadmin@stocketoque.fr', ['ROLE_SUPER_ADMIN']);
        $this->addReference(self::SUPER_ADMIN_REFERENCE, $superAdmin);

        // ===== 2 ADMINS =====
        $admins = [
            ['Camille', 'Moderateur', 'admin1@stocketoque.fr'],
            ['Julien', 'Moderateur', 'admin2@stocketoque.fr'],
        ];
        foreach ($admins as $i => [$prenom, $nom, $email]) {
            $admin = $this->creerUtilisateur($manager, $prenom, $nom, $email, ['ROLE_ADMIN']);
            $this->addReference(self::ADMIN_REFERENCE . $i, $admin);
        }

        // ===== 6 UTILISATEURS STANDARD (profils variés) =====
        $utilisateursData = [
            ['Sacha', 'Smith', 'sacha.smith@test.fr', 4, 400, JourSemaineEnum::SAMEDI, [0], [4], [0, 2]], // Végétarien, sans allergie particulière ici, four+plaques
            ['Romain', 'Ketchum', 'romain.ketchum@test.fr', 1, 150, JourSemaineEnum::DIMANCHE, [], [], [1, 10]], // pas de régime, micro-ondes+airfryer
            ['Aurore', 'Michel', 'aurore.michel@test.fr', 2, 250, JourSemaineEnum::MERCREDI, [4, 5], [0, 10], [0, 5, 6]], // sans gluten+sans lactose, gluten+lait allergie
            ['Karim', 'Bensaid', 'karim.bensaid@test.fr', 3, 300, JourSemaineEnum::VENDREDI, [9], [], [0, 2, 4]], // Halal
            ['Lea', 'Dupont', 'lea.dupont@test.fr', 2, 200, null, [1], [10], [5, 7]], // Végétalien, allergie lait
            ['Hugo', 'Lefevre', 'hugo.lefevre@test.fr', 5, 500, JourSemaineEnum::SAMEDI, [], [6], [2, 3, 13]], // pas de régime, sportif
        ];

        foreach ($utilisateursData as $i => [$prenom, $nom, $email, $nbPersonnes, $budget, $jourCourses, $regimesIdx, $allergiesIdx, $equipementsIdx]) {
            $user = $this->creerUtilisateur($manager, $prenom, $nom, $email, ['ROLE_USER']);
            $user->setQuestionnaireComplete(true);
            $this->addReference(self::USER_REFERENCE . $i, $user);

            $preferences = new Preferences();
            $preferences->setUtilisateur($user);
            $preferences->setNbPersonnes($nbPersonnes);
            $preferences->setBudgetMensuel($budget);
            $preferences->setJourCourses($jourCourses);
            $manager->persist($preferences);

            foreach ($regimesIdx as $idx) {
                $pr = new PreferencesRegime();
                $pr->setPreferences($preferences);
                $pr->setRegime($this->getReference(ReferentielFixtures::REGIME_REFERENCE . $idx, \App\Entity\RegimeAlimentaire::class));
                $manager->persist($pr);
            }

            foreach ($allergiesIdx as $idx) {
                $pa = new PreferencesAllergie();
                $pa->setPreferences($preferences);
                $pa->setAllergie($this->getReference(ReferentielFixtures::ALLERGIE_REFERENCE . $idx, \App\Entity\Allergie::class));
                $manager->persist($pa);
            }

            foreach ($equipementsIdx as $idx) {
                $pe = new PreferencesEquipement();
                $pe->setPreferences($preferences);
                $pe->setEquipement($this->getReference(ReferentielFixtures::EQUIPEMENT_REFERENCE . $idx, \App\Entity\Equipement::class));
                $manager->persist($pe);
            }

            // Quelques préférences de goût (smileys) sur 3 ingrédients au hasard par utilisateur
            $smileys = [SmileyEnum::AIME, SmileyEnum::NEUTRE, SmileyEnum::NAIME_PAS];
            for ($j = 0; $j < 3; $j++) {
                $ingredientIdx = ($i * 3 + $j) % 40; // on tourne dans les 40 ingrédients créés
                $preferenceGout = new PreferenceGout();
                $preferenceGout->setUtilisateur($user);
                $preferenceGout->setIngredientRef($this->getReference(ReferentielFixtures::INGREDIENT_REFERENCE . $ingredientIdx, \App\Entity\IngredientRef::class));
                $preferenceGout->setSmiley($smileys[$j]);
                $manager->persist($preferenceGout);
            }
        }

        $manager->flush();
    }

    private function creerUtilisateur(ObjectManager $manager, string $prenom, string $nom, string $email, array $roles): Utilisateur
    {
        $user = new Utilisateur();
        $user->setPrenom($prenom);
        $user->setNom($nom);
        $user->setEmail($email);
        $user->setRole($roles);
        $user->setMotDePasse($this->passwordHasher->hashPassword($user, self::MOT_DE_PASSE_TEST));
        $manager->persist($user);

        return $user;
    }
}