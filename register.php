<?php
session_start();

// Inclusions de fichiers essentiels
require_once 'db.php'; // Contient la connexion $conn
require_once 'UserModel.php'; // Contient la classe UserModel

$message = ''; // Variable pour afficher des messages à l'utilisateur

// Vérifie que la connexion à la base de données est valide avant d'instancier UserModel
if (!$conn instanceof PDO) {
    // Si la connexion n'est pas valide, affiche une erreur critique et arrête le script.
    // L'erreur détaillée est déjà logguée dans db.php
    die('<p style="color: red;">Erreur interne du serveur : Connexion à la base de données non valide.</p>');
}

$userModel = new UserModel($conn);

// Traitement de la soumission du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données POST
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    // --- MODIFICATION ICI : Récupération du username ---
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    // --- Validation des champs ---
    if (empty($email) || empty($username) || empty($password) || empty($passwordConfirm)) { // Ajout de $username à la validation
        $message = '<p style="color: red;">Veuillez remplir tous les champs.</p>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<p style="color: red;">L\'adresse e-mail n\'est pas valide.</p>';
    } elseif ($password !== $passwordConfirm) {
        $message = '<p style="color: red;">Les mots de passe ne correspondent pas.</p>';
    } else {
        // Tous les champs sont valides, on procède à l'enregistrement
        try {
            // Vérifier si l'utilisateur existe déjà avec cet e-mail
            if ($userModel->userExists($email)) {
                $message = '<p style="color: red;">Cet e-mail est déjà enregistré.</p>';
            } else {
                // Hacher le mot de passe avant de l'insérer dans la base de données
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Tente d'enregistrer le nouvel utilisateur via le UserModel
                // --- MODIFICATION ICI : Passage du username à registerUser ---
                if ($userModel->registerUser($email, $username, $hashedPassword)) {
                    $message = '<p style="color: green;">Inscription réussie ! Vous pouvez maintenant vous connecter.</p>';
                    // Redirection après une inscription réussie pour éviter la re-soumission du formulaire
                    header('Location: login.php?registration=success');
                    exit; // Toujours appeler exit après un header Location
                } else {
                    $message = '<p style="color: red;">Une erreur est survenue lors de l\'inscription. Veuillez réessayer.</p>';
                    // L'erreur détaillée est déjà logguée par UserModel
                }
            }
        } catch (Throwable $th) {
            // Capture les exceptions inattendues qui ne seraient pas gérées par PDOException
            error_log("Erreur inattendue dans register.php: " . $th->getMessage());
            $message = '<p style="color: red;">Une erreur est survenue. Veuillez réessayer plus tard.</p>';
        }
    }
}

// Conserver l'email et le username entrés en cas d'erreur de formulaire
$emailValue = htmlspecialchars($_POST['email'] ?? '');
$usernameValue = htmlspecialchars($_POST['username'] ?? ''); // Pour pré-remplir le champ username
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .register-container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h1 { color: #333; margin-bottom: 20px; }
        form div { margin-bottom: 15px; text-align: left; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="email"], input[type="text"], input[type="password"] { width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background-color: #0056b3; }
        p { margin-top: 20px; color: #666; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Inscription</h1>
        <?php echo $message; // Affiche les messages de succès ou d'erreur ?>
        <form method="POST" action="">
            <div>
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required value="<?= $emailValue ?>">
            </div>
            <div>
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required value="<?= $usernameValue ?>">
            </div>
            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="password_confirm">Confirmer mot de passe :</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit">S'inscrire</button>
        </form>
        <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
    </div>
</body>
</html>

