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
     * @return array|false Les informations de l'utilisateur (id, email, username) si la connexion est réussie, false sinon.
     */
    public function login(string $email, string $password) {
        // Requête pour obtenir l'utilisateur par email. Inclut 'username' pour la session.
        $stmt = $this->conn->prepare("SELECT id, email, username, password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch();

        // Vérifie si un utilisateur a été trouvé et si le mot de passe correspond.
        if ($user && password_verify($password, $user['password'])) {
            return $user; // Retourne les infos de l'utilisateur connecté
        } else {
            return false; // Échec de la connexion
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
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
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
    public function registerUser(string $email, string $username, string $hashedPassword): bool {
        try {
            // Inclut 'username' dans la requête INSERT car il est désormais requis.
            $sql = "INSERT INTO users (email, username, password) VALUES (:email, :username, :password)";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR); // Liaison pour 'username'
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

            return $stmt->execute();

        } catch (PDOException $e) {
            // Loggue l'erreur détaillée pour le débogage côté serveur.
            error_log("Erreur PDO lors de l'enregistrement de l'utilisateur: " . $e->getMessage());
            // Retourne false pour indiquer l'échec à l'appelant.
            return false;
        }
    }
}
?>