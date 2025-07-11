<?php
session_start();
require_once 'bd.php';
require_once 'ChatModel.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?status=login_required');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
    $sexe = filter_input(INPUT_POST, 'sexe', FILTER_SANITIZE_STRING);
    $race = filter_input(INPUT_POST, 'race', FILTER_SANITIZE_STRING);
    $localisation = filter_input(INPUT_POST, 'localisation', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    $photo_path = null;

    // Gestion du téléchargement de photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['photo']['tmp_name'];
        $file_name = $_FILES['photo']['name'];
        $file_size = $_FILES['photo']['size'];
        $file_type = $_FILES['photo']['type'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024; // 5 MB

        if (in_array($file_ext, $allowed_ext) && $file_size <= $max_size) {
            $upload_dir = 'uploads/cats/'; // Assurez-vous que ce dossier existe et est inscriptible
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Crée le dossier s'il n'existe pas
            }
            $new_file_name = uniqid('cat_') . '.' . $file_ext;
            $destination_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $destination_path)) {
                $photo_path = $destination_path;
            } else {
                $message = "Erreur lors du déplacement de la photo.";
                $message_type = 'error';
            }
        } else {
            $message = "Type de fichier non autorisé ou taille de fichier trop grande (max 5MB).";
            $message_type = 'error';
        }
    }

    if (empty($nom)) {
        $message = "Le nom du chat est requis.";
        $message_type = 'error';
    }

    if (empty($message)) { // Si aucune erreur n'a été rencontrée jusqu'à présent
        try {
            $chatModel = new ChatModel($conn);
            $success = $chatModel->addCat($user_id, $nom, $age, $sexe, $race, $localisation, $description, $photo_path);

            if ($success) {
                header('Location: main.php?status=cat_added');
                exit;
            } else {
                $message = "Erreur lors de l'ajout du chat dans la base de données.";
                $message_type = 'error';
            }
        } catch (Exception $e) {
            error_log("Erreur lors de l'ajout du chat: " . $e->getMessage());
            $message = "Une erreur inattendue est survenue lors de l'ajout. " . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Chat - Snapcat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f8f8; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 0 15px; }
        .header-main { background-color: #fff; padding: 20px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .logo { font-size: 2.2rem; font-weight: 800; color: #007bff; text-decoration: none; }
        .logo span { color: #28a745; }
        .btn-cta { background-color: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: 600; transition: background-color 0.3s ease; }
        .btn-cta:hover { background-color: #218838; }
        .message-box { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border-width: 1px; font-weight: 500; text-align: center; }
        .message-success { background-color: #d1fae5; border-color: #34d399; color: #065f46; }
        .message-error { background-color: #fee2e2; border-color: #ef4444; color: #b91c1c; }
    </style>
</head>
<body>
    <header>
        <div class="header-main">
            <div class="container flex justify-between items-center">
                <a href="index.php" class="logo">Snap<span>Cat</span></a>
                <nav class="main-nav hidden md:flex space-x-6">
                    <a href="main.php" class="text-gray-700 hover:text-blue-500 font-medium">Retour au Dashboard</a>
                </nav>
                <div class="flex items-center space-x-4">
                    <a href="logout.php" class="btn-cta bg-red-500 hover:bg-red-600">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container my-8 p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Ajouter un Nouveau Profil de Chat</h1>

        <?php if ($message): ?>
            <div class="message-box <?php echo ($message_type === 'success') ? 'message-success' : 'message-error'; ?>">
                <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <form action="add_cat.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="nom" class="block text-gray-700 text-sm font-bold mb-2">Nom du chat <span class="text-red-500">*</span></label>
                <input type="text" id="nom" name="nom" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Âge (ans)</label>
                <input type="number" id="age" name="age" min="0" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="sexe" class="block text-gray-700 text-sm font-bold mb-2">Sexe</label>
                <select id="sexe" name="sexe" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Sélectionner</option>
                    <option value="Mâle">Mâle</option>
                    <option value="Femelle">Femelle</option>
                </select>
            </div>
            <div>
                <label for="race" class="block text-gray-700 text-sm font-bold mb-2">Race</label>
                <input type="text" id="race" name="race" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="localisation" class="block text-gray-700 text-sm font-bold mb-2">Localisation</label>
                <input type="text" id="localisation" name="localisation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                <textarea id="description" name="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div>
                <label for="photo" class="block text-gray-700 text-sm font-bold mb-2">Photo du chat</label>
                <input type="file" id="photo" name="photo" accept="image/jpeg, image/png, image/gif" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG, GIF (max 5MB)</p>
            </div>
            <button type="submit" class="btn-cta w-full py-2">Ajouter le Chat</button>
        </form>
    </main>

    <footer class="footer bg-gray-800 text-white p-4 text-center mt-auto">
        &copy; <?php echo date('Y'); ?> Snapcat. Tous droits réservés.
    </footer>
</body>
</html>