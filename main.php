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

$message = ''; // Variable pour stocker les messages d'erreur ou de succès
$message_type = ''; // 'success' ou 'error'

// Traitement du formulaire d'ajout de chat et de snap
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère l'ID de l'utilisateur connecté depuis la session
    $current_user_id = $_SESSION['user_id'];

    // Vérifie que tous les champs nécessaires pour le chat existent
    $required_chat_fields = ['nom', 'numero_puce', 'age', 'sexe', 'description', 'localisation', 'interets', 'caracteristiques'];
    foreach ($required_chat_fields as $f) {
        if (!isset($_POST[$f])) {
            $message = "Champ manquant pour le profil du chat : " . $f;
            $message_type = 'error';
            break; // Sort de la boucle si un champ est manquant
        }
    }

    // Vérifie que le champ 'caption' pour le snap existe
    if (!isset($_POST['caption'])) {
        if ($message_type === '') { // N'ajoute pas d'erreur si une autre est déjà présente
            $message = "Champ 'Légende (Snap)' manquant.";
            $message_type = 'error';
        }
    }

    // Procède si aucune erreur de champ manquant n'a été détectée
    if ($message_type === '') {
        $nom = htmlspecialchars($_POST['nom']);
        $numero_puce = htmlspecialchars($_POST['numero_puce']);
        $age = (int)$_POST['age'];
        $sexe = htmlspecialchars($_POST['sexe']);
        $description = htmlspecialchars($_POST['description']);
        $localisation = htmlspecialchars($_POST['localisation']);
        // Convertit les intérêts et caractéristiques en chaînes séparées par des virgules pour la BD
        $interets = htmlspecialchars($_POST['interets']);
        $caracteristiques = htmlspecialchars($_POST['caracteristiques']);
        $caption = htmlspecialchars($_POST['caption']); // Légende pour le snap

        $photoPath = null; // Initialise le chemin de la photo

        // Gestion de l'upload de la photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $target_dir = 'uploads/cats/'; // Dossier spécifique pour les photos de chats
                if (!is_dir($target_dir)) {
                    if (!@mkdir($target_dir, 0777, true)) {
                        $message = "Erreur : Impossible de créer le dossier de téléchargement des photos de chats. Vérifiez les permissions.";
                        $message_type = 'error';
                        error_log("Erreur mkdir dans main.php: Impossible de créer " . $target_dir);
                    }
                }

                if ($message_type === '') { // Procède si le dossier a été créé ou existe
                    $photoPath = $target_dir . uniqid('cat_', true) . '.' . $ext;
                    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                        $message = "Erreur lors du déplacement du fichier uploadé.";
                        $message_type = 'error';
                        error_log("Erreur move_uploaded_file dans main.php: " . $_FILES['photo']['error']);
                    }
                }
            } else {
                $message = "Ce fichier n'est pas une image valide (formats acceptés : jpg, jpeg, png, gif).";
                $message_type = 'error';
            }
        } else {
            // Gère les erreurs d'upload PHP
            $phpFileUploadErrors = array(
                UPLOAD_ERR_OK         => "Aucune erreur, le fichier a été téléchargé avec succès.",
                UPLOAD_ERR_INI_SIZE   => "Le fichier téléchargé dépasse la directive upload_max_filesize dans php.ini.",
                UPLOAD_ERR_FORM_SIZE  => "Le fichier téléchargé dépasse la directive MAX_FILE_SIZE spécifiée dans le formulaire HTML.",
                UPLOAD_ERR_PARTIAL    => "Le fichier n'a été que partiellement téléchargé.",
                UPLOAD_ERR_NO_FILE    => "Aucun fichier n'a été téléchargé.",
                UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
                UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture du fichier sur le disque.",
                UPLOAD_ERR_EXTENSION  => "Une extension PHP a arrêté le téléchargement du fichier."
            );
            $message = "Erreur lors de l'upload de la photo : " . ($phpFileUploadErrors[$_FILES['photo']['error']] ?? "Erreur inconnue.");
            $message_type = 'error';
        }

        // Si aucune erreur jusqu'à présent, tenter l'insertion en BD
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
                    error_log("Erreur PDO execute (cats) dans main.php: " . implode(" - ", $stmt->errorInfo()));
                }

                // --- Insertion dans la table 'snapcat' (Snap) ---
                // Assurez-vous que la table 'snapcat' existe et que les colonnes sont correctes
                // sender_id : l'utilisateur connecté
                // receiver_id : Pour l'exemple, nous allons utiliser l'ID de l'expéditeur lui-même,
                //               mais dans une vraie application, cela viendrait d'une sélection de l'utilisateur.
                // expiration_time : Définir une expiration (ex: 24 heures à partir de maintenant)
                $receiver_id = $current_user_id; // À adapter selon la logique de votre application (ex: sélection d'un ami)
                $expiration_time = date('Y-m-d H:i:s', strtotime('+24 hours')); // Snap expire dans 24 heures

                $stmt2 = $conn->prepare("INSERT INTO snapcat (sender_id, receiver_id, image_ID, message, expiration_time) VALUES (:sender_id, :receiver_id, :image_ID, :message, :expiration_time)");

                $stmt2->bindParam(':sender_id', $current_user_id);
                $stmt2->bindParam(':receiver_id', $receiver_id);
                $stmt2->bindParam(':image_ID', $photoPath); // Utilise le chemin de la photo du chat comme image_ID
                $stmt2->bindParam(':message', $caption);    // Utilise la légende comme message
                $stmt2->bindParam(':expiration_time', $expiration_time);

                if ($stmt2->execute()) {
                    $message .= "Snap envoyé avec succès !";
                    if ($message_type !== 'error') $message_type = 'success'; // Ne pas écraser une erreur précédente
                } else {
                    $message .= "Erreur lors de l'enregistrement du snap : " . implode(" - ", $stmt2->errorInfo());
                    $message_type = 'error';
                    error_log("Erreur PDO execute (snapcat) dans main.php: " . implode(" - ", $stmt2->errorInfo()));
                }

            } catch (PDOException $e) {
                $message .= "Erreur de base de données lors de l'insertion : " . $e->getMessage();
                $message_type = 'error';
                error_log("Erreur PDO catch dans main.php: " . $e->getMessage());
            }
        }
    }
}

