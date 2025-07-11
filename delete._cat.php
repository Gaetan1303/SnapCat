<?php
session_start();
require_once 'bd.php';
require_once 'ChatModel.php';

// Redirige si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?status=login_required');
    exit;
}

$user_id = $_SESSION['user_id'];
$cat_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$cat_id) {
    header('Location: main.php?status=error&msg=ID de chat manquant pour la suppression.');
    exit;
}

try {
    $chatModel = new ChatModel($conn);

    // D'abord, récupérer le chemin de la photo pour la supprimer du serveur
    $chat_to_delete = $chatModel->getCatProfileById((int)$cat_id, $user_id);

    if (!$chat_to_delete) {
        // Chat non trouvé ou n'appartient pas à l'utilisateur
        header('Location: main.php?status=error&msg=Chat non trouvé ou non autorisé à supprimer.');
        exit;
    }

    // Tenter de supprimer le chat de la base de données
    $success = $chatModel->deleteCat((int)$cat_id, $user_id);

    if ($success) {
        // Si la suppression de la BDD réussit, supprimer le fichier photo
        if (!empty($chat_to_delete['photo']) && file_exists($chat_to_delete['photo'])) {
            unlink($chat_to_delete['photo']);
        }
        header('Location: main.php?status=cat_deleted');
        exit;
    } else {
        header('Location: main.php?status=error&msg=Erreur lors de la suppression du chat.');
        exit;
    }

} catch (Exception $e) {
    error_log("Erreur lors de la suppression du chat: " . $e->getMessage());
    header('Location: main.php?status=error&msg=Une erreur inattendue est survenue lors de la suppression.');
    exit;
}
?>