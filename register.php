<?php
// --- DÉBOGAGE TEMPORAIRE : À RETIRER EN PRODUCTION ---
ini_set('display_errors', 1); // Affiche les erreurs directement dans le navigateur
ini_set('display_startup_errors', 1); // Affiche les erreurs de démarrage
error_reporting(E_ALL);      // Rapporte tous les types d'erreurs PHP
// -----------------------------------------------------

session_start(); // Démarre la session PHP

// Inclut le fichier de connexion à la base de données.
// Assurez-vous que le chemin est correct.
require_once 'bd.php';

// Vérifie si l'objet de connexion PDO est valide après l'inclusion de bd.php
if (!isset($conn) || !$conn instanceof PDO) {
    // Si la connexion n'est pas établie ou n'est pas un objet PDO, affiche un message d'erreur fatal.
    die("Erreur fatale : La connexion à la base de données n'a pas pu être établie via bd.php. Vérifiez votre fichier bd.php et la disponibilité de MySQL.");
}

$message = ''; // Variable pour stocker les messages d'erreur ou de succès
$message_type = ''; // 'success' ou 'error'

// Vérifie si le formulaire d'inscription a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données du formulaire
    $nom = htmlspecialchars($_POST['nom'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation des données
    if (empty($nom) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "Tous les champs sont requis.";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format d'email invalide.";
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = "Les mots de passe ne correspondent pas.";
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
        $message_type = 'error';
    } else {
        // Hachage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Vérifie si l'email existe déjà
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE Email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                $message = "Cet email est déjà enregistré.";
                $message_type = 'error';
            } else {
                // Insertion du nouvel utilisateur dans la base de données
                $stmt = $conn->prepare("INSERT INTO users (Nom, Email, Mot_de_passe, role) VALUES (:nom, :email, :mot_de_passe, 'user')");
                $stmt->bindParam(':nom', $nom);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':mot_de_passe', $hashed_password);

                if ($stmt->execute()) {
                    $message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                    $message_type = 'success';
                    // Optionnel : Rediriger vers la page de connexion après une inscription réussie
                    // header('Location: login.php');
                    // exit;
                } else {
                    $message = "Erreur lors de l'inscription : " . implode(" - ", $stmt->errorInfo());
                    $message_type = 'error';
                    error_log("Erreur PDO lors de l'inscription: " . implode(" - ", $stmt->errorInfo()));
                }
            }
        } catch (PDOException $e) {
            $message = "Erreur de base de données : " . $e->getMessage();
            $message_type = 'error';
            error_log("Erreur PDO catch lors de l'inscription: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Snapcat</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Gris clair */
        }
        .message-box {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border-width: 1px;
            font-weight: 500;
        }
        .message-success {
            background-color: #d1fae5; /* vert-100 */
            border-color: #34d399; /* vert-400 */
            color: #065f46; /* vert-700 */
        }
        .message-error {
            background-color: #fee2e2; /* rouge-100 */
            border-color: #ef4444; /* rouge-400 */
            color: #b91c1c; /* rouge-700 */
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Inscription</h2>

        <?php if ($message): ?>
            <div class="message-box <?php echo ($message_type === 'success') ? 'message-success' : 'message-error'; ?>">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="mb-5">
                <label for="nom" class="block text-gray-700 text-sm font-medium mb-2">Nom</label>
                <input type="text" id="nom" name="nom" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Entrez votre nom" required>
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
                <label for="confirm_password" class="block text-gray-700 text-sm font-medium mb-2">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Confirmez votre mot de passe" required>
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
