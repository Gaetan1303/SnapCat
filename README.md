Snapcat

Bienvenue sur Snapcat, la plateforme sociale dédiée aux amoureux des chats ! Partagez les profils uniques de vos compagnons félins, découvrez d'autres chats adorables et connectez-vous avec une communauté de passionnés.

Table des matières

    Description du Projet

    Fonctionnalités

    Prérequis

    Démarrage Rapide avec Docker

        Configuration de la Base de Données (MySQL)

        Lancement de l'Application

    Structure du Code

    Fonctionnement du Code

    Contribuer

    Licence

1. Description du Projet

Snapcat est une application web qui permet aux utilisateurs de créer et de gérer des profils pour leurs chats, de télécharger des photos, de fournir des détails comme l'âge, la race, la localisation et une description. Les utilisateurs peuvent également découvrir les profils d'autres chats dans un carrousel interactif, simuler des "j'aime" ou des "passer", et interagir avec la communauté.

2. Fonctionnalités

    Inscription et Connexion Utilisateur : Créez votre compte pour commencer.

    Gestion de Profil Utilisateur : Mettez à jour votre nom d'utilisateur, votre e-mail et votre mot de passe.

    Tableau de Bord Utilisateur : Un espace personnel pour voir et gérer tous les profils de chats que vous avez ajoutés.

    Ajout de Profil de Chat : Créez des profils détaillés pour vos chats avec nom, âge, sexe, race, localisation, description et une photo.

    Modification de Profil de Chat : Mettez à jour les informations et la photo de vos chats à tout moment.

    Suppression de Profil de Chat : Supprimez facilement les profils de chats.

    Carrousel de Découverte : Parcourez les profils d'autres chats avec des options "J'aime" et "Passer".

    Design Responsive : Une interface utilisateur agréable et adaptable à différentes tailles d'écran (grâce à Tailwind CSS).

3. Prérequis

Pour exécuter Snapcat, vous avez besoin des éléments suivants :

    Docker et Docker Compose (recommandé pour un environnement de développement facile).

    PHP 7.4+ (ou version supérieure, compatible avec les dernières versions).

    MySQL 8.0+ (ou version équivalente pour la base de données).

    Un serveur web (Apache ou Nginx) pour servir les fichiers PHP.

4. Démarrage Rapide avec Docker

Le moyen le plus simple de démarrer Snapcat est d'utiliser Docker Compose.

Configuration de la Base de Données (MySQL)

Avant de lancer l'application, vous devez configurer votre base de données MySQL.

    Créez un fichier docker-compose.yml à la racine de votre projet avec le contenu suivant :
    YAML

version: '3.8'

services:
  db:
    image: mysql:8.0
    container_name: snapcat_mysql
    environment:
      MYSQL_ROOT_PASSWORD: root_password # Changez ceci pour un mot de passe sécurisé en production
      MYSQL_DATABASE: snapcat_db        # Nom de votre base de données
      MYSQL_USER: snapcat_user          # Nom d'utilisateur de la base de données
      MYSQL_PASSWORD: snapcat_password  # Mot de passe de l'utilisateur de la base de données
    ports:
      - "3306:3306" # Mappe le port MySQL du conteneur au port 3306 de votre machine
    volumes:
      - db_data:/var/lib/mysql # Stockage persistant pour les données de la base de données
      - ./db/init.sql:/docker-entrypoint-initdb.d/init.sql # Exécute ce script au démarrage du conteneur
    healthcheck: # Vérification de l'état de la base de données
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      timeout: 20s
      retries: 10
    networks:
      - snapcat_network

  php:
    build:
      context: .
      dockerfile: Dockerfile_php
    container_name: snapcat_php
    volumes:
      - .:/var/www/html # Monte le répertoire de votre projet dans le conteneur Apache
    ports:
      - "80:80" # Mappe le port HTTP du conteneur au port 80 de votre machine
    depends_on:
      db:
        condition: service_healthy # Attend que la base de données soit saine
    networks:
      - snapcat_network

volumes:
  db_data:

networks:
  snapcat_network:
    driver: bridge

Créez un dossier db à la racine de votre projet.

Créez un fichier db/init.sql à l'intérieur du dossier db. Ce fichier sera exécuté automatiquement par Docker pour initialiser votre schéma de base de données.
SQL

-- db/init.sql

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS snapcat_db;

-- Sélection de la base de données
USE snapcat_db;

