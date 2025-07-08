<?php
// Inclure le fichier de connexion à la base de données
// Assurez-vous que le chemin est correct. Par exemple, si register.php est à la racine et bd.php aussi.
include'bd.php';

// Vérifier si la méthode de requête est POST (le formulaire a été soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    // Utilisation de htmlspecialchars pour prévenir les attaques XSS lors de l'affichage ultérieur
    $Nom = htmlspecialchars($_POST['username']);
    $Email = htmlspecialchars($_POST['email']);
    $Mot_de_passe = $_POST['password']; // Le mot de passe sera haché, pas besoin de htmlspecialchars ici

    // Hacher le mot de passe pour des raisons de sécurité
    // PASSWORD_DEFAULT utilise l'algorithme de hachage le plus fort disponible (actuellement bcrypt)
    $Mot_de_passe_hache = password_hash($Mot_de_passe, PASSWORD_DEFAULT);

    // Définir le rôle par défaut pour les nouveaux utilisateurs
    $role = 'user';

    // Initialiser le chemin de la photo de profil à null
    $photo_de_profil_path = null;

    // Gérer le téléchargement de la photo de profil
    // Vérifier si un fichier a été téléchargé et s'il n'y a pas d'erreur
    if (isset($_FILES['photo_de_profil']) && $_FILES['photo_de_profil']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Dossier où les images seront stockées
        // Assurez-vous que ce dossier existe et est inscriptible par le serveur web (www-data)
        // Le Dockerfile gère déjà la création et les permissions de ce dossier.

        // Générer un nom de fichier unique pour éviter les conflits et les problèmes de sécurité
        $file_extension = pathinfo($_FILES['photo_de_profil']['name'], PATHINFO_EXTENSION);
        $new_file_name = uniqid('profile_') . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;

        // Vérifier le type de fichier (s'assurer que c'est une image)
        $check = getimagesize($_FILES['photo_de_profil']['tmp_name']);
        if ($check !== false) {
            // Déplacer le fichier temporaire vers le répertoire de destination
            if (move_uploaded_file($_FILES['photo_de_profil']['tmp_name'], $target_file)) {
                $photo_de_profil_path = $target_file; // Enregistrer le chemin relatif dans la base de données
            } else {
                echo "Désolé, une erreur s'est produite lors du téléchargement de votre fichier.";
                // Vous pouvez ajouter une gestion d'erreur plus robuste ici
            }
        } else {
            echo "Le fichier téléchargé n'est pas une image valide.";
        }
    }

    try {
        // Préparer la requête d'insertion avec des placeholders nommés pour PDO
        // Inclut la colonne 'role' et 'photo_de_profil'
        $stmt = $conn->prepare("INSERT INTO users (Nom, Email, Mot_de_passe, photo_de_profil, role) VALUES (:nom, :email, :mot_de_passe, :photo_de_profil, :role)");

        // Binder les valeurs aux placeholders
        $stmt->bindParam(':nom', $Nom);
        $stmt->bindParam(':email', $Email);
        $stmt->bindParam(':mot_de_passe', $Mot_de_passe_hache);
        $stmt->bindParam(':photo_de_profil', $photo_de_profil_path);
        $stmt->bindParam(':role', $role);

        // Exécuter la requête
        if ($stmt->execute()) {
            echo "Utilisateur enregistré avec succès!";
        } else {
            // Gérer les erreurs d'exécution de la requête
            // errorInfo() retourne un tableau d'informations sur l'erreur
            echo "Erreur lors de l'enregistrement de l'utilisateur : " . implode(" - ", $stmt->errorInfo());
        }
    } catch (PDOException $e) {
        // Gérer les erreurs de connexion ou de requête PDO
        // Par exemple, si l'email ou le nom est déjà utilisé (contrainte UNIQUE)
        if ($e->getCode() == 23000) { // Code SQLSTATE pour violation de contrainte d'intégrité
            echo "Erreur: Le nom d'utilisateur ou l'email est déjà utilisé.";
        } else {
            echo "Erreur de base de données : " . $e->getMessage();
        }
    }
}
// Pas besoin de fermer la connexion PDO explicitement, elle sera fermée à la fin du script
// ou lorsque l'objet $conn est détruit.
?>

<!-- Formulaire HTML mis à jour pour inclure la photo de profil -->
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <input type="file" name="photo_de_profil" accept="image/*">
    <button type="submit">S'inscrire</button>
</form>