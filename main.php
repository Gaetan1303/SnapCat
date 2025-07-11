<?php
session_start();
require_once 'bd.php'; // Assurez-vous que c'est bien 'bd.php'
require_once 'ChatModel.php'; // Incluez votre ChatModel

// Redirige l'utilisateur s'il n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?status=login_required');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Récupérer les messages de statut après redirection
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'cat_added') {
        $message = 'Le profil de votre chat a été ajouté avec succès !';
        $message_type = 'success';
    } elseif ($_GET['status'] === 'cat_updated') {
        $message = 'Le profil du chat a été mis à jour avec succès !';
        $message_type = 'success';
    } elseif ($_GET['status'] === 'cat_deleted') {
        $message = 'Le profil du chat a été supprimé avec succès !';
        $message_type = 'success';
    } elseif ($_GET['status'] === 'error') {
        $message = 'Une erreur est survenue : ' . htmlspecialchars($_GET['msg'] ?? 'Veuillez réessayer.');
        $message_type = 'error';
    } elseif ($_GET['status'] === 'account_updated') {
        $message = 'Vos informations de compte ont été mises à jour avec succès !';
        $message_type = 'success';
    }
}

try {
    $chatModel = new ChatModel($conn);
    // Récupérer tous les chats appartenant à l'utilisateur connecté
    $user_cats = $chatModel->getCatsByUserId($user_id);

    // Dans une vraie application, vous récupéreriez aussi les infos de l'utilisateur ici
    // Exemple (nécessite une méthode dans un UserModel ou directement ici):
    // $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    // $stmt->execute([$user_id]);
    // $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Erreur dans main.php: " . $e->getMessage());
    $message = "Impossible de charger les données. Veuillez réessayer plus tard.";
    $message_type = 'error';
    $user_cats = []; // S'assurer que $user_cats est un tableau vide en cas d'erreur
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Dashboard - Snapcat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f8f8; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        .header-main { background-color: #fff; padding: 20px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .logo { font-size: 2.2rem; font-weight: 800; color: #007bff; text-decoration: none; }
        .logo span { color: #28a745; }
        .btn-cta { background-color: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: 600; transition: background-color 0.3s ease; }
        .btn-cta:hover { background-color: #218838; }
        .message-box { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border-width: 1px; font-weight: 500; text-align: center; max-width: 500px; margin-left: auto; margin-right: auto; }
        .message-success { background-color: #d1fae5; border-color: #34d399; color: #065f46; }
        .message-error { background-color: #fee2e2; border-color: #ef4444; color: #b91c1c; }
        .message-info { background-color: #e0f2fe; border-color: #38bdf8; color: #0c4a6e; }
        .cat-card { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .cat-card img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin-bottom: 15px; }
    </style>
</head>
<body>
    <header>
        <div class="header-main">
            <div class="container flex justify-between items-center">
                <a href="index.php" class="logo">Snap<span>Cat</span></a>
                <nav class="main-nav hidden md:flex space-x-6">
                    <a href="index.php" class="text-gray-700 hover:text-blue-500 font-medium">Accueil Public</a>
                    <a href="main.php" class="text-blue-600 hover:text-blue-700 font-bold">Mon Dashboard</a>
                    <a href="account_settings.php" class="text-gray-700 hover:text-blue-500 font-medium">Paramètres du Compte</a>
                </nav>
                <div class="flex items-center space-x-4">
                    <a href="logout.php" class="btn-cta bg-red-500 hover:bg-red-600">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container my-8">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Bienvenue sur votre Dashboard, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?> !</h1>

        <?php if ($message): ?>
            <div class="message-box <?php echo ($message_type === 'success') ? 'message-success' : (($message_type === 'error') ? 'message-error' : 'message-info'); ?>">
                <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <section class="my-8 p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4 flex justify-between items-center">
                Mes Profuels de Chats
                <a href="add_cat.php" class="btn-cta text-sm py-2 px-4"><i class="fas fa-plus mr-2"></i>Ajouter un Nouveau Chat</a>
            </h2>

            <?php if (!empty($user_cats)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($user_cats as $cat): ?>
                        <div class="cat-card flex flex-col items-center">
                            <?php if (!empty($cat['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($cat['photo']); ?>" alt="Photo de <?php echo htmlspecialchars($cat['nom']); ?>" class="mb-4 rounded-full w-24 h-24 object-cover">
                            <?php else: ?>
                                <img src="https://placehold.co/100x100/e2e8f0/64748b?text=No+Photo" alt="Pas de Photo" class="mb-4 rounded-full w-24 h-24 object-cover">
                            <?php endif; ?>
                            <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($cat['nom']); ?></h3>
                            <p class="text-gray-600 text-center mb-4">
                                Age: <?php echo htmlspecialchars($cat['age'] ?? 'N/A'); ?> ans |
                                Sexe: <?php echo htmlspecialchars($cat['sexe'] ?? 'N/A'); ?>
                            </p>
                            <div class="flex space-x-3">
                                <a href="edit_cat.php?id=<?php echo htmlspecialchars($cat['id']); ?>" class="text-blue-500 hover:text-blue-700 font-medium"><i class="fas fa-edit mr-1"></i>Modifier</a>
                                <a href="delete_cat.php?id=<?php echo htmlspecialchars($cat['id']); ?>" class="text-red-500 hover:text-red-700 font-medium" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce chat ? Cette action est irréversible.');"><i class="fas fa-trash-alt mr-1"></i>Supprimer</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600 text-center">Vous n'avez pas encore ajouté de chats. <a href="add_cat.php" class="text-blue-500 hover:underline">Ajoutez-en un maintenant !</a></p>
            <?php endif; ?>
        </section>

        <section class="my-8 p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4 flex justify-between items-center">
                Paramètres du Compte
                <a href="account_settings.php" class="btn-cta text-sm py-2 px-4"><i class="fas fa-cog mr-2"></i>Gérer mon Compte</a>
            </h2>
            <p class="text-gray-600">Mettez à jour vos informations personnelles, votre email ou votre mot de passe.</p>
        </section>

    </main>

    <footer class="footer bg-gray-800 text-white p-4 text-center mt-auto">
        &copy; <?php echo date('Y'); ?> Snapcat. Tous droits réservés.
    </footer>
</body>
</html>