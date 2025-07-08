<?php
// --- DÉBOGAGE TEMPORAIRE : À RETIRER EN PRODUCTION ---
ini_set('display_errors', 1); // Affiche les erreurs directement dans le navigateur
error_reporting(E_ALL);      // Rapporte tous les types d'erreurs PHP
// -----------------------------------------------------
require_once ('db.php');
session_start(); // Démarre la session (utile si vous voulez rediriger après inscription)

// Inclure le fichier de connexion à la base de données

// Vérifie si l'objet de connexion PDO est valide après l'inclusion de bd.php
if (!isset($conn) || !$conn instanceof PDO) {
    // Si la connexion n'est pas établie ou n'est pas un objet PDO, affiche un message d'erreur fatal.
    // Cela aide à diagnostiquer les problèmes de connexion à la base de données.
    die("Erreur fatale : La connexion à la base de données n'a pas pu être établie via bd.php.");
}

$message = ''; // Variable pour stocker les messages d'erreur ou de succès
$message_type = ''; // 'success' ou 'error'

// Vérifier si la méthode de requête est POST (le formulaire a été soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $Nom = htmlspecialchars($_POST['username']);
    $Email = htmlspecialchars($_POST['email']);
    $Mot_de_passe = $_POST['password'];

    // Hacher le mot de passe pour des raisons de sécurité
    $Mot_de_passe_hache = password_hash($Mot_de_passe, PASSWORD_DEFAULT);

    // Définir le rôle par défaut pour les nouveaux utilisateurs
    $role = 'user';

    // Initialiser le chemin de la photo de profil à null
    $photo_de_profil_path = null;

    // Gérer le téléchargement de la photo de profil
    if (isset($_FILES['photo_de_profil']) && $_FILES['photo_de_profil']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        // Tente de créer le dossier si inexistant.
        // Un échec ici (par ex. permissions) peut causer un 500 si non géré.
        if (!is_dir($target_dir)) {
            // Utilisation de @ pour supprimer les avertissements si mkdir échoue,
            // et vérification explicite du succès.
            if (!@mkdir($target_dir, 0777, true)) {
                $message = "Erreur : Impossible de créer le dossier de téléchargement. Vérifiez les permissions du dossier parent.";
                $message_type = 'error';
                error_log("Erreur mkdir dans register.php: Impossible de créer " . $target_dir);
            }
        }

        // Si le dossier a été créé ou existe et qu'il n'y a pas eu d'erreur avant
        if ($message_type !== 'error') {
            $file_extension = pathinfo($_FILES['photo_de_profil']['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid('profile_') . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;

            $check = getimagesize($_FILES['photo_de_profil']['tmp_name']);
            if ($check !== false) {
                if (!move_uploaded_file($_FILES['photo_de_profil']['tmp_name'], $target_file)) {
                    $message = "Désolé, une erreur s'est produite lors du téléchargement de votre fichier.";
                    $message_type = 'error';
                    error_log("Erreur move_uploaded_file dans register.php: " . $_FILES['photo_de_profil']['error']);
                } else {
                    $photo_de_profil_path = $target_file;
                }
            } else {
                $message = "Le fichier téléchargé n'est pas une image valide.";
                $message_type = 'error';
            }
        }
    }

    // Si aucune erreur de téléchargement, tenter l'insertion en BD
    if ($message_type !== 'error') {
        try {
            // Préparer la requête d'insertion avec des placeholders nommés pour PDO
            $stmt = $conn->prepare("INSERT INTO users (Nom, Email, Mot_de_passe, photo_de_profil, role) VALUES (:nom, :email, :mot_de_passe, :photo_de_profil, :role)");

            // Binder les valeurs aux placeholders
            $stmt->bindParam(':nom', $Nom);
            $stmt->bindParam(':email', $Email);
            $stmt->bindParam(':mot_de_passe', $Mot_de_passe_hache);
            $stmt->bindParam(':photo_de_profil', $photo_de_profil_path);
            $stmt->bindParam(':role', $role);

            // Exécuter la requête
            if ($stmt->execute()) {
                $message = "Utilisateur enregistré avec succès ! Vous pouvez maintenant vous connecter.";
                $message_type = 'success';
                // Optionnel: Rediriger vers la page de connexion après un succès
                // header('Location: login.php?registered=true');
                // exit;
            } else {
                $message = "Erreur lors de l'enregistrement de l'utilisateur : " . implode(" - ", $stmt->errorInfo());
                $message_type = 'error';
                error_log("Erreur PDO execute dans register.php: " . implode(" - ", $stmt->errorInfo()));
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Code SQLSTATE pour violation de contrainte d'intégrité
                $message = "Erreur: Le nom d'utilisateur ou l'email est déjà utilisé.";
            } else {
                $message = "Erreur de base de données : " . $e->getMessage();
            }
            $message_type = 'error';
            error_log("Erreur PDO catch dans register.php: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Utilisateur</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Gris clair */
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Inscription</h2>

        <?php if ($message): ?>
            <div class="px-4 py-3 rounded relative mb-6
                <?php echo ($message_type === 'success') ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>"
                role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" enctype="multipart/form-data">
            <div class="mb-5">
                <label for="username" class="block text-gray-700 text-sm font-medium mb-2">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Entrez votre nom" required>
            </div>
            <div class="mb-5">
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Entrez votre email" required>
            </div>
            <div class="mb-5">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Mot de passe</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Entrez votre mot de passe" required>
            </div>
            <div class="mb-6">
                <label for="photo_de_profil" class="block text-gray-700 text-sm font-medium mb-2">Photo de profil</label>
                <input type="file" id="photo_de_profil" name="photo_de_profil" accept="image/*" class="w-full text-gray-700 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                S'inscrire
            </button>
        </form>
        <p class="text-center text-gray-600 text-sm mt-6">
            Déjà un compte ? <a href="login.php" class="text-blue-600 hover:underline">Se connecter ici</a>
        </p>
    </div>
</body>
</html>