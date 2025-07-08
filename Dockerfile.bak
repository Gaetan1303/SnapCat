# Utilise l'image de base PHP 8.2 avec Apache
FROM php:8.2-apache

# Met à jour la liste des paquets et installe les dépendances nécessaires pour GD (images)
# et l'extension PDO MySQL.
# --no-install-recommends réduit la taille de l'image en n'installant pas les paquets recommandés.
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Installe les extensions PHP :
# - gd: Pour la manipulation d'images (utile si vous traitez les photos de profil)
# - pdo_mysql: L'extension PDO pour MySQL, nécessaire pour votre connexion bd.php
# - mysqli: Gardé au cas où vous auriez besoin de l'utiliser à l'avenir, mais pdo_mysql est la clé pour votre configuration actuelle.
RUN docker-php-ext-install gd pdo_mysql mysqli

# Active le module Apache 'rewrite' pour les URL réécrites (utile pour les frameworks MVC par exemple)
RUN a2enmod rewrite

# Copie tous les fichiers de votre répertoire local dans le répertoire web d'Apache
COPY . /var/www/html/

# Définit les permissions pour le répertoire web d'Apache.
# C'est crucial pour que Apache (qui tourne sous l'utilisateur www-data) puisse lire les fichiers.
# chmod -R 755 : Donne les droits de lecture et d'exécution pour tous, écriture pour le propriétaire.
# chown -R www-data:www-data : Change le propriétaire et le groupe du répertoire et de son contenu à www-data.
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Crée le répertoire 'uploads' et définit les permissions pour que Apache (www-data) puisse y écrire.
# C'est crucial pour le téléchargement des photos de profil.
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads

# Expose le port 80 pour que le serveur web soit accessible
EXPOSE 80
