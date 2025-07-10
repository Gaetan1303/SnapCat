<?php
// UserModel.php - Gestion des utilisateurs

class UserModel {
    public PDO $conn;

    function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /**
     * Tente de connecter un utilisateur.
     *
     * @param string $email L'adresse e-mail de l'utilisateur.
     * @param string $password Le mot de passe non haché.
     * @return array|false Les informations de l'utilisateur si la connexion est réussie, false sinon.
     */
    public function login($email, $password) {
        // Requête SQL pour obtenir l'utilisateur correspondant à l'email
        // Récupère aussi le nom d'utilisateur si tu veux l'utiliser après la connexion
        $stmt = $this->conn->prepare("SELECT id, email, username, password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Vérifie si un utilisateur existe
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Si le mot de passe est correct, retourne les informations de l'utilisateur
            return $user;
        } else {
            // Si les informations sont incorrectes, retourne false
            return false;
        }
    }

    /**
     * Vérifie si un utilisateur existe déjà avec une adresse e-mail donnée.
     *
     * @param string $email L'adresse e-mail à vérifier.
     * @return bool Vrai si l'utilisateur existe, faux sinon.
     */
    public function userExists(string $email): bool {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR); // Spécifier le type est plus robuste
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Enregistre un nouvel utilisateur dans la base de données.
     *
     * @param string $email L'adresse e-mail du nouvel utilisateur.
     * @param string $username Le nom d'utilisateur du nouvel utilisateur.
     * @param string $hashedPassword Le mot de passe de l'utilisateur, qui DOIT être déjà haché.
     * @return bool Vrai si l'insertion est réussie, faux en cas d'échec ou d'exception.
     */
    // --- MODIFICATION MAJEURE ICI : Ajout du paramètre $username ---
    public function registerUser(string $email, string $username, string $hashedPassword): bool {
        try {
            // Prépare la requête SQL d'insertion, incluant la colonne 'username'
            $sql = "INSERT INTO users (email, username, password) VALUES (:email, :username, :password)";
            $stmt = $this->conn->prepare($sql);

            // Lie les paramètres aux valeurs, en spécifiant le type de données
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR); // Liaison du username
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

            // Exécute la requête préparée.
            return $stmt->execute();

        } catch (PDOException $e) {
            // En cas d'erreur lors de l'insertion, journalise l'erreur
            error_log("Erreur PDO lors de l'enregistrement de l'utilisateur: " . $e->getMessage());
            // Retourne false pour indiquer l'échec sans exposer les détails de l'erreur.
            return false;
        }
    }
}
?>