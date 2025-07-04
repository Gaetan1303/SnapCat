<?php
require 'db.php'; // Assurez-vous que ce fichier contient la connexion à la base de données
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo_de_profil']) && $_FILES['photo_de_profil']['error'] == 0) {
    // Récupérer l'ID de l'utilisateur (par exemple, depuis la session)
    $user_id = $_SESSION['user_id'];
    
    // Gérer l'upload
    $ext = pathinfo($_FILES['photo_de_profil']['name'], PATHINFO_EXTENSION);
    $photo_path = 'uploads/profils/' . uniqid() . '.' . $ext;

    // Vérifier si c'est une image valide
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        move_uploaded_file($_FILES['photo_de_profil']['tmp_name'], $photo_path);
        
        // Mettre à jour la base de données
        $sql = "UPDATE users SET photo_de_profil = :photo WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':photo' => $photo_path,
            ':id' => $user_id
        ]);

        echo "Photo de profil mise à jour avec succès !";
    } else {
        echo "Ce fichier n'est pas une image valide.";
    }
}
