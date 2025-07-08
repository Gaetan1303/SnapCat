## Exécution avec Docker

Ce projet fournit une configuration Docker complète pour faciliter le déploiement et l'exécution de l'application SnapCat dans un environnement isolé.

### Prérequis spécifiques
- **Image de base** : PHP 8.2 avec Apache
- **Extensions PHP** : `gd`, `pdo_mysql`, `mysqli` (installées automatiquement via le Dockerfile)
- **Aucun volume externe requis** : le dossier `uploads` est géré à l'intérieur du conteneur
- **Port exposé** : `8080` (redirigé vers le port 80 du conteneur Apache)

### Instructions de démarrage avec Docker Compose

1. **Construire et lancer l'application**
    ```bash
    docker compose up --build
    ```
    Cela construira l'image Docker et démarrera le service `php-app` défini dans `compose.yml`.

2. **Accéder à l'application**
    Ouvrez votre navigateur à l'adresse :
    ```
    http://localhost:8080
    ```

### Configuration spécifique
- **Réseau Docker** : le service utilise le réseau `snapcat-net` (défini dans `compose.yml`)
- **Utilisateur non-root** : l'application s'exécute sous l'utilisateur sécurisé `snapcatuser` à l'intérieur du conteneur
- **Aucune variable d'environnement obligatoire** : le fichier `.env` est optionnel et non requis par défaut

Pour toute personnalisation avancée (ex : connexion à une base de données externe), adaptez le fichier `compose.yml` ou le Dockerfile selon vos besoins.
