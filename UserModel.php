<?php
// UserModel.php - Gestion des utilisateurs

class UserModel {
    public PDO $conn;
    function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function login($email, $password) {

        // Requête SQL pour obtenir l'utilisateur correspondant à l'email
        $stmt = $this->conn->prepare("SELECT id, email, password FROM users WHERE email = :email");
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

    public function userExists(string $email){
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
 /**
     * Enregistre un nouvel utilisateur dans la base de données.
     *
     * @param string $email L'adresse e-mail du nouvel utilisateur.
     * @param string $hashedPassword Le mot de passe de l'utilisateur, qui DOIT être déjà haché.
     * @return bool Vrai si l'insertion est réussie, faux en cas d'échec ou d'exception.
     */
    public function registerUser($email, $hashedPassword) {
        try {
            // Prépare la requête SQL d'insertion.
            // Utilisez des placeholders nommés (:email, :password) pour la sécurité.
            $stmt = $this->conn->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");

            // Lie les paramètres aux valeurs, en spécifiant le type de données (PDO::PARAM_STR pour chaîne).
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            // Debogue
            var_dump($stmt->queryString); 
            // Exécute la requête préparée.
            // Si l'exécution réussit, execute() renvoie true. Sinon, false (si non géré par exception)
            // ou une exception si PDO::ERRMODE_EXCEPTION est activé (ce qui est le cas).
            return $stmt->execute();


        } catch (PDOException $e) {
            // En cas d'erreur lors de l'insertion (par exemple, un problème de contrainte UNIQUE,
            // ou une colonne manquante), l'exception est capturée ici.
            // Il est crucial de logguer cette erreur pour le débogage.
            error_log("Erreur PDO lors de l'enregistrement de l'utilisateur: " . $e->getMessage());
            // Pour l'utilisateur, on renvoie false pour indiquer l'échec.
            return false;
        }
    }
}
?>
