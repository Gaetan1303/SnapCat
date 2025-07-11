<?php

// Démarrage de la session PHP
session_start();

// Vérification de l'authentification de l'utilisateur
// Si l'utilisateur n'est pas connecté, redirigez-le vers la page de login
if (!isset($_SESSION['users'])) { // Utilise 'user_id' pour la cohérence avec login.php ############ modifié user comme lautre login !!
    header('Location: login.php');
    exit;
}

// Inclusion de la classe ChatModel et db.php
require_once 'db.php'; // Pour que $conn soit disponible
require_once 'ChatModel.php';

// Instanciation du modèle de chat pour interagir avec la base de données
$chatModel = null; // Initialise à null
try {
    if ($conn instanceof PDO) { // Vérifie que $conn est bien un objet PDO
        $chatModel = new ChatModel($conn);
    } else {
        throw new Exception("Connexion à la base de données non valide.");
    }
} catch (Exception $e) { // Capturer l'exception si la connexion ou la création de table échoue
    error_log("Erreur critique lors de l'instanciation de ChatModel: " . $e->getMessage());
    // Gérer l'erreur de manière plus douce pour l'utilisateur en production
    die("Désolé, une erreur est survenue lors du chargement des fonctionnalités de chat. Veuillez réessayer plus tard.");
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

    // --- NOUVEAU : Récupération de l'URL de la photo ---
    $photoUrl = filter_input(INPUT_POST, 'photo_url', FILTER_VALIDATE_URL) ? $_POST['photo_url'] : '';
    // Si l'URL n'est pas valide ou vide, on utilise une chaîne vide.
    // Pour un upload réel, il faudrait utiliser $_FILES et un traitement spécifique.

    // Validation minimale
    if (empty($nom) || empty($numeroPuce) || empty($age) || empty($sexe) || empty($localisation)) {
        $message = '<p class="error-message">Veuillez remplir au moins les champs obligatoires (Nom, Numéro de Puce, Âge, Sexe, Localisation).</p>';
    } else {
        // Ajout du profil de chat via le modèle
        if ($chatModel) {
            $added = $chatModel->addChatProfile(
                $nom, $numeroPuce, (int)$age, $sexe, $photoUrl, // --- Passer $photoUrl ici ---
                $description, $localisation, $interets, $caracteristiques
            );

            if ($added) {
                $message = '<p class="success-message">Profil de ' . htmlspecialchars($nom) . ' ajouté avec succès !</p>';
                header('Location: index.php?status=success_add');
                exit;
            } else {
                $message = '<p class="error-message">Erreur lors de l\'ajout du profil de ' . htmlspecialchars($nom) . '. Le numéro de puce est peut-être déjà utilisé ou une erreur est survenue.</p>';
            }
        } else {
            $message = '<p class="error-message">Erreur interne: Le modèle de chat n\'a pas pu être initialisé.</p>';
        }
    }
}

// Gérer le message de succès après redirection
if (isset($_GET['status']) && $_GET['status'] === 'success_add') {
    $message = '<p class="success-message">Profil de chat ajouté avec succès !</p>';
}


// Récupération de tous les profils de chats pour l'affichage
$chats = [];
if ($chatModel) {
    $chats = $chatModel->getAllChatProfiles();
}

