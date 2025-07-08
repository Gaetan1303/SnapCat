<?php
session_start(); // Démarre la session pour gérer l'état de l'utilisateur

// --- DÉBOGAGE TEMPORAIRE : À RETIRER EN PRODUCTION ---
ini_set('display_errors', 1); // Affiche les erreurs directement dans le navigateur
ini_set('display_startup_errors', 1); // Affiche les erreurs de démarrage
error_reporting(E_ALL);      // Rapporte tous les types d'erreurs PHP
// -----------------------------------------------------

// Vérifie si l'utilisateur est connecté. Si non, redirige vers la page de connexion.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Inclut le fichier de connexion à la base de données.
require_once 'bd.php';

// Vérifie si l'objet de connexion PDO est valide
if (!isset($conn) || !$conn instanceof PDO) {
    die("Erreur fatale : La connexion à la base de données n'a pas pu être établie via bd.php. Vérifiez votre fichier bd.php et la disponibilité de MySQL.");
}

$message = ''; // Variable pour stocker les messages d'erreur ou de succès
$message_type = ''; // 'success' ou 'error'

// Récupère l'ID de l'utilisateur connecté
$current_user_id = $_SESSION['user_id'];

// Récupère les informations actuelles de l'utilisateur
try {
    $stmt = $conn->prepare("SELECT id, Nom, Email, Mot_de_passe, photo_de_profil, role FROM users WHERE id = :id");
    $stmt->bindParam(':id', $current_user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Si l'utilisateur n'est pas trouvé (ce qui ne devrait pas arriver si la session est valide)
        session_destroy(); // Détruit la session invalide
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des informations utilisateur : " . $e->getMessage();
    $message_type = 'error';
    error_log("Erreur PDO (récupération profil) : " . $e->getMessage());
}

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $new_nom = htmlspecialchars($_POST['nom']);
    $new_email = htmlspecialchars($_POST['email']);
    $current_password_input = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    $update_fields = [];
    $update_values = [];

    // Mettre à jour le nom si modifié
    if ($new_nom !== $user['Nom']) {
        $update_fields[] = 'Nom = :nom';
        $update_values[':nom'] = $new_nom;
    }

    // Mettre à jour l'email si modifié
    if ($new_email !== $user['Email']) {
        $update_fields[] = 'Email = :email';
        $update_values[':email'] = $new_email;
    }

    // Gérer la mise à jour du mot de passe
    if (!empty($new_password)) {
        if (!password_verify($current_password_input, $user['Mot_de_passe'])) {
            $message = "Le mot de passe actuel est incorrect.";
            $message_type = 'error';
        } elseif ($new_password !== $confirm_new_password) {
            $message = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
            $message_type = 'error';
        } else {
            $update_fields[] = 'Mot_de_passe = :mot_de_passe';
            $update_values[':mot_de_passe'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    // Gérer le téléchargement de la nouvelle photo de profil
    $new_photo_path = $user['photo_de_profil']; // Conserve l'ancienne photo par défaut
    if (isset($_FILES['photo_de_profil']) && $_FILES['photo_de_profil']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo_de_profil']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $target_dir = 'uploads/'; // Même dossier que pour l'inscription
            if (!is_dir($target_dir)) {
                if (!@mkdir($target_dir, 0777, true)) {
                    $message = "Erreur : Impossible de créer le dossier de téléchargement. Vérifiez les permissions.";
                    $message_type = 'error';
                    error_log("Erreur mkdir dans profile.php: Impossible de créer " . $target_dir);
                }
            }

            if ($message_type === '') {
                $unique_file_name = uniqid('profile_', true) . '.' . $ext;
                $temp_target_file = $target_dir . $unique_file_name;

                if (move_uploaded_file($_FILES['photo_de_profil']['tmp_name'], $temp_target_file)) {
                    $new_photo_path = $temp_target_file;
                    $update_fields[] = 'photo_de_profil = :photo_de_profil';
                    $update_values[':photo_de_profil'] = $new_photo_path;

                    // Supprimer l'ancienne photo si elle existe et est différente de la nouvelle
                    if ($user['photo_de_profil'] && $user['photo_de_profil'] !== $new_photo_path && file_exists($user['photo_de_profil'])) {
                        @unlink($user['photo_de_profil']); // Supprime sans générer d'erreur si le fichier n'existe pas
                    }
                } else {
                    $message = "Erreur lors du déplacement du fichier uploadé.";
                    $message_type = 'error';
                    error_log("Erreur move_uploaded_file dans profile.php: " . $_FILES['photo_de_profil']['error']);
                }
            }
        } else {
            $message = "Ce fichier n'est pas une image valide (formats acceptés : jpg, jpeg, png, gif).";
            $message_type = 'error';
        }
    }

    // Exécuter la mise à jour si des champs ont été modifiés et aucune erreur n'est survenue
    if (!empty($update_fields) && $message_type === '') {
        try {
            $query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = :id";
            $stmt = $conn->prepare($query);
            $update_values[':id'] = $current_user_id;

            if ($stmt->execute($update_values)) {
                $message = "Votre profil a été mis à jour avec succès !";
                $message_type = 'success';
                // Mettre à jour les informations de session si le nom ou l'email a changé
                if (isset($update_values[':nom'])) {
                    $_SESSION['user_name'] = $new_nom;
                }
                // Recharger les informations utilisateur pour afficher les dernières données
                $stmt = $conn->prepare("SELECT id, Nom, Email, Mot_de_passe, photo_de_profil, role FROM users WHERE id = :id");
                $stmt->bindParam(':id', $current_user_id);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

            } else {
                $message = "Erreur lors de la mise à jour du profil : " . implode(" - ", $stmt->errorInfo());
                $message_type = 'error';
                error_log("Erreur PDO execute (update profil) : " . implode(" - ", $stmt->errorInfo()));
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Code SQLSTATE pour violation de contrainte d'intégrité (ex: email déjà utilisé)
                $message = "Erreur: L'email est déjà utilisé par un autre compte.";
            } else {
                $message = "Erreur de base de données lors de la mise à jour : " . $e->getMessage();
            }
            $message_type = 'error';
            error_log("Erreur PDO catch (update profil) : " . $e->getMessage());
        }
    } elseif (empty($update_fields) && $message_type === '') {
        $message = "Aucune modification à enregistrer.";
        $message_type = 'info'; // Nouveau type de message pour "info"
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Snapcat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #1a202c;
            margin-bottom: 20px;
            text-align: center;
        }
        form div {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #2563eb;
        }
        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            border: 3px solid #3b82f6;
        }
        .message-box {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border-width: 1px;
        }
        .message-success {
            background-color: #d1fae5; /* green-100 */
            border-color: #34d399; /* green-400 */
            color: #065f46; /* green-700 */
        }
        .message-error {
            background-color: #fee2e2; /* red-100 */
            border-color: #ef4444; /* red-400 */
            color: #b91c1c; /* red-700 */
        }
        .message-info {
            background-color: #e0f2fe; /* blue-100 */
            border-color: #38bdf8; /* blue-400 */
            color: #075985; /* blue-700 */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-4xl font-bold text-center text-gray-900 mb-8">Mon Profil</h1>

        <nav class="mb-8 p-4 bg-blue-600 text-white rounded-lg shadow-md flex justify-between items-center">
            <div class="text-xl font-semibold">
                Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?> !
            </div>
            <div>
                <a href="main.php" class="text-white hover:text-blue-200 mx-2">Accueil</a>
                <a href="profile.php" class="text-white hover:text-blue-200 mx-2">Mon Profil</a>
                <a href="logout.php" class="bg-blue-700 hover:bg-blue-800 text-white py-2 px-4 rounded-md ml-4">Déconnexion</a>
            </div>
        </nav>

        <?php if ($message): ?>
            <div class="message-box <?php echo ($message_type === 'success') ? 'message-success' : (($message_type === 'error') ? 'message-error' : 'message-info'); ?>">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <div class="text-center mb-6">
            <?php if ($user['photo_de_profil']): ?>
                <img src="<?php echo htmlspecialchars($user['photo_de_profil']); ?>" alt="Photo de profil" class="profile-photo">
            <?php else: ?>
                <div class="profile-photo bg-gray-200 flex items-center justify-center text-gray-500 text-sm">Pas de photo</div>
            <?php endif; ?>
            <p class="text-xl font-semibold text-gray-800 mt-2"><?php echo htmlspecialchars($user['Nom']); ?></p>
            <p class="text-gray-600"><?php echo htmlspecialchars($user['Email']); ?></p>
            <p class="text-gray-600 text-sm italic">Rôle : <?php echo htmlspecialchars($user['role']); ?></p>
        </div>

        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Modifier mon profil</h2>
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">

            <div class="mb-5">
                <label for="nom" class="block text-gray-700 text-sm font-medium mb-2">Nom d'utilisateur :</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['Nom']); ?>" required class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-5">
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-5">
                <label for="photo_de_profil" class="block text-gray-700 text-sm font-medium mb-2">Nouvelle photo de profil :</label>
                <input type="file" id="photo_de_profil" name="photo_de_profil" accept="image/*" class="w-full text-gray-700 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                <p class="text-xs text-gray-500 mt-1">Laissez vide pour conserver la photo actuelle.</p>
            </div>

            <h3 class="text-xl font-semibold text-gray-800 mb-4 mt-8">Changer le mot de passe (optionnel)</h3>
            <div class="mb-5">
                <label for="current_password" class="block text-gray-700 text-sm font-medium mb-2">Mot de passe actuel :</label>
                <input type="password" id="current_password" name="current_password" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Requis si vous changez votre mot de passe.</p>
            </div>
            <div class="mb-5">
                <label for="new_password" class="block text-gray-700 text-sm font-medium mb-2">Nouveau mot de passe :</label>
                <input type="password" id="new_password" name="new_password" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label for="confirm_new_password" class="block text-gray-700 text-sm font-medium mb-2">Confirmer le nouveau mot de passe :</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                Mettre à jour le profil
            </button>
        </form>
    </div>
</body>
</html>
