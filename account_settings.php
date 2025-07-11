<?php
session_start();
require_once 'bd.php'; // Incluez votre fichier de connexion à la BDD

// Redirige l'utilisateur s'il n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?status=login_required');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Récupérer les informations actuelles de l'utilisateur
$user_info = null;
try {
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_info) {
        // Si l'utilisateur n'est pas trouvé (cas rare mais possible)
        session_destroy();
        header('Location: login.php?status=error&msg=Votre session est invalide.');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la récupération des infos utilisateur: " . $e->getMessage());
    $message = "Impossible de récupérer vos informations. Veuillez réessayer.";
    $message_type = 'error';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_info) {
    $new_username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $new_email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $current_password = filter_input(INPUT_POST, 'current_password', FILTER_SANITIZE_STRING);
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
    $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

    $errors = [];

    // Validation des champs
    if (empty($new_username)) { $errors[] = "Le nom d'utilisateur est requis."; }
    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Une adresse email valide est requise."; }

    // Vérifier si le nom d'utilisateur ou l'email est déjà pris par un autre utilisateur
    try {
        if ($new_username !== $user_info['username']) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$new_username, $user_id]);
            if ($stmt->fetch()) { $errors[] = "Ce nom d'utilisateur est déjà pris."; }
        }
        if ($new_email !== $user_info['email']) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$new_email, $user_id]);
            if ($stmt->fetch()) { $errors[] = "Cette adresse email est déjà utilisée."; }
        }
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la vérification des unicité: " . $e->getMessage());
        $errors[] = "Erreur de base de données lors de la vérification des données.";
    }


    // Gestion du changement de mot de passe
    $password_changed = false;
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Veuillez entrer votre mot de passe actuel pour changer le mot de passe.";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "Le nouveau mot de passe et la confirmation ne correspondent pas.";
        } elseif (strlen($new_password) < 6) { // Exemple de règle de mot de passe
            $errors[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
        } else {
            // Vérifier le mot de passe actuel
            $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_data && password_verify($current_password, $user_data['password_hash'])) {
                $password_changed = true;
            } else {
                $errors[] = "Le mot de passe actuel est incorrect.";
            }
        }
    }

    if (empty($errors)) {
        try {
            $update_query = "UPDATE users SET username = ?, email = ?";
            $params = [$new_username, $new_email];

            if ($password_changed) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query .= ", password_hash = ?";
                $params[] = $new_password_hash;
            }
            $update_query .= " WHERE id = ?";
            $params[] = $user_id;

            $stmt = $conn->prepare($update_query);
            $success = $stmt->execute($params);

            if ($success) {
                // Mettre à jour les informations de session si le username a changé
                $_SESSION['username'] = $new_username;

                header('Location: main.php?status=account_updated');
                exit;
            } else {
                $message = "Erreur lors de la mise à jour de vos informations.";
                $message_type = 'error';
            }

        } catch (PDOException $e) {
            error_log("Erreur PDO lors de la mise à jour du compte: " . $e->getMessage());
            $message = "Une erreur de base de données est survenue. Veuillez réessayer.";
            $message_type = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres du Compte - Snapcat</title>
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
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Paramètres de votre Compte</h1>

        <?php if ($message): ?>
            <div class="message-box <?php echo ($message_type === 'success') ? 'message-success' : 'message-error'; ?>">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($user_info): ?>
        <form action="account_settings.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($user_info['username']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user_info['email']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <hr class="my-6 border-gray-300">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Changer de Mot de Passe</h2>

            <div>
                <label for="current_password" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <p class="text-xs text-gray-500 mt-1">Laissez vide si vous ne voulez pas changer le mot de passe.</p>
            </div>
            <div>
                <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <button type="submit" class="btn-cta w-full py-2">Mettre à jour le Compte</button>
        </form>
        <?php else: ?>
            <p class="text-gray-600 text-center">Vos informations ne peuvent pas être chargées. Veuillez vous reconnecter.</p>
        <?php endif; ?>
    </main>

    <footer class="footer bg-gray-800 text-white p-4 text-center mt-auto">
        &copy; <?php echo date('Y'); ?> Snapcat. Tous droits réservés.
    </footer>
</body>
</html>