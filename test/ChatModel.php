<?php

class ChatModel {
    public PDO $conn;

    /**
     * Constructeur : Reçoit la connexion PDO existante et crée la table 'chats' si elle n'existe pas.
     * @param PDO $conn L'objet de connexion PDO déjà établi.
     */
    // --- MODIFICATION ICI : Le constructeur prend $conn en paramètre ---
    public function __construct(PDO $conn) {
        $this->conn = $conn;

        // Création de la table 'chats' si elle n'existe pas
        // N'oubliez pas d'ajouter toutes les colonnes nécessaires selon votre modèle de données
        $createTableSQL = "
            CREATE TABLE IF NOT EXISTS chats (
                id INT AUTO_INCREMENT PRIMARY KEY, -- MODIFICATION ICI : Plus standard pour MySQL
                nom VARCHAR(255) NOT NULL,
                numero_puce VARCHAR(255) UNIQUE,
                age INT,
                sexe VARCHAR(50),
                photo TEXT,
                description TEXT,
                localisation VARCHAR(255),
                interets TEXT,
                caracteristiques TEXT
            );
        ";
        try {
            $this->conn->exec($createTableSQL);
        } catch (PDOException $e) {
            // En cas d'erreur de création de table, journalise l'erreur
            error_log("Erreur de création de table 'chats': " . $e->getMessage());
            // Important : ne pas utiliser die() dans un constructeur appelé dans un contexte web
            // Lancer une exception pour que le code appelant puisse gérer l'erreur.
            throw new Exception("Erreur de base de données lors de la création de la table : " . $e->getMessage());
        }
    }

    /**
     * Ajoute un nouveau profil de chat à la base de données.
     *
     * @param string $nom Nom du chat.
     * @param string|null $numero_puce Numéro de puce.
     * @param int|null $age Âge du chat .
     * @param string|null $sexe Sexe du chat.
     * @param string|null $photo URL ou chemin de la photo.
     * @param string|null $description Description du chat
     * @param string|null $localisation Localisation
     * @param string|null $interets Intérêts
     * @param string|null $caracteristiques Caractéristiques
     * @return bool Vrai si l'ajout est réussi, faux sinon.
     */
    public function addChatProfile(
        string $nom,
        ?string $numero_puce = null,
        ?int $age = null,
        ?string $sexe = null,
        ?string $photo = null,
        ?string $description = null,
        ?string $localisation = null,
        ?string $interets = null,
        ?string $caracteristiques = null
    ): bool {
        try {
            $sql = "INSERT INTO chats (nom, numero_puce, age, sexe, photo, description, localisation, interets, caracteristiques)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $nom,
                $numero_puce,
                $age,
                $sexe,
                $photo,
                $description,
                $localisation,
                $interets,
                $caracteristiques
            ]);
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout du profil de chat: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les profils de chats.
     * @return array Tableau de profils de chats.
     */
    public function getAllChatProfiles(): array {
        try {
            $stmt = $this->conn->query("SELECT * FROM chats ORDER BY nom ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des profils de chats: " . $e->getMessage());
            return [];
        }
    }

}

// --- MODIFICATION ICI : Suppression de la fonction main() de débogage ---
// Le code de test ne doit pas être dans le fichier de la classe de modèle.