// Récupérer tous les profils de chats depuis la base de données pour l'affichage
function getTousProfilsCats($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM cats");
        $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

$cats = getTousProfilsCats($conn); // Récupère les chats après une éventuelle insertion

// Fonction pour simuler une rencontre
function Rencontre($chat1, $chat2) {
    echo "<h3 class='text-xl font-semibold text-gray-800 mb-4'>Rencontre entre {$chat1['nom']} et {$chat2['nom']} !</h3>";
    $interetsCommuns = array_intersect($chat1['interets'], $chat2['interets']);
    if (!empty($interetsCommuns)) {
        echo "<p class='text-gray-700'>Ils ont des intérêts communs : " . implode(', ', $interetsCommuns) . ".</p>";
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
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
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
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select,
        input[type="file"] { /* Ajout de input[type="file"] pour le style */
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #2563eb;
        }
        .cat-profile {
            border: 1px solid #e5e7eb;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            background-color: #f9fafb;
        }
        .cat-profile img {
            max-width: 100px;
            height: auto;
            border-radius: 5px;
            margin-top: 10px;
        }
        .message-box {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border-width: 1px;
        }
        .message-success {
            background-color: #d1fae5; /* green-100 */
            border-color: #34d399; /* green-400 */
            color: #065f46; /* green-700 */
        }
        .message-error {
            background-color: #fee2e2; /* red-100 */
            border-color: #ef4444; /* red-400 */
            color: #b91c1c; /* red-700 */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-4xl font-bold text-center text-gray-900 mb-8">Snapcat</h1>

        <nav class="mb-8 p-4 bg-blue-600 text-white rounded-lg shadow-md flex justify-between items-center">
            <div class="text-xl font-semibold">
                Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?> !
            </div>
            <div>
                <a href="main.php" class="text-white hover:text-blue-200 mx-2">Accueil</a>
                <a href="profile.php" class="text-white hover:text-blue-200 mx-2">Mon Profil</a>
                <a href="logout.php" class="bg-blue-700 hover:bg-blue-800 text-white py-2 px-4 rounded-md ml-4">Déconnexion</a>
            </div>
        </nav>

        <?php if ($message): ?>
            <div class="message-box <?php echo ($message_type === 'success') ? 'message-success' : 'message-error'; ?>">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Ajouter un nouveau profil de chat et un Snap</h2>
        <form method="POST" enctype="multipart/form-data">
            <!-- Champs pour le profil du chat -->
            <div class="mb-5">
                <label for="nom" class="block text-gray-700 text-sm font-medium mb-2">Nom du chat :</label>
                <input type="text" id="nom" name="nom" required class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-5">
                <label for="numero_puce" class="block text-gray-700 text-sm font-medium mb-2">Numéro de puce :</label>
                <input type="text" id="numero_puce" name="numero_puce" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-5">
                <label for="age" class="block text-gray-700 text-sm font-medium mb-2">Âge :</label>
                <input type="number" id="age" name="age" required min="0" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-5">
                <label for="sexe" class="block text-gray-700 text-sm font-medium mb-2">Sexe :</label>
                <select id="sexe" name="sexe" required class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="M">Mâle</option>
                    <option value="F">Femelle</option>
                    <option value="Inconnu">Inconnu</option>
                </select>
            </div>
            <div class="mb-5">
                <label for="description" class="block text-gray-700 text-sm font-medium mb-2">Description :</label>
                <textarea id="description" name="description" rows="4" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="mb-5">
                <label for="localisation" class="block text-gray-700 text-sm font-medium mb-2">Localisation :</label>
                <input type="text" id="localisation" name="localisation" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-5">
                <label for="interets" class="block text-gray-700 text-sm font-medium mb-2">Intérêts (séparés par des virgules) :</label>
                <input type="text" id="interets" name="interets" placeholder="ex: jouer,dormir,chasser" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label for="caracteristiques" class="block text-gray-700 text-sm font-medium mb-2">Caractéristiques (séparées par des virgules) :</label>
                <input type="text" id="caracteristiques" name="caracteristiques" placeholder="ex: poils courts,yeux verts" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Champs pour la photo et la légende (utilisés pour le snap) -->
            <div class="mb-5">
                <label for="photo" class="block text-gray-700 text-sm font-medium mb-2">Photo du chat (pour profil et Snap) :</label>
                <input type="file" id="photo" name="photo" accept="image/*" required class="w-full text-gray-700 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
            </div>
            <div class="mb-6">
                <label for="caption" class="block text-gray-700 text-sm font-medium mb-2">Légende du Snap :</label>
                <input type="text" id="caption" name="caption" placeholder="Ajoutez une légende pour le snap" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                Ajouter le chat et le Snap
            </button>
        </form>

        <h2 class="text-2xl font-bold text-center text-gray-800 mt-10 mb-6">Profils de chats enregistrés :</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($cats)): ?>
                <p class="col-span-full text-center text-gray-600">Aucun profil de chat enregistré pour le moment.</p>
            <?php else: ?>
                <?php foreach ($cats as $profil): ?>
                    <div class="cat-profile bg-white shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($profil['nom']); ?></h3>
                        <?php if ($profil['photo']): ?>
                            <img src="<?php echo htmlspecialchars($profil['photo']); ?>" alt="Photo de <?php echo htmlspecialchars($profil['nom']); ?>" class="w-full h-48 object-cover mb-4 rounded-md">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500 mb-4 rounded-md">Pas de photo</div>
                        <?php endif; ?>
                        <p class="text-gray-700"><strong>Numéro de puce :</strong> <?php echo htmlspecialchars($profil['numero_puce']); ?></p>
                        <p class="text-gray-700"><strong>Âge :</strong> <?php echo htmlspecialchars($profil['age']); ?> ans</p>
                        <p class="text-gray-700"><strong>Sexe :</strong> <?php echo htmlspecialchars($profil['sexe']); ?></p>
                        <p class="text-gray-700"><strong>Localisation :</strong> <?php echo htmlspecialchars($profil['localisation']); ?></p>
                        <p class="text-gray-700"><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($profil['description'])); ?></p>
                        <p class="text-gray-700"><strong>Intérêts :</strong> <?php echo htmlspecialchars(implode(', ', $profil['interets'])); ?></p>
                        <p class="text-gray-700"><strong>Caractéristiques :</strong> <?php echo htmlspecialchars(implode(', ', $profil['caracteristiques'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h2 class="text-2xl font-bold text-center text-gray-800 mt-10 mb-6">Simulations de Rencontres</h2>
        <?php
        // Simuler une rencontre entre deux chats si au moins deux profils existent
        if (count($cats) >= 2) {
            Rencontre($cats[0], $cats[1]);
        } else {
            echo "<p class='text-center text-gray-600'>Pas assez de chats pour une rencontre.</p>";
        }
        ?>
    </div>
</body>
</html>
