# SnapCat
Exercice
## Description

SnapCat est une application permettant de partager des photos de chats de façon simple et rapide.

## Fonctionnalités

- Prendre et partager des photos de chats
- Filtrer et rechercher des photos
- Aimer et commenter les photos

## Installation

```bash
git clone https://github.com/votre-utilisateur/SnapCat.git
cd SnapCat
npm install
npm start
```

## Utilisation

1. Lancez l'application.
2. Créez un compte ou connectez-vous.
3. Commencez à partager vos photos de chats préférées !

## Contribuer

Les contributions sont les bienvenues ! Veuillez ouvrir une issue ou une pull request.



## Projet MiniInsta
Objectif

Développer une application web simple permettant aux utilisateurs de publier et de consulter des photos. Toutes les photos sont visibles par l'ensemble des utilisateurs.
Pré-requis

    Serveur PHP : lancer avec php -S localhost:8000
    Formulaire HTML
        multipart/form-data
        Utilisation de isset()
        Accès à $_POST
        Gestion des fichiers avec move_uploaded_file() et $_FILES
    Redirection de page
    Lecture de dossier via readdir()
    Manipulation de tableaux PHP
    Boucle foreach dans un template HTML
    Clean Code : création de fonctions
        pour encapsuler la logique de lecture de dossier

## Synopsis

L'application est un mini Instagram développé en PHP. Les utilisateurs peuvent publier des photos, qui sont ensuite affichées les unes sous les autres pour tous les visiteurs.

L'application adopte une approche mobile-first, optimisée pour une utilisation sur téléphone mobile, tout en restant accessible sur ordinateur. La page d'accueil présente l'ensemble des photos publiées par les utilisateurs.

    Chaque photo est affichée seule, sans informations supplémentaires, afin de conserver la simplicité du projet, en l'absence de base de données SQL.

## Contraintes

Les fichiers uploadés doivent respecter le format de nommage suivant : date-auteur-filename.extension.
Exemple : 20250601144419-pierre-chat.png pour une photo de chat postée par Pierre le 01/06/2025 à 14h44m19s.

    Cela permet de démontrer la capacité à traiter à la fois les champs textuels et binaires d'un formulaire HTML.

Cahier des charges
Tâches 	Description 	Contraintes
Page d'accueil 	Afficher les photos publiées par les utilisateurs 	Utiliser readdir() pour lire le dossier des photos et une boucle foreach pour l'affichage
Upload de photos 	Permettre aux utilisateurs de publier des photos via un formulaire comportant deux champs : auteur et fichier 	Utiliser un formulaire HTML de type multipart/form-data pour envoyer les photos à un script serveur
Convention de nommage des fichiers uploadés 	Les fichiers doivent être nommés selon le format date-auteur-filename.extension 	Exemple : 20250601144419-pierre-chat.png pour une photo de chat postée par Pierre le 01/06/2025 à 14h44m19s# SnapCat Application

## SnapCat est une application web qui permet aux utilisateurs de partager leurs photos de chats préférées. Ce README fournit une vue d'ensemble du projet, les instructions d'installation et les détails d'utilisation.

## Structure du projet

```
SnapCat
├── index.html        # Structure HTML de l'application SnapCat
├── Dockerfile        # Instructions pour construire une image Docker de l'application
├── .dockerignore     # Fichiers et dossiers à ignorer lors de la création de l'image Docker
└── README.md         # Documentation de l'application SnapCat
```

## Instructions d'installation

1. **Cloner le dépôt**
    ```bash
    git clone <repository-url>
    cd SnapCat
    ```

2. **Construire l'image Docker**
    Assurez-vous que Docker est installé sur votre machine. Ensuite, exécutez la commande suivante pour construire l'image Docker :
    ```bash
    docker build -t snapcat .
    ```

3. **Lancer le conteneur Docker**
    Après avoir construit l'image, lancez le conteneur avec :
    ```bash
    docker run -p 8080:80 snapcat
    ```

4. **Accéder à l'application**
    Ouvrez votre navigateur et allez sur `http://localhost:8080` pour accéder à l'application SnapCat.

## Utilisation

- Utilisez le formulaire pour téléverser une photo de votre chat.
- Ajoutez une légende à votre photo.
- Cliquez sur "Envoyer" pour soumettre votre photo.
- La photo envoyée s'affichera dans la section d'aperçu.
