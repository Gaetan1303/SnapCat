<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Nom = $_POST['username'];
    $Email = $_POST['email'];
    $Mot_de_passe = password_hash($_POST['password'], PASSWORD_DEFAULT); // Sécurisation du mot de passe

    $stmt = $conn->prepare("INSERT INTO users (Nom, Email, Mot_de_passe) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $Nom, $Email, $Mot_de_passe);

    if ($stmt->execute()) {
        echo "Utilisateur enregistré avec succès!";
    } else {
        echo "Erreur: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
<form method="POST">
    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">S'inscrire</button>
</form>