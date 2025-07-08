<?php
// --- DÉBOGAGE TEMPORAIRE : À RETIRER EN PRODUCTION ---
ini_set('display_errors', 1); // Affiche les erreurs directement dans le navigateur
ini_set('display_startup_errors', 1); // Affiche les erreurs de démarrage
error_reporting(E_ALL);      // Rapporte tous les types d'erreurs PHP
// -----------------------------------------------------

session_start(); // Démarre la session pour gérer l'état de l'utilisateur

// Inclut le fichier de connexion à la base de données.
// Assurez-vous que le chemin est correct.
require_once 'bd.php'; // Ligne 6

// --- NOUVEAU : AJOUT POUR LE DÉBOGAGE DE $conn ---
// Cette ligne va afficher l'état de la variable $conn juste après l'inclusion de bd.php
// et arrêter le script pour que nous puissions voir le résultat.
// NE LAISSEZ PAS CELA EN PRODUCTION.
echo "<pre>";
var_dump($conn);
echo "</pre>";
// exit; // Décommentez cette ligne si vous voulez que le script s'arrête ici pour une analyse plus facile.
// --- FIN DU NOUVEAU DÉBOGAGE ---


// Vérifie si l'objet de connexion PDO est valide après l'inclusion de bd.php
if (!isset($conn) || !$conn instanceof PDO) {
    // Si la connexion n'est pas établie ou n'est pas un objet PDO, affiche un message d'erreur fatal.
    die("Erreur fatale : La connexion à la base de données n'a pas pu être établie via bd.php. Vérifiez votre fichier bd.php et la disponibilité de MySQL.");
}

$message = ''; // Variable pour stocker les messages d'erreur ou de succès

// Vérifie si le formulaire de connexion a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    try {
        // Prépare la requête pour récupérer l'utilisateur par son email
        $stmt = $conn->prepare("SELECT id, Nom, Email, Mot_de_passe, role FROM users WHERE Email = :email"); // Ligne 13 (ou 16 selon les insertions)
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Récupère les données de l'utilisateur
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie si un utilisateur a été trouvé et si le mot de passe est correct
        if ($user && password_verify($password, $user['Mot_de_passe'])) {
            // Mot de passe correct, démarre la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['Nom'];
            $_SESSION['user_role'] = $user['role']; // Stocke le rôle de l'utilisateur dans la session

            // Redirige l'utilisateur vers la page principale
            header('Location: main.php');
            exit;
        } else {
            // Identifiants incorrects
            $message = "Email ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        // Gère les erreurs de base de données
        $message = "Erreur de connexion à la base de données : " . $e->getMessage();
        error_log($message); // Enregistre l'erreur dans les logs du serveur
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Snapcat</title>
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
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Connexion</h2>

        <?php if ($message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-5">
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Entrez votre email" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Mot de passe</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Entrez votre mot de passe" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                Se connecter
            </button>
        </form>
        <p class="text-center text-gray-600 text-sm mt-6">
            Pas encore de compte ? <a href="register.php" class="text-blue-600 hover:underline">S'inscrire ici</a>
        </p>
    </div>
</body>
</html>
