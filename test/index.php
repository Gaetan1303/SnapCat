<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapCat - Tableau de Bord</title>
    <style>
        bod
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 900px; margin: auto; padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        h2, h3 { color: #007bff; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .logout-btn { background-color: #dc3545; color: white; padding: 8px 15px; border: none; border-radius: 5px; text-decoration: none; font-size: 0.9em; }
        .logout-btn:hover { background-color: #c82333; }
        .nav-buttons { margin-top: 20px; text-align: center; }
        .nav-buttons a { background-color: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin: 0 10px; font-weight: bold; }
        .nav-buttons a:hover { background-color: #218838; }
        form div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"], input[type="number"], textarea, select { width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        input[type="file"] { width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; background-color: #e9ecef; }
        button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; }
        button:hover { background-color: #0056b3; }
        .chat-profile { border: 1px solid #e0e0e0; padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: #fafafa; position: relative; }
        .chat-profile h3 { margin-top: 0; color: #333; }
        .chat-profile p { margin: 5px 0; font-size: 0.95em; color: #666; }
        .profile-actions { margin-top: 10px; }
        .profile-actions button { margin-right: 10px; }
        .edit-btn { background-color: #ffc107; color: #333; }
        .edit-btn:hover { background-color: #e0a800; }
        .delete-btn { background-color: #dc3545; }
        .delete-btn:hover { background-color: #c82333; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .message p { margin: 0; }
        .message p[style*="green"] { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .message p[style*="red"] { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <h2>Bienvenue sur SnapCat<?php echo htmlspecialchars($_SESSION['users']['username']); ?>!</h2>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>

        <div class="nav-buttons">
            <a href="discover.php">Découvrir des chats</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <h3>Ajouter un nouveau profil de chat</h3>
        <form action="index.php" method="POST" enctype="multipart/form-data">
            <div>
                <label for="nom">Nom du chat * :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div>
                <label for="numero_puce">Numéro de puce :</label>
                <input type="text" id="numero_puce" name="numero_puce">
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
                <label for="photo">Photo du chat :</label>
                <input type="file" id="photo" name="photo" accept="image/*">
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

        <h3>Tous les profils de chats enregistrés</h3>
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
                    <p><strong>Caractéristiques :</strong> <?php htmlspecialchars($chat['caracteristiques'] ?? 'N/A'); ?></p>
                    <?php if (!empty($chat['photo'])): ?>
                        <p><img src="<?php echo htmlspecialchars($chat['photo']); ?>" alt="Photo de <?php echo htmlspecialchars($chat['nom']); ?>" style="max-width: 150px; height: auto; border-radius: 5px; margin-top: 10px;"></p>
                    <?php else: ?>
                        <p>Pas de photo disponible</p>
                    <?php endif; ?>

                    <div class="profile-actions">
                        <form action="edit_chat.php" method="GET" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($chat['id']); ?>">
                            <button type="submit" class="edit-btn">Modifier</button>
                        </form>
                        <form action="index.php" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce profil ?');">
                            <input type="hidden" name="chat_id" value="<?php echo htmlspecialchars($chat['id']); ?>">
                            <button type="submit" name="delete_chat" class="delete-btn">Supprimer</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun profil de chat trouvé pour le moment. Ajoutez-en un !</p>
        <?php endif; ?>
    </div>
</body>
</html>