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
            $userModel = new UserModel();

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
