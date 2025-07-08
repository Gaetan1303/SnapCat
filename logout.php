<?php
session_start(); // Démarre la session

// Détruit toutes les variables de session
$_SESSION = array();

// Si vous utilisez des cookies de session, cela les détruit également.
// Note: Cela détruira la session, et pas seulement les données de session !
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalement, détruit la session.
session_destroy();

// Redirige l'utilisateur vers la page de connexion ou la page d'accueil
header('Location: login.php'); // Ou index.php, selon votre structure
exit;
?>