-- Table des utilisateurs (si elle n'existe pas déjà)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Stockez les hachages de mot de passe, JAMAIS les mots de passe en clair
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des chats
CREATE TABLE IF NOT EXISTS chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    age INT,
    sexe VARCHAR(10), -- 'Mâle', 'Femelle'
    race VARCHAR(100),
    localisation VARCHAR(255),
    description TEXT,
    photo VARCHAR(255), -- Chemin vers l'image du chat (ex: uploads/cats/chat_123.jpg)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Création de l'utilisateur de la base de données et attribution des privilèges (correspondant à docker-compose.yml)
CREATE USER 'snapcat_user'@'%' IDENTIFIED BY 'snapcat_password';
GRANT ALL PRIVILEGES ON snapcat_db.* TO 'snapcat_user'@'%';
FLUSH PRIVILEGES;

-- Données de test (optionnel)
-- INSERT INTO users (username, email, password_hash) VALUES ('testuser', '$2y$10$Q7eY/D/Qj/t0P8fK1qQ3ouZ7N5fN2dJ1yP4w5m6o7p8q9r0s1t2u3v4w5x6y7z8A9B', NOW()); -- Mot de passe: password
-- INSERT INTO chats (user_id, nom, age, sexe, race, localisation, description, photo) VALUES (1, 'Félix', 3, 'Mâle', 'Siamois', 'Paris', 'Un chat très joueur et affectueux.', 'uploads/cats/felix.jpg');

Créez un fichier Dockerfile_php à la racine de votre projet. Ce Dockerfile construira l'image PHP avec Apache.
Dockerfile

    # Dockerfile_php

    FROM php:8.1-apache

    # Installer les extensions PHP nécessaires
    # mysqli pour la connexion à MySQL, gd pour le traitement d'image (si utilisé)
    RUN docker-php-ext-install pdo pdo_mysql gd

    # Activer mod_rewrite (pour les URL "propres" si vous en avez besoin plus tard)
    RUN a2enmod rewrite

    # Configurer le répertoire de travail
    WORKDIR /var/www/html

    # Copier les fichiers de l'application (le volume monte déjà, mais c'est une bonne pratique)
    COPY . /var/www/html/

    # Exposer le port 80
    EXPOSE 80

    # Démarrer Apache en mode foreground
    CMD ["apache2-foreground"]

Lancement de l'Application

    Mettez à jour votre fichier bd.php pour qu'il utilise les informations de connexion Docker Compose. Le host sera le nom du service MySQL (db dans notre docker-compose.yml).
    PHP

<?php
// bd.php
$host = 'db';          // Nom du service MySQL dans docker-compose.yml
$dbname = 'snapcat_db';
$user = 'snapcat_user';
$password = 'snapcat_password';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    // Définit le mode d'erreur de PDO sur Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optionnel : Définit le mode de récupération par défaut des résultats
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Enregistre l'erreur dans les logs du serveur
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    // Affiche un message générique à l'utilisateur
    die("Impossible de se connecter à la base de données. Veuillez réessayer plus tard.");
}
?>

Créez un dossier uploads/cats à la racine de votre projet. C'est là que les photos des chats seront stockées.

Construisez et lancez les conteneurs Docker :
Ouvrez votre terminal à la racine de votre projet (là où se trouve docker-compose.yml) et exécutez :
Bash

docker-compose up --build -d

    --build : Construit les images Docker (nécessaire la première fois ou après des modifications du Dockerfile).

    -d : Démarre les conteneurs en arrière-plan (detached mode).

Accédez à l'application :
Une fois les conteneurs lancés (cela peut prendre quelques instants), ouvrez votre navigateur web et accédez à :

http://localhost/

Vous devriez voir la page d'accueil de Snapcat.

Pour arrêter les conteneurs :
Bash

docker-compose down

Pour arrêter et supprimer les données persistantes de la base de données :
Bash

    docker-compose down -v

5. Structure du Code

Le projet est organisé comme suit :

snapcat/
├── bd.php                  # Configuration de la connexion à la base de données
├── ChatModel.php           # Classe pour les opérations CRUD sur les chats
├── index.php               # Page d'accueil publique avec carrousel de découverte
├── login.php               # Formulaire et logique de connexion
├── register.php            # Formulaire et logique d'inscription
├── logout.php              # Script de déconnexion
├── main.php                # Tableau de bord utilisateur (gestion des chats)
├── add_cat.php             # Formulaire pour ajouter un nouveau chat
├── edit_cat.php            # Formulaire pour modifier un chat existant
├── delete_cat.php          # Script pour supprimer un chat
├── account_settings.php    # Formulaire pour modifier les informations du compte utilisateur
├── uploads/                # Dossier pour les fichiers téléchargés
│   └── cats/               #   Photos des chats
├── db/                     # Dossier pour les scripts d'initialisation de la base de données
│   └── init.sql            #   Script SQL pour créer les tables (utilisé par Docker)
├── Dockerfile_php          # Dockerfile pour l'image PHP/Apache
└── docker-compose.yml      # Configuration Docker Compose

