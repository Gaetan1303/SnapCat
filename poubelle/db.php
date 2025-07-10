<?php
// Détection automatique de l'environnement
// Ces variables sont lues depuis les variables d'environnement Docker Compose
// ou utilisent des valeurs par défaut si elles ne sont pas définies.
// Détection automatique de l'environnement
// Ces variables sont lues depuis les variables d'environnement Docker Compose
// ou utilisent des valeurs par défaut si elles ne sont pas définies.
$servername =  '127.0.0.1';
$username = 'root';
$password =  'root';
$dbname =  'snapcatdb';
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
    // error_log est utilisé pour enregistrer l'erreur côté serveur.
    error_log("Erreur de connexion PDO dans bd.php : " . $e->getMessage());
    // AJOUT POUR LE DÉBOGAGE : Affiche le message d'erreur exact directement dans le navigateur
    echo "<h1>Erreur fatale de connexion à la base de données !</h1>";
    echo "<p>Veuillez vérifier votre configuration et la disponibilité de MySQL.</p>";
    echo "<p><strong>Détails de l'erreur :</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    die(); // Arrête l'exécution du script après avoir affiché l'erreur.
}

// À ce stade, si le script n'a pas été arrêté par die(),
// la variable $conn contient un objet PDO valide et connecté.
?>
