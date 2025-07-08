<?php
function getDbConnection(): PDO
{
    $host = 'db';
    $dbname = 'snapcatdb';
    $user = 'snapcatuser';
    $password = 'secret';

    try {
        $dsn = "pgsql:host=$host;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données dans db.php: " . $e->getMessage());
        die("Impossible de se connecter à la base de données. Veuillez réessayer plus tard.");
    }
}

?>