6. Fonctionnement du Code

L'application suit une architecture basée sur un modèle de conception simple (proche du MVC, mais sans contrôleur dédié) pour séparer la logique de la présentation.

    bd.php : Ce fichier établit la connexion à la base de données MySQL via PDO (PHP Data Objects). Il est inclus par tous les scripts qui ont besoin d'interagir avec la base de données.

    ChatModel.php : C'est le "Modèle" pour la ressource Chat. Il contient toutes les méthodes qui interagissent directement avec la table chats de la base de données.

        __construct($conn) : Initialise le modèle avec la connexion PDO.

        getCatProfileById($chatId, $userId = null) : Récupère les détails d'un chat spécifique. Le $userId optionnel permet de vérifier que le chat appartient à l'utilisateur connecté.

        getAllCatsForCarousel($excludeUserId = null) : Récupère tous les chats pour la section de découverte (peut exclure les chats de l'utilisateur connecté).

        addCat(...) : Insère un nouveau profil de chat dans la base de données.

        updateCat(...) : Met à jour un profil de chat existant.

        deleteCat($chatId, $userId) : Supprime un profil de chat, en s'assurant que l'utilisateur est le propriétaire.

        getCatsByUserId($userId) : Récupère tous les chats appartenant à un utilisateur spécifique pour son tableau de bord.

    index.php : C'est la page d'accueil de l'application. Elle affiche :

        Une section "Hero" de bienvenue.

        La section "Découverte" des chats, utilisant ChatModel::getAllCatsForCarousel().

        Des messages de statut (succès/erreur) après les redirections (inscription, connexion, interaction avec le carrousel).

        Des liens conditionnels (Connexion/Inscription ou Dashboard/Déconnexion) basés sur l'état de la session de l'utilisateur.

        Des sections informatives (À Propos, Services, Équipe, Témoignages, Contact).

    login.php et register.php : Gèrent l'authentification des utilisateurs. register.php crée de nouveaux comptes (hachage des mots de passe avec password_hash()). login.php vérifie les identifiants et démarre la session utilisateur ($_SESSION['user_id'], $_SESSION['username']).

    logout.php : Détruit la session utilisateur et redirige vers la page d'accueil.

    main.php (Dashboard Utilisateur) : C'est le point d'entrée après une connexion réussie. Il :

        Affiche les chats de l'utilisateur connecté en utilisant ChatModel::getCatsByUserId().

        Propose des liens pour ajouter, modifier ou supprimer des profils de chats.

        Affiche les messages de statut spécifiques aux opérations sur les chats.

    add_cat.php : Contient le formulaire pour créer un nouveau profil de chat. Il gère l'upload des photos et utilise ChatModel::addCat().

    edit_cat.php : Pré-remplit un formulaire avec les données d'un chat existant (obtenues via ChatModel::getCatProfileById()). Il permet de modifier les informations du chat et sa photo, en utilisant ChatModel::updateCat(). Une vérification stricte est faite pour s'assurer que l'utilisateur est le propriétaire du chat.

    delete_cat.php : Un script qui gère la suppression d'un profil de chat. Il supprime le chat de la base de données via ChatModel::deleteCat() et supprime également le fichier photo du serveur. Une vérification est faite pour s'assurer que l'utilisateur est le propriétaire.

    account_settings.php : Permet à l'utilisateur de mettre à jour son nom d'utilisateur, son e-mail et/ou son mot de passe. Nécessite le mot de passe actuel pour des raisons de sécurité lors du changement de mot de passe.

    Gestion des photos (uploads/cats/) : Les images sont téléchargées dans ce répertoire. Le chemin est stocké en base de données. Lors des mises à jour ou suppressions, les anciens fichiers sont gérés pour éviter l'accumulation.

7. Contribuer

Les contributions sont les bienvenues ! Si vous souhaitez améliorer Snapcat, voici comment procéder :

    Faites un "fork" du dépôt.

    Créez une branche pour votre fonctionnalité (git checkout -b feature/AmazingFeature).

    Commitez vos changements (git commit -m 'Add some AmazingFeature').

    Poussez vers la branche (git push origin feature/AmazingFeature).

    Ouvrez une Pull Request.
