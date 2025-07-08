<?php

// Détection automatique de l'environnement
// Ces variables sont lues depuis les variables d'environnement Docker Compose
// ou utilisent des valeurs par défaut si elles ne sont pas définies.
$servername = getenv('MYSQL_HOST') ?: 'localhost';
$username = getenv('MYSQL_USER') ?: 'snapcatuser';
$password = getenv('MYSQL_PASSWORD') ?: 'snapcatpass';
$dbname = getenv('MYSQL_DATABASE') ?: 'snapcat';

$conn = null; // Initialise la variable de connexion à null

try {
    // Crée la connexion PDO
    // Les options PDO::ATTR_ERRMODE et PDO::ERRMODE_EXCEPTION sont cruciales
    // pour que PDO lance des exceptions en cas d'erreur, ce qui permet de les capturer.
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Récupère les résultats sous forme de tableau associatif par défaut
        PDO::ATTR_EMULATE_PREPARES => false, // Désactive l'émulation des requêtes préparées pour une meilleure sécurité et performance
    ]);

    // Si la connexion réussit, il n'y a rien à faire ici.
    // Le script continuera.

} catch (PDOException $e) {
    // Si une exception PDO est lancée (erreur de connexion ou de requête)
    // Affiche un message d'erreur et arrête le script.
    // error_log est utilisé pour enregistrer l'erreur côté serveur,
    // et die() pour afficher un message à l'utilisateur (utile en développement).
    error_log("Erreur de connexion PDO dans bd.php : " . $e->getMessage());
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// À ce stade, si le script n'a pas été arrêté par die(),
// la variable $conn contient un objet PDO valide et connecté.
?>