<?php

session_start();

if (!isset($_SESSION['users'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';
require_once 'ChatModel.php';

try {
    if ($conn instanceof PDO) {
        $chatModel = new ChatModel($conn);
    } else {
        throw new Exception("Connexion à la base de données non valide.");
    }
} catch (Exception $e) {
    error_log("Erreur critique lors de l'instanciation de ChatModel: " . $e->getMessage());
    die("Désolé, une erreur est survenue. Veuillez réessayer plus tard.");
}

$chat = null;
$message = '';

// Vérifier si un ID est fourni pour la modification
if (isset($_GET['id'])) {
    $chat_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $chat = $chatModel->getChatProfileById((int)$chat_id);

    if (!$chat) {
        $message = '<p style="color: red;">Profil de chat non trouvé.</p>';
    }
} else {
    $message = '<p style="color: red;">ID de chat manquant pour la modification.</p>';
}

// Gérer la soumission du formulaire pour la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_chat'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $numeroPuce = filter_input(INPUT_POST, 'numero_puce', FILTER_SANITIZE_STRING);
    $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
    $sexe = filter_input(INPUT_POST, 'sexe', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $localisation = filter_input(INPUT_POST, 'localisation', FILTER_SANITIZE_STRING);
    $interets = filter_input(INPUT_POST, 'interets', FILTER_SANITIZE_STRING);
    $caracteristiques = filter_input(INPUT_POST, 'caracteristiques', FILTER_SANITIZE_STRING);
    $photo = filter_input(INPUT_POST, 'photo', FILTER_SANITIZE_STRING); // Supposant que l'URL de la photo est modifiée directement ou conservée

    if (empty($nom) || empty($id)) {
        $message = '<p style="color: red;">Le nom du chat et l\'ID sont requis pour la modification.</p>';
    } else {
        $updated = $chatModel->updateChatProfile(
            (int)$id, $nom, $numeroPuce, (int)$age, $sexe, $photo,
            $description, $localisation, $interets, $caracteristiques
        );

        if ($updated) {
            $message = '<p style="color: green;">Profil de ' . htmlspecialchars($nom) . ' mis à jour avec succès !</p>';
            // Récupérer à nouveau les données du chat mises à jour
            $chat = $chatModel->getChatProfileById((int)$id);
        } else {
            $message = '<p style="color: red;">Erreur lors de la mise à jour du profil de ' . htmlspecialchars($nom) . '. Le numéro de puce est peut-être déjà utilisé ou une erreur est survenue.</p>';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapCat - Modifier Profil de Chat</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 800px; margin: auto; padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        h2 { color: #007bff; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        form div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"], input[type="number"], textarea, select { width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; }
        button:hover { background-color: #0056b3; }
        .back-link { display: inline-block; margin-top: 20px; text-decoration: none; color: #007bff; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .message p { margin: 0; }
        .message p[style*="green"] { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .message p[style*="red"] { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier le profil du chat</h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($chat): ?>
            <form action="edit_chat.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($chat['id']); ?>">
                <div>
                    <label for="nom">Nom du chat * :</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($chat['nom'] ?? ''); ?>" required>
                </div>
                <div>
                    <label for="numero_puce">Numéro de puce :</label>
                    <input type="text" id="numero_puce" name="numero_puce" value="<?php echo htmlspecialchars($chat['numero_puce'] ?? ''); ?>">
                </div>
                <div>
                    <label for="age">Âge :</label>
                    <input type="number" id="age" name="age" min="0" value="<?php echo htmlspecialchars($chat['age'] ?? ''); ?>">
                </div>
                <div>
                    <label for="sexe">Sexe :</label>
                    <select id="sexe" name="sexe">
                        <option value="">Sélectionner</option>
                        <option value="Mâle" <?php echo (isset($chat['sexe']) && $chat['sexe'] == 'Mâle') ? 'selected' : ''; ?>>Mâle</option>
                        <option value="Femelle" <?php echo (isset($chat['sexe']) && $chat['sexe'] == 'Femelle') ? 'selected' : ''; ?>>Femelle</option>
                    </select>
                </div>
                <div>
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($chat['description'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label for="localisation">Localisation :</label>
                    <input type="text" id="localisation" name="localisation" value="<?php echo htmlspecialchars($chat['localisation'] ?? ''); ?>">
                </div>
                <div>
                    <label for="interets">Intérêts (séparés par des virgules) :</label>
                    <input type="text" id="interets" name="interets" value="<?php echo htmlspecialchars($chat['interets'] ?? ''); ?>">
                </div>
                <div>
                    <label for="caracteristiques">Caractéristiques (séparées par des virgules) :</label>
                    <input type="text" id="caracteristiques" name="caracteristiques" value="<?php echo htmlspecialchars($chat['caracteristiques'] ?? ''); ?>">
                </div>
                <div>
                    <label for="photo">URL de la Photo (laissez vide si pas de changement) :</label>
                    <input type="text" id="photo" name="photo" value="<?php echo htmlspecialchars($chat['photo'] ?? ''); ?>">
                    <?php if (!empty($chat['photo'])): ?>
                        <p><img src="<?php echo htmlspecialchars($chat['photo']); ?>" alt="Photo actuelle" style="max-width: 100px; height: auto; margin-top: 10px;"></p>
                    <?php endif; ?>
                </div>
                <div>
                    <button type="submit" name="update_chat">Mettre à jour le profil</button>
                </div>
            </form>
        <?php endif; ?>

        <a href="index.php" class="back-link">Retour au Tableau de Bord</a>
    </div>
</body>
</html>