<?php
// Détection automatique de l'environnement
// Ces variables sont lues depuis les variables d'environnement Docker Compose
// ou utilisent des valeurs par défaut si elles ne sont pas définies.

// ****** MODIFICATION ICI ******
// Utilisez 'db' car c'est le nom du service de la base de données dans docker-compose.yml

// ****************************

$username = 'root';
$password =  'root'; // Correspond à MYSQL_ROOT_PASSWORD dans docker-compose.yml
$dbname =  'snapcatdb'; // Correspond à MYSQL_DATABASE dans docker-compose.yml
$servername = '127.0.0.1';
$conn = null; // Initialise la variable de connexion à null
// echo "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

} catch (PDOException $e) {
    error_log("Erreur de connexion PDO dans bd.php : " . $e->getMessage());
    echo "<h1>Erreur fatale de connexion à la base de données !</h1>";
    echo "<p>Veuillez vérifier votre configuration et la disponibilité de MySQL.</p>";
    echo "<p><strong>Détails de l'erreur :</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    die();
}
?>