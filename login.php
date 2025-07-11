<?php
// login.php - Page de connexion des utilisateurs

session_start(); // Démarre la session PHP, doit être la première chose dans le script.

// Inclusion de la connexion à la base de données (définit $conn)
require_once 'db.php';

if (!$_SESSION['users']['user_id'] != $loggedInUser["id"]) {
     [
        "user_id" => $loggedInUser["id"],
        "username" => $loggedInUser['username'],
        "email" => $loggedInUser['email']
    ];
    // Si l'utilisateur n'est pas authentifié, on le redirige vers la page de connexion
    header('Location: index.php'); // Assurez-vous que login.php existe
    exit;}

// Inclusion de la classe UserModel pour les opérations liées aux utilisateurs.
require_once 'UserModel.php';

$message = ''; // Variable pour stocker les messages d'erreur/succès à afficher.

// Vérification de la validité de la connexion PDO avant d'instancier UserModel.
if (!$conn instanceof PDO) {
    error_log("Erreur critique dans login.php: La variable \$conn n'est pas un objet PDO.");
    die('<p style="color: red;">Erreur interne du serveur. Impossible de se connecter à la base de données.</p>');
}

// Instanciation de UserModel en lui passant l'objet PDO $conn.
$userModel = new UserModel($conn);

// Traitement de la soumission du formulaire de connexion (méthode POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage et validation des entrées.
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Validation des champs côté serveur.
    if (empty($email) || empty($password)) {
        $message = '<p style="color: red;">Veuillez remplir tous les champs.</p>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<p style="color: red;">L\'adresse e-mail n\'est pas valide.</p>';
    } else {
        try {
            // Tente de connecter l'utilisateur via la méthode login du UserModel.
            $loggedInUser = $userModel->login($email, $password);

            if ($loggedInUser) {
                // Connexion réussie : Stocke l'ID et le nom d'utilisateur (ou l'email si username n'existe pas) en session.
                $_SESSION['users'] = [
                    "user_id" => $loggedInUser["id"],
                    "username" => $loggedInUser['username'],
                    "email" => $loggedInUser['email']
                ];

                // Redirige vers la page d'accueil après une connexion réussie.
                header('Location: index.php');
                exit;
            } else {
                // Identifiants incorrects.
                $message = '<p style="color: red;">Email ou mot de passe incorrect.</p>';
            }
        } catch (Throwable $th) {
            // Capture et journalise toute exception inattendue lors de la tentative de connexion.
            error_log("Erreur inattendue lors de la connexion dans login.php: " . $th->getMessage());
            $message = '<p style="color: red;">Une erreur est survenue lors de la connexion. Veuillez réessayer plus tard.</p>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapCat - Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            color: white;
        }
        .message p {
            margin: 0;
        }
        .message.error {
            background-color: #f44336;
        }
        .message.success {
            background-color: #4CAF50;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="email"],
        input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }
        button, .register-link {
            flex: 1;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
        }
        button:hover {
            background-color: #0056b3;
        }
        .register-link {
            background-color: #28a745;
            color: white;
            border: 1px solid #28a745;
        }
        .register-link:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bienvenue sur SnapCat</h2>
        <?php
        // Affiche le message de succès si redirection depuis register.php
        if (isset($_GET['registration']) && $_GET['registration'] === 'success') {
            echo '<div class="message success"><p>Inscription réussie ! Vous pouvez maintenant vous connecter.</p></div>';
        }
        // Affiche les autres messages d'erreur/succès
        if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'red') !== false) ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">E-mail :</label>
                <input type="email" id="email" name="email" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <div class="button-group">
                <button type="submit">Connexion</button>
                <a href="register.php" class="register-link">S'inscrire</a>
            </div>
        </form>
    </div>
</body>
</html>