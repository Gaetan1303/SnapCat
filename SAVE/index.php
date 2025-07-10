<?php

// Démarrage de la session PHP
session_start();

// Vérification de l'authentification de l'utilisateur
// Si l'utilisateur n'est pas connecté, redirigez-le vers la page de login
if (!isset($_SESSION['users'])) {
    header('Location: login.php'); // Assurez-vous que login.php existe
    exit;
}

// Inclusion de la classe ChatModel
// Assurez-vous que le chemin est correct en fonction de l'emplacement de ChatModel.php
// Par exemple, si ChatModel.php est dans un sous-dossier 'modeles': require_once 'modeles/ChatModel.php';
require_once 'ChatModel.php';

// Instanciation du modèle de chat pour interagir avec la base de données
try {
    $chatModel = new ChatModel();
} catch (PDOException $e) {
    // Si la connexion échoue (généralement déjà gérée dans le constructeur de ChatModel)
    // Affichez un message d'erreur et terminez l'exécution
    error_log("Erreur critique lors de l'instanciation de ChatModel: " . $e->getMessage());
    die("Désolé, une erreur est survenue. Veuillez réessayer plus tard.");
}

$message = ''; // Variable pour afficher des messages à l'utilisateur

// Traitement du formulaire d'ajout de chat (si un formulaire est soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_chat'])) {
    // Récupération et validation des données du formulaire
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $numeroPuce = filter_input(INPUT_POST, 'numero_puce', FILTER_SANITIZE_STRING);
    $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
    $sexe = filter_input(INPUT_POST, 'sexe', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $localisation = filter_input(INPUT_POST, 'localisation', FILTER_SANITIZE_STRING);
    $interets = filter_input(INPUT_POST, 'interets', FILTER_SANITIZE_STRING);
    $caracteristiques = filter_input(INPUT_POST, 'caracteristiques', FILTER_SANITIZE_STRING);

    // Pour la photo, vous devrez gérer l'upload de fichier.
    // Pour cet exemple, nous allons juste utiliser une URL fictive ou laisser vide.
    $photo = ''; // TODO: Implémenter la gestion d'upload de photo

    // Validation minimale
    if (empty($nom)) {
        $message = '<p style="color: red;">Le nom du chat est requis.</p>';
    } else {
        // Ajout du profil de chat via le modèle
        $added = $chatModel->addChatProfile(
            $nom, $numeroPuce, (int)$age, $sexe, $photo,
            $description, $localisation, $interets, $caracteristiques
        );

        if ($added) {
            $message = '<p style="color: green;">Profil de ' . htmlspecialchars($nom) . ' ajouté avec succès !</p>';
        } else {
            $message = '<p style="color: red;">Erreur lors de l\'ajout du profil de ' . htmlspecialchars($nom) . '. Le numéro de puce est peut-être déjà utilisé ou une erreur est survenue.</p>';
        }
    }
}

// Récupération de tous les profils de chats pour l'affichage
$chats = $chatModel->getAllChatProfiles();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>SnapCat - Accueil</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .chat-profile { border: 1px solid #eee; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .chat-profile h3 { margin-top: 0; }
        form div { margin-bottom: 10px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea, select { width: calc(100% - 22px); padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .logout-btn { float: right; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bienvenue sur SnapCat, <?php echo htmlspecialchars($_SESSION['users']); ?>!</h2>
        <a href="logout.php" class="logout-btn">Déconnexion</a>

        <?php echo $message; // Affiche les messages de succès ou d'erreur ?>

        <h3>Ajouter un nouveau profil de chat</h3>
        <form action="index.php" method="POST">
            <div>
                <label for="nom">Nom du chat * :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div>
                <label for="numero_puce">Numéro de puce :</label>
                <inpu-t type="text" id="numero_puce" name="numero_puce">
            </div>
            <div>
                <label for="age">Âge :</label>
                <input type="number" id="age" name="age" min="0">
            </div>
            <div>
                <label for="sexe">Sexe :</label>
                <select id="sexe" name="sexe">
                    <option value="">Sélectionner</option>
                    <option value="Mâle">Mâle</option>
                    <option value="Femelle">Femelle</option>
                </select>
            </div>
            <div>
                <label for="description">Description :</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <div>
                <label for="localisation">Localisation :</label>
                <input type="text" id="localisation" name="localisation">
            </div>
            <div>
                <label for="interets">Intérêts (séparés par des virgules) :</label>
                <input type="text" id="interets" name="interets">
            </div>
            <div>
                <label for="caracteristiques">Caractéristiques (séparées par des virgules) :</label>
                <input type="text" id="caracteristiques" name="caracteristiques">
            </div>
            <div>
                <button type="submit" name="add_chat">Ajouter le profil du chat</button>
            </div>
        </form>

        <hr>

        <h3>Tous les profils de chats</h3>
        <?php if (!empty($chats)): ?>
            <?php foreach ($chats as $chat): ?>
                <div class="chat-profile">
                    <h3><?php echo htmlspecialchars($chat['nom']); ?> (ID: <?php echo htmlspecialchars($chat['id']); ?>)</h3>
                    <p><strong>Numéro de puce :</strong> <?php echo htmlspecialchars($chat['numero_puce'] ?? 'N/A'); ?></p>
                    <p><strong>Âge :</strong> <?php echo htmlspecialchars($chat['age'] ?? 'N/A'); ?> ans</p>
                    <p><strong>Sexe :</strong> <?php echo htmlspecialchars($chat['sexe'] ?? 'N/A'); ?></p>
                    <p><strong>Localisation :</strong> <?php echo htmlspecialchars($chat['localisation'] ?? 'N/A'); ?></p>
                    <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($chat['description'] ?? 'N/A')); ?></p>
                    <p><strong>Intérêts :</strong> <?php echo htmlspecialchars($chat['interets'] ?? 'N/A'); ?></p>
                    <p><strong>Caractéristiques :</strong> <?php echo htmlspecialchars($chat['caracteristiques'] ?? 'N/A'); ?></p>
                    <?php if (!empty($chat['photo'])): ?>
                        <p><img src="<?php echo htmlspecialchars($chat['photo']); ?>" alt="Photo de <?php echo htmlspecialchars($chat['nom']); ?>" style="max-width: 150px; height: auto;"></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun profil de chat trouvé pour le moment. Ajoutez-en un !</p>
        <?php endif; ?>
    </div>
</body>
</html>