<?php
require_once 'db.php';
require_once 'UserModel.php'; 

session_start(); // Démarre la session


if (!isset($_SESSION['users']) || $_SESSION['users']['user_id'] != $loggedInUser["id"]) {
    $_SESSION['users'] = [
        "user_id" => $loggedInUser["id"],
        "username" => $loggedInUser['username'],
        "email" => $loggedInUser['email']
    ];
    // Si l'utilisateur n'est pas authentifié, on le redirige vers la page de connexion
    header('Location: login.php'); // Assurez-vous que login.php existe
    exit;
}
// Inclusion de la classe UserModel
// Assurez-vous que le chemin est correct (ex: 'UserModel.php' si dans le même dossier, ou 'models/UserModel.php')
require_once 'UserModel.php';

$message = ''; // Variable pour stocker les messages d'erreur ou de succès

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des entrées utilisateur
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? ''; // Le mot de passe ne doit pas être filtré avec SANITIZE_STRING car cela pourrait modifier des caractères spéciaux légitimes. Il sera haché.

    // Validation simple
    if (empty($email) || empty($password)) {
        $message = '<p style="color: red;">Veuillez remplir tous les champs.</p>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<p style="color: red;">L\'adresse e-mail n\'est pas valide.</p>';
    } else {
        try {
            $userModel = new UserModel($conn);

            // Vérifier si c'est une demande d'inscription (signup) ou de connexion (login)
            if (isset($_POST['action']) && $_POST['action'] === 'signup') {
                // Inscription
                if ($userModel->userExists($email)) {
                    $message = '<p style="color: red;">Cet e-mail est déjà enregistré. Veuillez vous connecter ou utiliser un autre e-mail.</p>';
                } else {
                    $registered = $userModel->signup($email, $password);
                    if ($registered) {
                        // Inscription réussie, connectez l'utilisateur automatiquement
                        $_SESSION['user'] = $email;
                        header('Location: index.php');
                        exit;
                    } else {
                        $message = '<p style="color: red;">Erreur lors de l\'inscription. Veuillez réessayer.</p>';
                    }
                }
            } else {
                // Connexion (action par défaut si non spécifié ou si 'login')
                $loggedInUser = $userModel->login($email, $password);
                if ($loggedInUser) {
                    $_SESSION['user'] = $loggedInUser; // Stocke l'email de l'utilisateur dans la session
                    header('Location: index.php'); // Redirige vers la page d'accueil
                    exit;
                } else {
                    $message = '<p style="color: red;">Identifiants incorrects. Veuillez réessayer.</p>';
                }
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
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; text-align: center; }
        h2 { color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="email"], input[type="password"] { width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-group { display: flex; justify-content: space-between; gap: 10px; margin-top: 20px; }
        button { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; flex-grow: 1; }
        .btn-login { background-color: #007bff; color: white; }
        .btn-login:hover { background-color: #0056b3; }
        .btn-signup { background-color: #28a745; color: white; }
        .btn-signup:hover { background-color: #218838; }
        .message { margin-top: 15px; font-weight: bold; }
        .message p { margin: 0; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Connexion à SnapCat</h2>
        <div class="message">
            <?php echo $message; ?>
        </div>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Adresse e-mail :</label>
                <input type="email" id="email" name="email" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <div class="btn-group">
                <button type="submit" name="action" value="login" class="btn-login">Se connecter</button>
                <button type="submit" name="action" value="signup" class="btn-signup">S'inscrire</button>
            </div>
        </form>
    </div>
</body>
</html>
*



