<?php

// Incluez votre classe ChatModel
require_once 'ChatModel.php'; // Assurez-vous que le chemin est correct en fonction de l'emplacement réel de ChatModel.php

echo "--- Initialisation de la base de données et test du ChatModel ---\n";

try {
    // Instanciez le modèle de chat.
    // Le constructeur de ChatModel tentera de se connecter à la DB et de créer la table 'chats'.
    $chatModel = new ChatModel();
    echo "Connexion à la base de données et vérification/création de la table 'chats' réussies.\n";

    // --- Ajout de données initiales ---
    echo "Tentative d'ajout de profils de chats...\n";

    $chatsToAdd = [
        [
            "nom" => "Felix",
            "numero_puce" => "ABC12345",
            "age" => 3,
            "sexe" => "Mâle",
            "photo" => "http://example.com/felix.jpg",
            "description" => "Un chat joueur et affectueux.",
            "localisation" => "Toulouse",
            "interets" => "Jouer, dormir, manger",
            "caracteristiques" => "Poil court, yeux verts"
        ],
        [
            "nom" => "Mia",
            "numero_puce" => "DEF67890",
            "age" => 2,
            "sexe" => "Femelle",
            "photo" => "http://example.com/mia.jpg",
            "description" => "Une chatte calme et indépendante.",
            "localisation" => "Paris",
            "interets" => "Câlins, explorer, chasser",
            "caracteristiques" => "Poil long, yeux bleus"
        ],
        [
            "nom" => "Gribouille",
            "numero_puce" => "GHI98765",
            "age" => 5,
            "sexe" => "Mâle",
            "photo" => "http://example.com/gribouille.jpg",
            "description" => "Un gros matou paresseux mais très gentil.",
            "localisation" => "Lyon",
            "interets" => "Manger, dormir, être caressé",
            "caracteristiques" => "Gros, poil tigré, ronronne fort"
        ]
    ];

    foreach ($chatsToAdd as $chatData) {
        $isAdded = $chatModel->addChatProfile(
            $chatData["nom"],
            $chatData["numero_puce"],
            $chatData["age"],
            $chatData["sexe"],
            $chatData["photo"],
            $chatData["description"],
            $chatData["localisation"],
            $chatData["interets"],
            $chatData["caracteristiques"]
        );

        if ($isAdded) {
            echo "Profil de chat '" . $chatData["nom"] . "' ajouté avec succès.\n";
        } else {
            echo "Échec de l'ajout du profil de chat '" . $chatData["nom"] . "'. Le numéro de puce est peut-être déjà utilisé ou une autre erreur s'est produite. Vérifiez les logs PHP.\n";
        }
    }

    // --- Récupération et affichage des données ---
    echo "\n--- Profils de chats actuellement dans la base de données ---\n";
    $allChats = $chatModel->getAllChatProfiles();
    if (!empty($allChats)) {
        print_r($allChats);
    } else {
        echo "Aucun profil de chat trouvé (ou erreur lors de la récupération).\n";
    }

} catch (Throwable $th) {
    // Capture les erreurs non gérées par PDOException dans la classe
    error_log("Erreur inattendue lors de l'initialisation: " . $th->getMessage());
    echo "Une erreur inattendue s'est produite lors de l'initialisation. Vérifiez les logs pour plus de détails.\n";
}

echo "--- Initialisation terminée ---\n";

?>