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

$chats = $chatModel->getAllChatProfiles(); // Récupérer tous les chats à afficher dans le carrousel

$message = '';

// Simuler l'action "j'aime" ou "je n'aime pas"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['like']) || isset($_POST['dislike']))) {
    $chat_id = filter_input(INPUT_POST, 'chat_id', FILTER_SANITIZE_NUMBER_INT);
    if ($chat_id) {
        $chat_name = htmlspecialchars($chatModel->getChatProfileById((int)$chat_id)['nom'] ?? 'ce chat');
        if (isset($_POST['like'])) {
            // Dans une application réelle, vous stockeriez ce "like" dans une table `user_likes` :
            // $chatModel->recordLike($_SESSION['user_id'], (int)$chat_id);
            $message = "<p style='color: green;'>Vous avez aimé $chat_name !</p>";
        } else { // dislike
            // Dans une application réelle, vous pourriez enregistrer un "je n'aime pas" ou simplement ne rien faire
            $message = "<p style='color: blue;'>Vous n'avez pas aimé $chat_name.</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapCat - Découvrir</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; color: #333; display: flex; flex-direction: column; min-height: 95vh; justify-content: center; align-items: center; }
        .container { max-width: 500px; width: 100%; padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); text-align: center; }
        h2 { color: #007bff; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .carousel-container { position: relative; width: 100%; height: 400px; overflow: hidden; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .carousel-item { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; background-color: #fff; transition: opacity 0.5s ease-in-out; opacity: 0; visibility: hidden; }
        .carousel-item.active { opacity: 1; visibility: visible; }
        .carousel-item img { max-width: 90%; max-height: 250px; object-fit: cover; border-radius: 8px; margin-bottom: 15px; }
        .carousel-item h3 { margin-bottom: 5px; color: #333; }
        .carousel-item p { font-size: 0.9em; color: #666; margin: 2px 0; }
        .carousel-controls { display: flex; justify-content: center; gap: 20px; margin-top: 20px; }
        .carousel-controls button { padding: 12px 25px; border: none; border-radius: 30px; cursor: pointer; font-size: 1.1em; font-weight: bold; transition: background-color 0.3s ease; color: white; }
        .like-btn { background-color: #28a745; }
        .like-btn:hover { background-color: #218838; }
        .dislike-btn { background-color: #dc3545; }
        .dislike-btn:hover { background-color: #c82333; }
        .back-link { display: inline-block; margin-top: 30px; text-decoration: none; color: #007bff; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
        .message { padding: 10px; margin-top: 20px; border-radius: 5px; font-weight: bold; width: 100%; max-width: 500px; box-sizing: border-box;}
        .message p { margin: 0; }
        .message p[style*="green"] { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .message p[style*="blue"] { background-color: #cfe2ff; border-color: #b6d4fe; color: #0a58ca; }

        /* Style pour la section Activités/Rencontres */
        .info-section { margin-top: 40px; padding: 20px; border-top: 1px solid #eee; text-align: left; }
        .info-section h3 { color: #007bff; margin-bottom: 15px; }
        .info-section ul { list-style: none; padding: 0; }
        .info-section li { background-color: #e9f7ef; padding: 10px 15px; border-left: 5px solid #28a745; margin-bottom: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Découvrir de nouveaux amis félins !</h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!empty($chats)): ?>
            <div class="carousel-container">
                <?php foreach ($chats as $index => $chat): ?>
                    <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>" data-chat-id="<?php echo htmlspecialchars($chat['id']); ?>">
                        <?php if (!empty($chat['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($chat['photo']); ?>" alt="Photo de <?php echo htmlspecialchars($chat['nom']); ?>">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($chat['nom']); ?></h3>
                        <p><strong>Âge :</strong> <?php echo htmlspecialchars($chat['age'] ?? 'N/A'); ?> ans</p>
                        <p><strong>Sexe :</strong> <?php echo htmlspecialchars($chat['sexe'] ?? 'N/A'); ?></p>
                        <p><strong>Localisation :</strong> <?php echo htmlspecialchars($chat['localisation'] ?? 'N/A'); ?></p>
                        <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($chat['description'] ?? 'N/A')); ?></p>
                        <p><strong>Intérêts :</strong> <?php echo htmlspecialchars($chat['interets'] ?? 'N/A'); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="carousel-controls">
                <form action="discover.php" method="POST" style="display: inline;" class="interact-form">
                    <input type="hidden" name="chat_id" class="current-chat-id">
                    <button type="submit" name="dislike" class="dislike-btn">Passer</button>
                </form>
                <form action="discover.php" method="POST" style="display: inline;" class="interact-form">
                    <input type="hidden" name="chat_id" class="current-chat-id">
                    <button type="submit" name="like" class="like-btn">J'aime !</button>
                </form>
            </div>
        <?php else: ?>
            <p>Aucun profil de chat disponible pour la découverte pour le moment. Ajoutez-en via le tableau de bord !</p>
        <?php endif; ?>

        <div class="info-section">
            <h3>Idées de Rencontres et Activités avec votre nouveau Chat !</h3>
            <ul>
                <li>Organisez une séance de jeu avec des jouets à plumes.</li>
                <li>Créez un parcours d'obstacles avec des boîtes en carton.</li>
                <li>Initiez-le à une nouvelle cachette confortable pour la sieste.</li>
                <li>Brossage et caresses pour renforcer les liens.</li>
                <li>Séance de photos "SnapCat" pour immortaliser vos moments !</li>
                <li>Découverte de nouveaux sons relaxants (musique douce pour chats).</li>
            </ul>
        </div>

        <a href="index.php" class="back-link">Retour au Tableau de Bord</a>
    </div>

    <script>
        const carouselItems = document.querySelectorAll('.carousel-item');
        const currentChatIdInputs = document.querySelectorAll('.current-chat-id');
        let currentIndex = 0;

        function showSlide(index) {
            carouselItems.forEach((item, i) => {
                if (i === index) {
                    item.classList.add('active');
                    currentChatIdInputs.forEach(input => {
                        input.value = item.dataset.chatId;
                    });
                } else {
                    item.classList.remove('active');
                }
            });
        }

        // Initialiser le carrousel
        if (carouselItems.length > 0) {
            showSlide(currentIndex);
        } else {
            // Cacher les contrôles s'il n'y a pas de chats disponibles
            document.querySelector('.carousel-controls').style.display = 'none';
        }

        document.querySelectorAll('.carousel-controls button').forEach(button => {
            button.addEventListener('click', (event) => {
                event.preventDefault(); // Empêcher la soumission immédiate du formulaire

                const form = event.target.closest('form');
                const nextIndex = (currentIndex + 1) % carouselItems.length;

                // Soumettre le formulaire
                form.submit();

                // Passer à la diapositive suivante seulement après une action "j'aime" ou "je n'aime pas" réussie
                // (Dans une vraie application, cela se produirait après une réponse de requête AJAX)
                if (carouselItems.length > 1) { // Avancer seulement s'il y a plus d'un chat
                    currentIndex = nextIndex;
                    setTimeout(() => { // Petit délai pour permettre l'affichage du message
                         showSlide(currentIndex);
                         // Effacer le message après l'affichage de la diapositive suivante
                         const messageDiv = document.querySelector('.message');
                         if (messageDiv) messageDiv.innerHTML = '';
                    }, 1000); // Ajuster le délai si nécessaire
                }
            });
        });

    </script>
</body>
</html>