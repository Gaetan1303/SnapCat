<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

include('db.php');
require 'db.php'; // Assurez-vous que ce fichier contient la connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_POST['sender_id'];
    $receiver_id = $_POST['receiver_id'];
    $image_path = $_FILES['image']['name'];
    $expiration_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // Déplacer l'image téléchargée dans un dossier spécifique
    move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image_path);

    $stmt = $conn->prepare("INSERT INTO snapshots (sender_id, receiver_id, image_path, expiration_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $image_path, $expiration_time);

    if ($stmt->execute()) {
        echo "Snap envoyé avec succès!";
    } else {
        echo "Erreur: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
<form method="POST" enctype="multipart/form-data">
    <input type="number" name="sender_id" placeholder="Votre ID" required>
    <input type="number" name="receiver_id" placeholder="ID du destinataire" required>
    <input type="file" name="image" required>
    <button type="submit">Envoyer le Snap</button>
</form>
