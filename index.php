<?php


session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
 // Assurez-vous que ce fichier contient la connexion à la base de données

// API pour enregistrer les profils de chats
$chats = [];     
// Fonction pour ajouter un chat au "site de rencontre"
function ajouterProfilChat($nom, $id, $numero_puce, $age, $sexe, $photo, $description, $localisation, $interets, $caracteristiques) {
    global $chats;
    $chats[] = [
        'nom' => $nom,
        'id' => $id,
        'numéro de puce' => $numero_puce,
        'age' => $age,
        'sexe' => $sexe,
        'photo' => $photo,
        'description' => $description,
        'localisation' => $localisation,
        'interets' => $interets,
        'caracteristiques' => $caracteristiques
    ];
}   
