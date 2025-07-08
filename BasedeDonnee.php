<?php
$sql = "SELECT * FROM chats";
$stmt = $pdo->query($sql);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Profils de chats enregistrés :<br>";
foreach ($chats as $profil) {
    echo "- Nom : {$profil['nom']}, Numéro de puce : {$profil['numero_puce']}, Age : {$profil['age']}, Sexe : {$profil['sexe']}, Localisation : {$profil['localisation']}<br>";
    echo "  Description : {$profil['description']}<br>";
    echo "  Intérêts : {$profil['interets']}<br>";
    echo "  Caractéristiques : {$profil['caracteristiques']}<br>";
    echo "  <img src='{$profil['photo']}' alt='Photo de {$profil['nom']}'><br><br>";
}
?>
