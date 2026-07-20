# Fixtures — Stock & Toque

## À propos de ce dossier

Les fixtures de ce dossier ont été générées avec l'aide de Claude (Anthropic) dans le cadre de mon
projet de fin de formation DWWM. L'assistant a rédigé le code PHP à ma demande, à partir des règles
métier et du modèle de données (MPD) que j'ai définis moi-même en amont du projet.

Mon rôle a été de :
- Définir la structure du MPD et les règles métier (régimes, allergies, ON DELETE, etc.)
- Décider du contenu à générer (nombre d'utilisateurs, mot de passe de test, simplification
  volontaire de certaines listes pour aller plus vite)
- Relire, vérifier et corriger le code (voir par exemple la correction d'un index d'équipement
  erroné dans `UtilisateurFixtures.php`)
- Valider chaque fichier avant de l'intégrer au projet

## Contenu des fixtures

| Fichier | Rôle | Dépend de |
|---|---|---|
| `ReferentielFixtures.php` | Tables de référence : régimes alimentaires, allergies (14 allergènes officiels + intolérances courantes), équipements, catégories alimentaires, ingrédients de référence (`ingredient_ref`), avec quelques exclusions régime/ingrédient pour tester la logique de suggestions | — |
| `UtilisateurFixtures.php` | 1 Super Admin, 2 Admins, 6 utilisateurs standard avec des profils variés (régimes, allergies, équipements et préférences de goût différents) | `ReferentielFixtures` |
| `ProduitFixtures.php` | Une trentaine de produits alimentaires génériques, utilisés dans le stock et les recettes | — |
| `RecetteFixtures.php` | 200 recettes publiques + quelques recettes privées/brouillons, avec leurs ingrédients, étapes et équipements | `UtilisateurFixtures`, `ProduitFixtures`, `ReferentielFixtures` |
| `StockFixtures.php` | Quelques produits en stock par utilisateur de test (5 à 10), avec DLC/DDM proches pour tester les futures alertes | `UtilisateurFixtures`, `ProduitFixtures` |
| `PlanningFixtures.php` | Un planning passé (recettes planifiées et "consommées", pour tester la notation J+1) et un planning en cours (mix recettes/plats libres) par utilisateur | `UtilisateurFixtures`, `RecetteFixtures` |

## Ajustements effectués après premier test

Après un premier chargement des fixtures, une vérification du nombre de lignes en base
(`SELECT COUNT(*)` sur `recette`, `utilisateur`, `produit`, `ingredient_ref`) a permis de
repérer deux petites incohérences entre les constantes utilisées dans le code et le nombre
réel d'éléments créés dans `ProduitFixtures.php` (39 produits, pas 37) et `ReferentielFixtures.php`
(40 ingrédients de référence, pas 39). Conséquence : quelques produits/ingrédients n'étaient
jamais piochés par les recettes ou les préférences de goût générées.

Corrigé dans `RecetteFixtures.php` (`NB_PRODUITS` : 37 → 39) et `UtilisateurFixtures.php`
(modulo des préférences de goût : 39 → 40), puis fixtures rechargées pour valider la correction.


## Mot de passe de test

Tous les comptes générés utilisent le même mot de passe : **`Test1234!`**

Ce choix a été fait volontairement pour simplifier les tests manuels et via Bruno (un seul mot de
passe à retenir, peu importe le compte utilisé). Il respecte les contraintes de validation définies
sur l'entité `Utilisateur` (8 caractères minimum, majuscule, minuscule, chiffre, caractère spécial),
même si les fixtures ne passent pas par le Validator Symfony.

⚠️ Ce mot de passe est utilisé uniquement en environnement de développement/démo, jamais en production.

## Charger les fixtures

```bash
symfony console doctrine:fixtures:load
```

Cette commande vide la base avant de la recharger. Une confirmation est demandée avant exécution.

## Statut

✅ Toutes les fixtures prévues pour l'Epic 0 sont en place et fonctionnelles (référentiel, utilisateurs, produits, recettes, stock, planning).