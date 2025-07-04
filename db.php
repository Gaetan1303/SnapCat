<?php
// Détection automatique de l'environnement
$servername = getenv('MYSQL_HOST') ?: 'localhost';

$username = getenv('MYSQL_USER') ?: 'snapcatuser';
$password = getenv('MYSQL_PASSWORD') ?: 'snapcatpass';
$dbname = getenv('MYSQL_DATABASE') ?: 'snapcat';

// Créer la connexion
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

// Vérifier la connexion
if (!$conn) {
    die("La connexion a échoué : " . $conn->errorInfo());
}
?>
