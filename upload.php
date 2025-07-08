<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

// Connexion à la base de données avec PDO
try {
    $conn = new PDO('mysql:host=localhost;dbname=nom_de_la_base_de_donnees;charset=utf8', 'utilisateur', 'motdepasse');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("La connexion a échoué : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifie que tous les champs existent
    $fields = ['nom','numero_puce','age','sexe','description','localisation','interets','caracteristiques','caption'];
    foreach ($fields as $f) {
        if (!isset($_POST[$f])) {
            die("Champ manquant : $f");
        }
    }

    $nom = $_POST['nom'];
    $numero_puce = $_POST['numero_puce'];
    $age = $_POST['age'];
    $sexe = $_POST['sexe'];
    $description = $_POST['description'];
    $localisation = $_POST['localisation'];
    $interets = $_POST['interets'];
    $caracteristiques = $_POST['caracteristiques'];
    $caption = $_POST['caption'];
    $photoPath = '';

    // Gestion de l'upload de la photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            $photoPath = 'uploads/' . uniqid('cat_', true) . '.' . $ext;
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                die("Erreur lors du déplacement du fichier uploadé.");
            }
        } else {
            die("Ce fichier n'est pas une image valide.");
        }
    } else {
        die("Erreur lors de l'upload de la photo.");
    }

    // Enregistrer les données dans la table 'chats' avec PDO
    try {
        $sql1 = "INSERT INTO chats (nom, numero_puce, age, sexe, photo, description, localisation, interets, caracteristiques) 
                 VALUES (:nom, :numero_puce, :age, :sexe, :photo, :description, :localisation, :interets, :caracteristiques)";
        $stmt1 = $conn->prepare($sql1);

        // Lier les paramètres
        $stmt1->bindParam(':nom', $nom);
        $stmt1->bindParam(':numero_puce', $numero_puce);
        $stmt1->bindParam(':age', $age);
        $stmt1->bindParam(':sexe', $sexe);
        $stmt1->bindParam(':photo', $photoPath);
        $stmt1->bindParam(':description', $description);
        $stmt1->bindParam(':localisation', $localisation);
        $stmt1->bindParam(':interets', $interets);
        $stmt1->bindParam(':caracteristiques', $caracteristiques);

        // Exécution de la requête
        $stmt1->execute();
        echo "Profil ajouté avec succès !<br>";
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement du profil : " . $e->getMessage());
    }

    // Enregistrer la photo et la légende dans la table 'snaps' avec PDO
    try {
        $sql2 = "INSERT INTO snaps (photo, caption) VALUES (:photo, :caption)";
        $stmt2 = $conn->prepare($sql2);

        // Lier les paramètres
        $stmt2->bindParam(':photo', $photoPath);
        $stmt2->bindParam(':caption', $caption);

        // Exécution de la requête
        $stmt2->execute();
        echo "Photo envoyée avec succès !";
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement de la photo : " . $e->getMessage());
    }

    // Fermer la connexion PDO
    $conn = null;
}
?>
