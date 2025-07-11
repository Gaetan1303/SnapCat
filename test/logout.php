<?php
// logout.php - Gère la déconnexion de l'utilisateur

session_start(); // Démarre la session PHP pour pouvoir y accéder et la détruire.

// Supprime toutes les variables de session.
// C'est une bonne pratique pour s'assurer qu'aucune donnée de session ne persiste.
session_unset();

// Détruit la session.
// Cela efface complètement toutes les données de session stockées côté serveur
// et invalide l'ID de session sur le navigateur de l'utilisateur.
session_destroy();

// Redirige l'utilisateur vers la page de connexion après la déconnexion.
// Assure-toi que 'login.php' est le bon chemin vers ta page de connexion.
header('Location: login.php');

exit; // Très important : arrête l'exécution du script après la redirection.
?>