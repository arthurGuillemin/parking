# 🅿️ Parking - Guide d'Installation

Application de gestion de parkings partagés développée en PHP avec une architecture Clean Architecture.

---

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir installé :

| Outil | Version requise |
|-------|-----------------|
| **PHP** | >= 8.2 |
| **Composer** | Dernière version |
| **MySQL/MariaDB** | >= 5.7 |
| **Extensions PHP** | pdo, pdo_mysql, xdebug (optionnel) |

---

## 🚀 Installation

### 1. Extraire le projet

Décompressez l'archive ZIP dans le dossier de votre choix (ex: C:\wamp64\www\parking ou /var/www/parking).

### 2. Installer les dépendances PHP

Ouvrez un terminal dans le dossier du projet et exécutez :

    composer install

### 3. Configurer l'environnement

Copiez le fichier .env.sample vers .env :

Windows (PowerShell) :

    Copy-Item .env.sample .env

Linux/Mac :

    cp .env.sample .env

Éditez le fichier .env avec vos paramètres de base de données :

    DB_HOST=localhost
    DB_PORT=3306
    DB_NAME=parking
    DB_USER=root
    DB_PASSWORD=votre_mot_de_passe
    JWT_SECRET_KEY=votre_cle_secrete_unique
    STORAGE_DRIVER=sql

> ⚠️ Important : Changez impérativement la valeur de JWT_SECRET_KEY par une clé secrète unique et sécurisée.

### 4. Créer la base de données

#### Option A : Ligne de commande

Connectez-vous à MySQL et créez la base de données :

    CREATE DATABASE parking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

Puis importez le schéma :

    mysql -u root -p parking < config/schema.sql

#### Option B : phpMyAdmin

1. Créez une nouvelle base de données nommée parking
2. Sélectionnez la base de données
3. Allez dans l'onglet Importer
4. Sélectionnez le fichier config/schema.sql
5. Cliquez sur Exécuter

---

## ▶️ Exécution

### Serveur de développement PHP intégré

    php -S localhost:8080 -t public

L'application sera accessible à l'adresse : http://localhost:8080

### Avec WAMP

1. Placez le projet dans C:\wamp64\www\parking
2. Configurez un VirtualHost pointant vers le dossier public/
3. Accédez à http://localhost/parking/public

### Avec XAMPP

1. Placez le projet dans C:\xampp\htdocs\parking
2. Accédez à http://localhost/parking/public

---

## 🧪 Tests

### Exécuter tous les tests

    ./vendor/bin/phpunit

### Exécuter uniquement les tests Unitaire

    ./vendor/bin/phpunit tests/Unit

### Exécuter uniquement les tests Fonctionnel

    ./vendor/bin/phpunit tests/Functional

pour avoir le coverage dans le Terminal ajouté :

    ./vendor/bin/phpunit tests/Functional --coverage-text

### Générer un rapport de couverture

    ./vendor/bin/phpunit --coverage-html coverage

Le rapport sera disponible dans le dossier coverage/.

---

## 🐳 Docker (Optionnel)

### Construire l'image

    docker build -t parking-app .

### Lancer le conteneur

    docker run -p 8080:8000 -e PORT=8000 parking-app

---

## 📁 Structure du projet

    parking/
    ├── config/             # Configuration (routes, schéma SQL)
    │   ├── routes.php      # Définition des routes
    │   └── schema.sql      # Schéma de la base de données
    ├── public/             # Point d'entrée web
    │   └── index.php       # Front controller
    ├── src/                # Code source (Clean Architecture)
    │   ├── Application/    # Cas d'utilisation (Use Cases)
    │   ├── Domain/         # Entités et interfaces du domaine
    │   └── Infrastructure/ # Implémentations (DB, Repositories)
    ├── templates/          # Vues HTML (PHP)
    ├── tests/              # Tests unitaires et fonctionnels
    ├── .env.sample         # Exemple de configuration
    ├── composer.json       # Dépendances PHP
    └── dockerfile          # Configuration Docker

---
Une démo est disponible sur cet url : https://parking-hj0w.onrender.com/

## 👥 Auteurs

- Antoine TU - @atu0601
- Arthur Guillemin - @arthurGuillemin
- Amaury SANCHEZ - @Amaury057
- PILLAH Niali Henri Guy-Harvyn - @Harvyn-10

---
