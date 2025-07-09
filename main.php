<?php
session_start(); // Démarre la session pour gérer l'état de l'utilisateur

// --- DÉBOGAGE TEMPORAIRE : À RETIRER EN PRODUCTION ---
ini_set('display_errors', 1); // Affiche les erreurs directement dans le navigateur
ini_set('display_startup_errors', 1); // Affiche les erreurs de démarrage
error_reporting(E_ALL);      // Rapporte tous les types d'erreurs PHP
// -----------------------------------------------------

// Vérifie si l'utilisateur est connecté. Si non, redirige vers la page de connexion.
// Nous utilisons 'user_id' stocké en session après une connexion réussie.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Assurez-vous d'avoir une page login.php
    exit;
}

// Inclut le fichier de connexion à la base de données.
// Assurez-vous que 'bd.php' est le bon chemin vers votre fichier de connexion PDO.
require_once 'bd.php';

// Vérifie si l'objet de connexion PDO est valide après l'inclusion de bd.php
if (!isset($conn) || !$conn instanceof PDO) {
    // Si la connexion n'est pas établie ou n'est pas un objet PDO, affiche un message d'erreur fatal.
    die("Erreur fatale : La connexion à la base de données n'a pas pu être établie via bd.php. Vérifiez votre fichier bd.php et la disponibilité de MySQL.");
}

$message = ''; // Variable pour stocker les messages de succès ou d'erreur
$message_type = ''; // 'success' ou 'error'

