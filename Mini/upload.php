<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'Données/'; // Répertoire où les photos seront stockées
    $uploadFile = $uploadDir . basename($_FILES['userfile']['name']);

    // Regarde si la photo est correcte
    $check = getimagesize($_FILES['userfile']['tmp_name']);
    if ($check === false) {
        die("ce n'est pas une image.");
    }

    // Regarde la taille du fichier (limite de 5 Mo)
    if ($_FILES['userfile']['size'] > 5000000) {
        die("Le fichier est trop volumineux.");
    }

    // Autoriser certains formats de fichiers
    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedTypes)) {
        die("Only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Déplace le fichier téléchargé vers le répertoire désigné
    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadFile)) {
        echo "Le fichier est valide et a été téléchargé avec succès.\n";
    } else {
        echo "Possible attaque de téléchargement de fichier !\n";
    }
}
?>