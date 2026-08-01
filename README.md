# Stock & Toque

Application web de gestion alimentaire et culinaire, réalisée dans le cadre d'un projet de fin de formation développeur web et web mobile.

Stock & Toque permet de gérer son stock de produits alimentaires, planifier ses repas de la semaine, organiser sa liste de courses, et consulter ou partager des recettes de cuisine.

## Stack technique

- **Backend** : Symfony 7 (API REST)
- **Frontend** : React + Vite (JavaScript)
- **Base de données relationnelle** : MySQL (Doctrine ORM)
- **Base de données non relationnelle** : MongoDB (Doctrine ODM, cache OpenFoodFacts)
- **Authentification** : JWT via cookies httpOnly
- **Conteneurisation** : Docker Compose (MySQL, MongoDB, Mailpit)

## Prérequis

- PHP 8.5
- Composer
- Node.js 22+ / npm
- Docker et Docker Compose
- Symfony CLI

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/RoxaneL2906/Stock-et-Toque.git
```

### 2. Backend (Symfony)

```bash
composer install
```

Copier le fichier `.env` en `.env.local` et compléter les variables (base de données, mailer, CORS...).

Lancer les conteneurs Docker (MySQL, MongoDB, Mailpit) :

```bash
docker compose up -d
```

Générer les clés JWT (si non présentes) :

```bash
symfony console lexik:jwt:generate-keypair
```

Exécuter les migrations :

```bash
symfony console doctrine:migrations:migrate
```

Charger les fixtures (données de démonstration) :

```bash
symfony console doctrine:fixtures:load
```

Lancer le serveur Symfony :

```bash
symfony serve
```

L'API est accessible sur `https://localhost:8000`.

### 3. Frontend (React)

```bash
cd front
npm install
```

Créer un fichier `.env` dans `front/` avec :

```
VITE_API_URL=https://localhost:8000/api
```

Lancer le serveur de développement :

```bash
npm run dev
```

L'application est accessible sur `http://localhost:5173`.

## Compte de démonstration

Un compte de test est disponible après le chargement des fixtures :

- Email : `sacha.smith@test.fr`
- Mot de passe : `Test1234!`

## Fonctionnalités principales

- Gestion du stock de produits (ajout manuel ou via recherche OpenFoodFacts, alertes de péremption)
- Liste de courses (ajout manuel ou automatique depuis une recette)
- Recettes privées et publiques (création, modification, favoris, commentaires)
- Planning de repas hebdomadaire
- Gestion du profil utilisateur

## Structure du projet

stock-et-toque/
- src/                (Backend Symfony)
  - Controller/
  - Service/
  - Entity/
  - Repository/
  - Enum/
- front/               (Frontend React)
  - src/
    - components/
    - screens/
    - services/
    - utils/
- migrations/