// Gère la soumission du formulaire pour ajouter un profil de chat et un snap
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère l'ID de l'utilisateur actuel depuis la session
    $current_user_id = $_SESSION['user_id'];

    // Récupère et nettoie les données du formulaire pour le profil du chat
    $nom = htmlspecialchars($_POST['nom'] ?? '');
    $numero_puce = htmlspecialchars($_POST['numero_puce'] ?? '');
    $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
    $sexe = htmlspecialchars($_POST['sexe'] ?? '');
    $description = htmlspecialchars($_POST['description'] ?? '');
    $localisation = htmlspecialchars($_POST['localisation'] ?? '');
    $interets = htmlspecialchars($_POST['interets'] ?? '');
    $caracteristiques = htmlspecialchars($_POST['caracteristiques'] ?? '');
    $caption = htmlspecialchars($_POST['caption'] ?? ''); // Légende pour le snap

    $photoPath = null; // Initialise le chemin de la photo

    // Gère le téléchargement de la photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $target_dir = 'uploads/cats/'; // Dossier spécifique pour les photos de chats

        // Crée le répertoire s'il n'existe pas
        if (!is_dir($target_dir)) {
            if (!@mkdir($target_dir, 0777, true)) {
                $message = "Erreur : Impossible de créer le répertoire de téléchargement pour les photos de chats. Vérifiez les permissions.";
                $message_type = 'error';
                error_log("Erreur mkdir dans main.php: Impossible de créer " . $target_dir);
            }
        }

        // Si le répertoire a été créé ou existe et qu'il n'y a pas eu d'erreur précédente
        if ($message_type === '') {
            $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $new_file_name = uniqid('cat_', true) . '.' . $file_extension;
                $target_file = $target_dir . $new_file_name;

                $check = getimagesize($_FILES['photo']['tmp_name']);
                if ($check !== false) {
                    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                        $message = "Erreur lors du déplacement du fichier téléchargé.";
                        $message_type = 'error';
                        error_log("Erreur move_uploaded_file dans main.php: " . $_FILES['photo']['error']);
                    } else {
                        $photoPath = $target_file;
                    }
                } else {
                    $message = "Le fichier téléchargé n'est pas une image valide.";
                    $message_type = 'error';
                }
            } else {
                $message = "Type de fichier image invalide (acceptés : jpg, jpeg, png, gif).";
                $message_type = 'error';
            }
        }
    } else if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Gère les autres erreurs de téléchargement PHP si un fichier a été tenté d'être téléchargé
        $phpFileUploadErrors = array(
            UPLOAD_ERR_INI_SIZE   => "Le fichier téléchargé dépasse la directive upload_max_filesize dans php.ini.",
            UPLOAD_ERR_FORM_SIZE  => "Le fichier téléchargé dépasse la directive MAX_FILE_SIZE spécifiée dans le formulaire HTML.",
            UPLOAD_ERR_PARTIAL    => "Le fichier n'a été que partiellement téléchargé.",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
            UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier sur le disque.",
            UPLOAD_ERR_EXTENSION  => "Une extension PHP a arrêté le téléchargement du fichier."
        );
        $message = "Erreur lors du téléchargement de la photo : " . ($phpFileUploadErrors[$_FILES['photo']['error']] ?? "Erreur inconnue.");
        $message_type = 'error';
    }

    // Si aucune erreur jusqu'à présent, tente l'insertion en base de données
    if ($message_type === '') {
        try {
            // --- Insertion dans la table 'cats' (Profil du chat) ---
            $stmt = $conn->prepare("INSERT INTO cats (user_id, nom, numero_puce, age, sexe, photo, description, localisation, interets, caracteristiques) VALUES (:user_id, :nom, :numero_puce, :age, :sexe, :photo, :description, :localisation, :interets, :caracteristiques)");

            $stmt->bindParam(':user_id', $current_user_id);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':numero_puce', $numero_puce);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':sexe', $sexe);
            $stmt->bindParam(':photo', $photoPath);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':localisation', $localisation);
            $stmt->bindParam(':interets', $interets);
            $stmt->bindParam(':caracteristiques', $caracteristiques);

            if ($stmt->execute()) {
                $message .= "Profil de chat ajouté avec succès !<br>";
                $message_type = 'success';
            } else {
                $message .= "Erreur lors de l'enregistrement du profil de chat : " . implode(" - ", $stmt->errorInfo()) . "<br>";
                $message_type = 'error';
                error_log("Erreur d'exécution PDO (cats) dans main.php: " . implode(" - ", $stmt->errorInfo()));
            }

            // --- Insertion dans la table 'snapcat' (Snap) ---
            // Assurez-vous que la table 'snapcat' existe et que les colonnes sont correctes
            // sender_id : l'utilisateur connecté
            // receiver_id : Pour cet exemple, nous utiliserons l'ID de l'expéditeur lui-même,
            //               mais dans une vraie application, cela proviendrait de la sélection de l'utilisateur.
            // expiration_time : Définir une expiration (ex: 24 heures à partir de maintenant)
            $receiver_id = $current_user_id; // Adaptez ceci en fonction de la logique de votre application (ex: sélection d'un ami)
            $expiration_time = date('Y-m-d H:i:s', strtotime('+24 hours')); // Le snap expire dans 24 heures

            $stmt2 = $conn->prepare("INSERT INTO snapcat (sender_id, receiver_id, image_ID, message, expiration_time) VALUES (:sender_id, :receiver_id, :image_ID, :message, :expiration_time)");

            $stmt2->bindParam(':sender_id', $current_user_id);
            $stmt2->bindParam(':receiver_id', $receiver_id);
            $stmt2->bindParam(':image_ID', $photoPath); // Utilise le chemin de la photo du chat comme image_ID pour le snap
            $stmt2->bindParam(':message', $caption);    // Utilise la légende comme message
            $stmt2->bindParam(':expiration_time', $expiration_time);

            if ($stmt2->execute()) {
                $message .= "Snap envoyé avec succès !";
                if ($message_type !== 'error') $message_type = 'success'; // Ne pas écraser une erreur précédente
            } else {
                $message .= "Erreur lors de l'enregistrement du snap : " . implode(" - ", $stmt2->errorInfo());
                $message_type = 'error';
                error_log("Erreur d'exécution PDO (snapcat) dans main.php: " . implode(" - ", $stmt2->errorInfo()));
            }

        } catch (PDOException $e) {
            $message .= "Erreur de base de données lors de l'insertion : " . $e->getMessage();
            $message_type = 'error';
            error_log("Erreur PDO catch dans main.php: " . $e->getMessage());
        }
    }
}

