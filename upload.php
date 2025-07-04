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

if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
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

    // Enregistrer les données dans la table chats
    $stmt = $conn->prepare("INSERT INTO chats (nom, numero_puce, age, sexe, photo, description, localisation, interets, caracteristiques) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissssss", $nom, $numero_puce, $age, $sexe, $photoPath, $description, $localisation, $interets, $caracteristiques);

    if ($stmt->execute()) {
        echo "Profil ajouté avec succès !<br>";
    } else {
        echo "Erreur lors de l'enregistrement du profil : " . $stmt->error;
    }
    $stmt->close();

    // Enregistrer la photo et la légende dans la table snaps
    $stmt2 = $conn->prepare("INSERT INTO snaps (photo, caption) VALUES (?, ?)");
    $stmt2->bind_param("ss", $photoPath, $caption);

    if ($stmt2->execute()) {
        echo "Photo envoyée avec succès !";
    } else {
        echo "Erreur lors de l'enregistrement de la photo : " . $stmt2->error;
    }
    $stmt2->close();

    $conn->close();
}
?>
