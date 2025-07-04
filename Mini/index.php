<?php
// Répertoire des photos uploadées
$directory = 'Données/';

// Fonction pour lire les photos du dossier
function get_photos($dir) {
    $photos = [];
    // Ouvrir le dossier
    if ($handle = opendir($dir)) {
        // Lire le dossier
        while (false !== ($file = readdir($handle))) {
            // Ignorer les fichiers "." et ".."
            if ($file != "." && $file != "..") {
                $photos[] = $file;
            }
        }
        closedir($handle);
    }
    return $photos;
}

// Obtenir la liste des photos
$photos = get_photos($directory);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniInsta - Accueil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
        }
        .photo {
            width: 100%;
            max-width: 500px;
            margin: 10px 0;
        }
        img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>Bienvenue sur MiniInsta</h1>
    <p>Découvrez les photos publiées par les utilisateurs.</p>
    <div>
        <?php
        // Afficher chaque photo dans un élément HTML
        foreach ($photos as $photo) {
            echo '<div class="photo"><img src="' . $directory . $photo . '" alt="photo"></div>';
        }
        ?>
    </div>
</body>
</html>