// Fonction pour récupérer tous les profils de chats depuis la base de données pour l'affichage
function getAllCatProfiles($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM cats");
        $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Traite les intérêts et caractéristiques des chaînes séparées par des virgules en tableaux
        foreach ($cats as &$cat) {
            $cat['interets'] = explode(',', $cat['interets']);
            $cat['caracteristiques'] = explode(',', $cat['caracteristiques']);
        }
        return $cats;
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération des profils de chats: " . $e->getMessage());
        return [];
    }
}

$cats = getAllCatProfiles($conn); // Récupère les chats après une éventuelle insertion

// Fonction pour simuler une rencontre entre deux chats
function Rencontre($chat1, $chat2) {
    echo "<h3 class='text-xl font-semibold text-gray-800 mb-4'>Rencontre entre " . htmlspecialchars($chat1['nom']) . " et " . htmlspecialchars($chat2['nom']) . "!</h3>";
    $interetsCommuns = array_intersect($chat1['interets'], $chat2['interets']);
    if (!empty($interetsCommuns)) {
        echo "<p class='text-gray-700'>Ils ont des intérêts communs : " . htmlspecialchars(implode(', ', $interetsCommuns)) . ".</p>";
    } else {
        echo "<p class='text-gray-700'>Pas d'intérêts communs, mais ils peuvent toujours devenir amis !</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snapcat - Gestion des Profils de Chats</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 900px; /* Increased max-width for better layout */
            margin: 20px auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1, h2, h3 {
            color: #1a202c;
            margin-bottom: 20px;
            text-align: center;
        }
        form div {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #4a5568; /* Gray-700 */
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db; /* Gray-300 */
            border-radius: 5px;
            box-sizing: border-box;
            background-color: #f9fafb; /* Gray-50 */
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus,
        input[type="file"]:focus {
            border-color: #3b82f6; /* Blue-500 */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25); /* Blue-500 with alpha */
            outline: none;
        }
        button {
            background-color: #3b82f6; /* Blue-600 */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.1s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        button:hover {
            background-color: #2563eb; /* Blue-700 */
            transform: translateY(-1px);
        }
        button:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .cat-profile {
            border: 1px solid #e5e7eb; /* Gray-200 */
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            background-color: #f9fafb; /* Gray-50 */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex; /* Utilise flexbox pour la mise en page */
            flex-direction: column; /* Empile le contenu verticalement */
            align-items: center; /* Centre les éléments horizontalement */
            text-align: center;
        }
        .cat-profile img {
            max-width: 150px; /* Taille augmentée pour une meilleure visibilité */
            height: 150px; /* Hauteur fixe pour un affichage cohérent */
            object-fit: cover; /* Assure que l'image couvre la zone, en la recadrant si nécessaire */
            border-radius: 50%; /* Image circulaire */
            margin-bottom: 15px;
            border: 3px solid #3b82f6; /* Bordure bleue */
        }
        .cat-profile p {
            font-size: 0.95rem;
            color: #4a5568; /* Gris-700 */
            margin-bottom: 5px;
        }
        .cat-profile strong {
            color: #2d3748; /* Gris-800 */
        }
        .message-box {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border-width: 1px;
            font-weight: 500;
        }
        .message-success {
            background-color: #d1fae5; /* vert-100 */
            border-color: #34d399; /* vert-400 */
            color: #065f46; /* vert-700 */
        }
        .message-error {
            background-color: #fee2e2; /* rouge-100 */
            border-color: #ef4444; /* rouge-400 */
            color: #b91c1c; /* rouge-700 */
        }
        .nav-link {
            padding: 8px 16px;
            border-radius: 5px;
            transition: background-color 0.2s ease;
        }
        .nav-link:hover {
            background-color: #2563eb; /* Bleu-700 */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-4xl font-bold text-center text-gray-900 mb-8">Snapcat</h1>

        <nav class="mb-8 p-4 bg-blue-600 text-white rounded-lg shadow-md flex justify-between items-center">
            <div class="text-xl font-semibold">
                Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?>!
            </div>
            <div>
                <a href="main.php" class="nav-link">Accueil</a>
                <a href="profile.php" class="nav-link">Mon Profil</a>
                <a href="logout.php" class="bg-blue-700 hover:bg-blue-800 text-white py-2 px-4 rounded-md ml-4 transition duration-200">Déconnexion</a>
            </div>
        </nav>

        <?php if ($message): ?>
            <div class="message-box <?php echo ($message_type === 'success') ? 'message-success' : 'message-error'; ?>">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Ajouter un nouveau profil de chat et un Snap</h2>
        <form method="POST" enctype="multipart/form-data" class="bg-gray-50 p-6 rounded-lg shadow-inner mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nom">Nom du chat :</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div>
                    <label for="numero_puce">Numéro de puce :</label>
                    <input type="text" id="numero_puce" name="numero_puce">
                </div>
                <div>
                    <label for="age">Âge :</label>
                    <input type="number" id="age" name="age" required min="0">
                </div>
                <div>
                    <label for="sexe">Sexe :</label>
                    <select id="sexe" name="sexe" required>
                        <option value="M">Mâle</option>
                        <option value="F">Femelle</option>
                        <option value="Inconnu">Inconnu</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                <div>
                    <label for="localisation">Localisation :</label>
                    <input type="text" id="localisation" name="localisation">
                </div>
                <div>
                    <label for="interets">Intérêts (séparés par des virgules) :</label>
                    <input type="text" id="interets" name="interets" placeholder="ex: jouer,dormir,chasser">
                </div>
                <div class="md:col-span-2">
                    <label for="caracteristiques">Caractéristiques (séparées par des virgules) :</label>
                    <input type="text" id="caracteristiques" name="caracteristiques" placeholder="ex: poils courts,yeux verts">
                </div>
            </div>

            <div class="mt-6 border-t pt-6 border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 text-center">Détails de la photo et du Snap</h3>
                <div class="mb-5">
                    <label for="photo">Photo du chat (pour profil et Snap) :</label>
                    <input type="file" id="photo" name="photo" accept="image/*" required class="w-full text-gray-700 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>
                <div class="mb-6">
                    <label for="caption">Légende du Snap :</label>
                    <input type="text" id="caption" name="caption" placeholder="Ajoutez une légende pour le snap">
                </div>
            </div>

            <button type="submit" class="w-full mt-4">
                Ajouter le chat et le Snap
            </button>
        </form>

        <h2 class="text-2xl font-bold text-center text-gray-800 mt-10 mb-6">Profils de chats enregistrés :</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($cats)): ?>
                <p class="col-span-full text-center text-gray-600">Aucun profil de chat enregistré pour le moment.</p>
            <?php else: ?>
                <?php foreach ($cats as $profil): ?>
                    <div class="cat-profile">
                        <img src="<?php echo htmlspecialchars($profil['photo'] ?: 'https://placehold.co/150x150/e2e8f0/64748b?text=Pas+de+Photo'); ?>" alt="Photo de <?php echo htmlspecialchars($profil['nom']); ?>">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($profil['nom']); ?></h3>
                        <p><strong>Numéro de puce :</strong> <?php echo htmlspecialchars($profil['numero_puce'] ?? 'N/A'); ?></p>
                        <p><strong>Âge :</strong> <?php echo htmlspecialchars($profil['age'] ?? 'N/A'); ?> ans</p>
                        <p><strong>Sexe :</strong> <?php echo htmlspecialchars($profil['sexe'] ?? 'N/A'); ?></p>
                        <p><strong>Localisation :</strong> <?php echo htmlspecialchars($profil['localisation'] ?? 'N/A'); ?></p>
                        <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($profil['description'] ?? 'N/A')); ?></p>
                        <p><strong>Intérêts :</strong> <?php echo htmlspecialchars(implode(', ', $profil['interets'] ?? [])); ?></p>
                        <p><strong>Caractéristiques :</strong> <?php echo htmlspecialchars(implode(', ', $profil['caracteristiques'] ?? [])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h2 class="text-2xl font-bold text-center text-gray-800 mt-10 mb-6">Simulations de Rencontres</h2>
        <?php
        // Simule une rencontre entre deux chats si au moins deux profils existent
        if (count($cats) >= 2) {
            Rencontre($cats[0], $cats[1]);
        } else {
            echo "<p class='text-center text-gray-600'>Pas assez de chats pour une rencontre.</p>";
        }
        ?>
    </div>
</body>
</html>
