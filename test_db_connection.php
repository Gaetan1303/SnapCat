<?php
// test_db_connection.php
echo "Tentative de connexion à la base de données...<br>";

// Assure-toi que ces chemins sont corrects par rapport à test_db_connection.php
require_once 'db.php'; // Inclut ton fichier db.php

if ($conn instanceof PDO) {
    echo "<h2 style='color: green;'>SUCCÈS : Connexion à la base de données établie !</h2>";
    // Tu peux même faire un petit test de requête
    try {
        $stmt = $conn->query("SELECT 1");
        if ($stmt->fetchColumn()) {
            echo "Requête simple exécutée avec succès.<br>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>Avertissement : La connexion est OK, mais une requête simple a échoué : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<h2 style='color: red;'>ÉCHEC : La variable \$conn n'est pas un objet PDO.</h2>";
    // Les messages d'erreur de db.php devraient déjà s'afficher si l'exception est levée
}

echo "Fin du test de connexion.<br>";
?>