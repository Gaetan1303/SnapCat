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
}/*
// Affiche poster une photo
if (isset($_FILES['userfile'])) {
    echo '<p>Merci de poster vos photos !</p>';
    get_photos('Données/'); // Appel de la fonction pour récupérer les photos
    // Rediriger vers la page d'accueil
    header('Location: index.php');
    exit;
}
// Affiche poster une vidéo
if (isset($_FILES['videofile'])) {
    echo '<p>Merci de poster vos vidéos !</p>';
    get_browser(dir('Données/')); // Appel de la fonction pour récupérer les vidéos
    // Rediriger vers la page d'accueil
    header('Location: index.php');
    exit;
}
// Affiche poster une story
if (isset($_FILES['storyfile'])) {
    echo '<p>Merci de poster vos stories !</p>';
    get_browser('Données/'); // Appel de la fonction pour récupérer les stories
    // Rediriger vers la page d'accueil
    header('Location: index.php');
    exit;
}
?> */
?>