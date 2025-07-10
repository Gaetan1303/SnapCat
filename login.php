<?php
require_once 'db.php';

session_start(); // Démarre la session

// Si l'utilisateur est déjà connecté, redirigez-le vers la page d'accueil
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Inclusion de la classe UserModel
require_once 'UserModel.php';

$message = ''; // Variable pour stocker les messages d'erreur ou de succès

// Traitement de la soumission du formulaire de CONNEXION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des entrées utilisateur
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Validation simple
    if (empty($email) || empty($password)) {
        $message = '<p style="color: red;">Veuillez remplir tous les champs.</p>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<p style="color: red;">L\'adresse e-mail n\'est pas valide.</p>';
    } else {
        try {
            $userModel = new UserModel($email); // Passer $db ou une connexion PDO si UserModel en a besoin

            // Connexion
            $loggedInUser = $userModel->login($email, $password);
            if ($loggedInUser) {
                $_SESSION['user'] = $loggedInUser; // Stocke l'email de l'utilisateur dans la session
                header('Location: index.php'); // Redirige vers la page d'accueil
                exit;
            } else {
                $message = '<p style="color: red;">Identifiants incorrects. Veuillez réessayer.</p>';
            }
        } catch (Throwable $th) {
            // Capture les erreurs d'instanciation de UserModel (ex: problème de connexion à la DB)
            error_log("Erreur critique dans login.php: " . $th->getMessage());
            $message = '<p style="color: red;">Une erreur est survenue. Veuillez réessayer plus tard.</p>';
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
            text-decoration: none; /* Pour le lien */
            display: inline-block; /* Pour le lien */
            text-align: center; /* Pour le lien */
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
        <?php if (!empty($message)): ?>
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