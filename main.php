<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require 'db.php'; // Assurez-vous que ce fichier contient la connexion à la base de données

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

// Exemple d'ajout de profils
ajouterProfilChat('Miaou', 1, '12345', 2, 'F', 'https://cat.com/photo1.jpg', 'Un chat énergique et curieux.', 'Paris, France', ['jouer', 'dormir'], ['poils courts', 'yeux bleus']);
ajouterProfilChat('Felix', 2, '67890', 3, 'M', 'https://cat.com/photo2.jpg', 'Un chat calme et posé.', 'Lyon, France', ['manger', 'dormir'], ['poils longs', 'yeux verts']);

// Affiche tous les profils enregistrés
echo "Profils de chats enregistrés :\n";
foreach ($chats as $profil) {
    echo "- Nom : {$profil['nom']}, Numéro : {$profil['id']}, Numéro de puce : {$profil['numéro de puce']}, Age : {$profil['age']}, Sexe : {$profil['sexe']}, Localisation : {$profil['localisation']}\n";
    echo "  Description : {$profil['description']}\n";
    echo "  Intérêts : " . implode(', ', $profil['interets']) . "\n";
    echo "  Caractéristiques : " . implode(', ', $profil['caracteristiques']) . "\n";
    echo "  Photo : {$profil['photo']}\n\n";
}

// Fonction pour simuler une rencontre
function Rencontre($chat1, $chat2) {
    echo "Rencontre entre {$chat1['nom']} et {$chat2['nom']} !\n";
    // Logique de rencontre : vérifier les intérêts communs
    $interetsCommuns = array_intersect($chat1['interets'], $chat2['interets']);
    if (!empty($interetsCommuns)) {
        echo "Ils ont des intérêts communs : " . implode(', ', $interetsCommuns) . ".\n";
    } else {
        echo "Pas d'intérêts communs, mais ils peuvent toujours devenir amis !\n";
    }
}

// Simuler une rencontre entre deux chats
if (count($chats) > 1) {
    Rencontre($chats[0], $chats[1]);
} else {
    echo "Pas assez de chats pour une rencontre.\n";
}

?>
