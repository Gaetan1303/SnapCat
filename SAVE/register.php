<?php
session_start();

// Inclusions de fichiers
require_once 'db.php';
require_once 'UserModel.php';

$message = ''; // Message d'erreur ou de succès

// --- Début des points de contrôle ---

// Vérifier que $conn est bien un objet PDO avant d'instancier UserModel
if ($conn instanceof PDO) {
    $userModel = new UserModel($conn);
    // echo "DEBUG: UserModel instancié avec succès.<br>"; // Message de débogage
} else {
    $message = '<p style="color: red;">Erreur interne : Connexion à la base de données non valide.</p>';
    // echo "DEBUG: Erreur: \$conn n'est pas un objet PDO.<br>"; // Message de débogage
    // die("Arrêt : Connexion BDD non valide."); // Arrête si la connexion est ko
}

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // echo "DEBUG: Formulaire soumis.<br>"; // Message de débogage

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? ''; // Assurez-vous d'avoir ce champ dans votre formulaire HTML !

    // --- Validation des champs ---
    if (empty($email) || empty($password) || empty($passwordConfirm)) {
        $message = '<p style="color: red;">Veuillez remplir tous les champs.</p>';
        // echo "DEBUG: Erreur: Champs vides.<br>"; // Message de débogage
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<p style="color: red;">L\'adresse e-mail n\'est pas valide.</p>';
        // echo "DEBUG: Erreur: Email invalide.<br>"; // Message de débogage
    } elseif ($password !== $passwordConfirm) {
        $message = '<p style="color: red;">Les mots de passe ne correspondent pas.</p>';
        // echo "DEBUG: Erreur: Mots de passe non correspondants.<br>"; // Message de débogage
    } else {
        // Tous les champs sont valides, on continue
        // echo "DEBUG: Validation initiale réussie.<br>"; // Message de débogage

        try {
            // Vérifier si l'utilisateur existe déjà
            if (isset($userModel) && $userModel->userExists($email)) { // Appelle userExists (singulier)
                $message = '<p style="color: red;">Cet e-mail est déjà enregistré.</p>';
                // echo "DEBUG: Erreur: Email déjà enregistré.<br>"; // Message de débogage
            } else {
                // Hacher le mot de passe avant de l'insérer
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // --- Insertion dans la base de données ---
                // Il vous manque la méthode d'enregistrement dans UserModel !
                // Ajoutez une méthode `registerUser` ou similaire dans UserModel.php
                // Par exemple, dans UserModel.php :
                /*
                public function registerUser($email, $hashedPassword) {
                    $stmt = $this->conn->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                    return $stmt->execute(); // Renvoie true en cas de succès, false sinon
                }
                */

                if (isset($userModel) && $userModel->registerUser($email, $hashedPassword)) { // Appel à la nouvelle méthode
                    $message = '<p style="color: green;">Inscription réussie ! Vous pouvez maintenant vous connecter.</p>';
                    // Optionnel : Rediriger après une inscription réussie
                     header('Location: login.php?registration=success');
                    
                    // echo "DEBUG: Inscription réussie.<br>"; // Message de débogage
                } else {
                    // $message = '<p style="color: red;">Une erreur est survenue lors de l\'inscriiiiiiiiiiiiiiiiiiiiiiiiiiiiption. Veuillez réessayer.</p>';
                    // echo "DEBUG: Erreur lors de l'insertion en base de données.<br>"; // Message de débogage
                    var_dump($userModel);
                    
                }
            }
        } catch (Throwable $th) {
            // error_log("Erreur dans register.php: " . $th->getMessage());
            
            $message = '<p style="color: red;">Une erreur est survenue. Veuillez réessayer plus tard.</p>';
            // echo "DEBUG: Exception capturée: " . $th->getMessage() . "<br>"; // Message de débogage
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>
<body>
    <h1>Inscription</h1>
    <?php echo $message; ?>
    <form method="POST" action="">
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <br>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
        <br>
        <label for="password_confirm">Confirmer mot de passe :</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
        <br>
        <button type="submit">S'inscrire</button>
    </form>
    <p>Déjà un compte ? <a href="login.php"><button>Se connecter</button></a></p>
</body>
</html>