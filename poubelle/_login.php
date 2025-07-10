<?php

session_start(); // Démarre la session

// Si l'utilisateur est déjà connecté, redirigez-le vers la page d'accueil
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Inclusion de la classe UserModel
// Assurez-vous que le chemin est correct (ex: 'UserModel.php' si dans le même dossier, ou 'models/UserModel.php')
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
            $userModel = new UserModel();
            $loggedInUser = $userModel->login($email, $password); // Appelle la méthode de connexion

            if ($loggedInUser) {
                $_SESSION['users'] = $loggedInUser; // Stocke l'email de l'utilisateur dans la session
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
    <title>Connexion</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80vh; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h2 { color: #333; margin-bottom: 20px; }
        form label { display: block; margin-bottom: 5px; text-align: left; color: #555; }
        form input[type="email"], form input[type="password"] { width: calc(100% - 20px); padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; }
        form button { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease; }
        form button:hover { background-color: #0056b3; }
        .message { margin-bottom: 15px; font-weight: bold; }
        .register-link { margin-top: 20px; font-size: 1.1em; }
        .register-link a { color: #007bff; text-decoration: none; font-weight: bold; }
        .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>

        <?php echo $message; // Affiche les messages (erreurs/succès) ?>

        <form action="" method="POST">
            <label for="email">E-mail :</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Se connecter</button>
        </form>

        <hr> <div class="register-link">
            <p>Pas encore de compte ? <a href="register.php">S'inscrire ici</a></p>
        </div>
    </div>
</body>
</html>