// Nom de l'utilisateur connecté pour l'affichage
$username = $_SESSION['user'] ?? 'Invité';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapCat - Accueil</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50; /* Vert pour l'action */
            --secondary-color: #007bff; /* Bleu pour les liens/boutons secondaires */
            --text-color: #333;
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --danger-color: #dc3545;
            --success-color: #28a745;
        }

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }

        header {
            background-color: var(--card-bg);
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 4px var(--shadow-light);
            text-align: center;
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Permet aux éléments de passer à la ligne sur mobile */
        }

        header h1 {
            margin: 0;
            color: var(--primary-color);
            font-size: 1.8em;
            flex-grow: 1; /* Permet au titre de prendre de la place */
            text-align: left;
        }

        header .user-info {
            font-size: 1em;
            color: #666;
            margin-right: 20px;
            white-space: nowrap; /* Empêche le texte de passer à la ligne */
        }

        header nav a {
            text-decoration: none;
            color: var(--secondary-color);
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            white-space: nowrap;
        }

        header nav a:hover {
            background-color: #e9ecef;
        }

        .message-area {
            margin: 20px auto;
            width: 90%;
            max-width: 1200px;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 1em;
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: var(--success-color);
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: var(--danger-color);
            border: 1px solid #f5c6cb;
        }

        main {
            flex-grow: 1; /* Permet au contenu principal de prendre tout l'espace disponible */
        }

        .section-title {
            text-align: center;
            color: var(--primary-color);
            margin-top: 30px;
            margin-bottom: 20px;
            font-size: 2em;
        }

        .form-section, .chat-list-section {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px var(--shadow-light);
            margin-bottom: 30px;
        }

        .form-section form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
            font-size: 0.95em;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="url"], /* NOUVEAU: Type pour l'URL de la photo */
        .form-group select,
        .form-group textarea {
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1em;
            width: 100%;
            box-sizing: border-box; /* Inclut le padding dans la largeur */
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-actions {
            grid-column: 1 / -1; /* Prend toute la largeur de la grille */
            text-align: center;
            margin-top: 15px;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #45a049;
        }

        .chat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .chat-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px var(--shadow-light);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
        }

        .chat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .chat-card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 15px;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
        }

        .chat-card-body {
            padding: 15px;
            flex-grow: 1;
        }

        .chat-card-body p {
            margin-bottom: 8px;
            font-size: 0.95em;
        }

        .chat-card-body strong {
            color: #555;
        }

        .chat-card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background-color: #f0f0f0; /* Placeholder couleur */
            border-bottom: 1px solid var(--border-color);
        }
        
        .chat-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block; /* Supprime l'espace sous l'image */
        }

        footer {
            background-color: var(--text-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            font-size: 0.9em;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                align-items: flex-start; /* Alignement à gauche sur mobile */
            }
            header h1 {
                text-align: center;
                width: 100%;
                margin-bottom: 10px;
            }
            header .user-info {
                width: 100%;
                text-align: center;
                margin-bottom: 10px;
                margin-right: 0;
            }
            header nav {
                width: 100%;
                display: flex;
                justify-content: center;
                gap: 10px;
            }
            header nav a {
                flex-grow: 1;
                text-align: center;
            }

            .form-section form {
                grid-template-columns: 1fr; /* Une seule colonne sur mobile */
            }
        }

        @media (max-width: 480px) {
            .container {
                margin: 10px auto;
            }
            .section-title {
                font-size: 1.6em;
            }
            .form-section, .chat-list-section {
                padding: 15px;
            }
            .btn-submit {
                font-size: 1em;
                padding: 10px 20px;
            }
            .chat-card-header {
                font-size: 1.1em;
                padding: 10px;
            }
            .chat-card-body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>SnapCat</h1>
            <div class="user-info">Bienvenue, <?= htmlspecialchars($username) ?> !</div>
            <nav>
                <a href="logout.php">Déconnexion</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if (!empty($message)): ?>
            <div class="message-area">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <section class="form-section">
            <h2 class="section-title">Ajouter un nouveau profil de chat</h2>
            <form action="index.php" method="POST">
                <div class="form-group">
                    <label for="nom">Nom : <span style="color: red;">*</span></label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="numero_puce">Numéro de puce : <span style="color: red;">*</span></label>
                    <input type="text" id="numero_puce" name="numero_puce" required>
                </div>
                <div class="form-group">
                    <label for="age">Âge (années) : <span style="color: red;">*</span></label>
                    <input type="number" id="age" name="age" min="0" required>
                </div>
                <div class="form-group">
                    <label for="sexe">Sexe : <span style="color: red;">*</span></label>
                    <select id="sexe" name="sexe" required>
                        <option value="">Sélectionner</option>
                        <option value="Mâle">Mâle</option>
                        <option value="Femelle">Femelle</option>
                        <option value="Inconnu">Inconnu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="localisation">Localisation : <span style="color: red;">*</span></label>
                    <input type="text" id="localisation" name="localisation" required>
                </div>
                <div class="form-group">
                    <label for="photo_url">URL de la photo :</label>
                    <input type="url" id="photo_url" name="photo_url" placeholder="https://exemple.com/photo.jpg">
                </div>
                <div class="form-group">
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="interets">Intérêts :</label>
                    <input type="text" id="interets" name="interets" placeholder="Ex: Jouer, dormir, câlins">
                </div>
                <div class="form-group">
                    <label for="caracteristiques">Caractéristiques :</label>
                    <input type="text" id="caracteristiques" name="caracteristiques" placeholder="Ex: Poil long, yeux bleus">
                </div>
                <div class="form-actions">
                    <button type="submit" name="add_chat" class="btn-submit">Ajouter le Chat</button>
                </div>
            </form>
        </section>

        <section class="chat-list-section">
            <h2 class="section-title">Nos amis les chats</h2>
            <?php if (empty($chats)): ?>
                <p style="text-align: center; font-style: italic; color: #777;">Aucun profil de chat trouvé pour le moment. Ajoutez-en un !</p>
            <?php else: ?>
                <div class="chat-grid">
                    <?php foreach ($chats as $chat): ?>
                        <div class="chat-card">
                            <div class="chat-card-header">
                                <?= htmlspecialchars($chat['nom']) ?> (<?= htmlspecialchars($chat['sexe']) ?>)
                            </div>
                            <div class="chat-card-img">
                                <?php if (!empty($chat['photo_url'])): ?>
                                    <img src="<?= htmlspecialchars($chat['photo_url']) ?>" alt="Photo de <?= htmlspecialchars($chat['nom']) ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/200x200?text=Pas+de+photo" alt="Pas de photo de chat">
                                <?php endif; ?>
                            </div>
                            <div class="chat-card-body">
                                <p><strong>Âge :</strong> <?= htmlspecialchars($chat['age']) ?> ans</p>
                                <p><strong>Puce :</strong> <?= htmlspecialchars($chat['numero_puce']) ?></p>
                                <p><strong>Localisation :</strong> <?= htmlspecialchars($chat['localisation']) ?></p>
                                <?php if (!empty($chat['description'])): ?>
                                    <p><strong>Description :</strong> <?= nl2br(htmlspecialchars($chat['description'])) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($chat['interets'])): ?>
                                    <p><strong>Intérêts :</strong> <?= htmlspecialchars($chat['interets']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($chat['caracteristiques'])): ?>
                                    <p><strong>Caractéristiques :</strong> <?= htmlspecialchars($chat['caracteristiques']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> SnapCat. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>