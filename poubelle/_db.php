<?php
// db.php - Configuration et connexion à la base de données

// Paramètres de connexion à la base de données
// NOTE : En environnement Docker Compose, 'db' est le nom du service MySQL.
$servername = 'db'; // NOM DU SERVICE MYSQL DANS DOCKER-COMPOSE.YML
$username = 'root'; // Ton nom d'utilisateur MySQL
$password = 'root'; // Ton mot de passe MySQL (défini dans docker-compose.yml)
$dbname = 'snapcatdb'; // Le nom de ta base de données

$conn = null; // Initialise la variable de connexion

try {
    // Tente d'établir une nouvelle connexion PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Active le mode exception pour les erreurs
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Définit le mode de récupération par défaut en tableau associatif
        PDO::ATTR_EMULATE_PREPARES => false,                // Désactive l'émulation des requêtes préparées pour une meilleure sécurité et performance
    ]);

} catch (PDOException $e) {
    // En cas d'échec de connexion, journalise l'erreur et affiche un message générique.
    error_log("Erreur de connexion PDO dans db.php : " . $e->getMessage());
    // Arrête l'exécution du script car la connexion est essentielle
    die("<h1>Erreur fatale de connexion à la base de données !</h1><p>Veuillez vérifier la disponibilité du service MySQL et les identifiants de connexion.</p>");
}
?>