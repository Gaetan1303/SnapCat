<?php
$message = ''; // Variable pour stocker les messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'Données/'; // Répertoire où les fichiers seront stockés
    $uploadFile = $uploadDir . basename($_FILES['userfile']['name']);

    // Regarde si le fichier est une image
    $check = getimagesize($_FILES['userfile']['tmp_name']);
    if ($check === false) {
        $message = "Ce n'est pas une image.";
    }

    // Vérifie la taille du fichier (limite de 5 Mo)
    elseif ($_FILES['userfile']['size'] > 5000000) {
        $message = "Le fichier est trop volumineux. La taille maximale autorisée est 5 Mo.";
    }

    // Vérifie le type de fichier
    else {
        $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            $message = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
        }
        // Si tout est OK, on déplace le fichier dans le répertoire
        else {
            if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadFile)) {
                $message = "Le fichier a été téléchargé avec succès.";
            } else {
                $message = "Une erreur s'est produite lors du téléchargement du fichier.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="scripts.js">
    <title>Bienvenu Sur Instagram, le vrai et l'unique</title>
</head>

<body>

    <div class="container">
        <h1>Upload d'image</h1>

        <!-- Affichage du message PHP -->
        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'succès') !== false) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de téléchargement -->
        <form action="index.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="userfile">Choisir une image :</label>
                <input type="file" name="userfile" id="userfile" accept="image/*" required>
            </div>
            <input type="submit" value="Télécharger l'image">
        </form>
    </div>
    </head>

    <body>
        <h1>Bienvenu Sur Instagram, le vrai et l'unique</h1>

        <!-- Affichage du message (succès ou erreur) -->
        <?php if (!empty($message)): ?>
            <p style="color: <?php echo (strpos($message, 'succès') !== false) ? 'green' : 'red'; ?>;">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <!-- Formulaire d'upload -->
        <form action="index.php" method="post" enctype="multipart/form-data">
            <label for="userfile">Choisir un fichier image à télécharger :</label>
            <input type="file" name="userfile" id="userfile" required>
            <br><br>
            <input type="submit" value="Télécharger l'image">
        </form>
    </body>

</html>