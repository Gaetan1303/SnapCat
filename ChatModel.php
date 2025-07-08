<?php

class Chat {
    public PDO $conn;

    /**
     * Constructeur : Établit la connexion à la base de données et crée la table 'chat' si elle n'existe pas.
     */
    public function __construct() {
        // Configuration de la connexion PostgreSQL
        // 'db' est le nom du service PostgreSQL dans votre docker-compose.yaml
        // 'snapcatdb' est le nom de la base de données
        // 'snapcatuser' est l'utilisateur et 'secret' est le mot de passe
        $host = '127.0.0.1';
        $dbname = 'snapcatdb';
        $user = 'root';
        $password = 'root';

        try {
            $dsn = "mysql:host=$host;dbname=$dbname";
            // Instancie PDO pour la connexion à PostgreSQL
            $this->conn = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Active le mode exception pour les erreurs
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Récupère les résultats sous forme de tableau associatif
            ]);

            // Création de la table 'chats' si elle n'existe pas
            // N'oubliez pas d'ajouter toutes les colonnes nécessaires selon votre modèle de données
            $createTableSQL = "
                CREATE TABLE IF NOT EXISTS chats (
                    id SERIAL PRIMARY KEY,
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
            $this->conn->exec($createTableSQL);

        } catch (PDOException $e) {
            // En cas d'erreur de connexion ou de création de table, journalise l'erreur
            // Il est important de configurer PHP pour logguer les erreurs dans un fichier
            // ou de rediriger STDOUT/STDERR vers un système de log.
            error_log("Erreur de connexion à la base de données ou de création de table: " . $e->getMessage());
            // Pour le débogage initial, vous pouvez afficher l'erreur, mais supprimez-le en production
            die("Erreur de connexion à la base de données: " . $e->getMessage());
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
            // Pour le débogage initial, vous pouvez afficher l'erreur
            // echo "Erreur lors de l'ajout du profil de chat: " . $e->getMessage();
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

function main() {
    echo "Démarrage de l'application...\n";
    try {
        $chatModel = new ChatModel();
        echo "Connexion à la base de données et vérification de la table 'chats' réussies.\n";

        // Exemple d'ajout d'un chat
        $isAdded = $chatModel->addChatProfile(
            "Felix",
            "ABC12345", // numéro de puce
            3,          // âge
            "Mâle",
            "http://example.com/felix.jpg",
            "Un chat joueur et affectueux.",
            "Toulouse",
            "Jouer, dormir, manger",
            "Poil court, yeux verts"
        );

        if ($isAdded) {
            echo "Profil de chat 'Felix' ajouté avec succès.\n";
        } else {
            echo "Échec de l'ajout du profil de chat 'Felix'. Vérifiez les logs.\n";
        }

        // Exemple d'ajout d'un deuxième chat (si le numéro de puce est unique, le second échouera s'il est identique)
        $isAdded2 = $chatModel->addChatProfile(
            "Mia",
            "DEF67890", // numéro de puce unique
            2,
            "Femelle",
            "http://example.com/mia.jpg",
            "Une chatte calme et indépendante.",
            "Paris",
            "Câlins, explorer, chasser",
            "Poil long, yeux bleus"
        );

        if ($isAdded2) {
            echo "Profil de chat 'Mia' ajouté avec succès.\n";
        } else {
            echo "Échec de l'ajout du profil de chat 'Mia'. Vérifiez les logs.\n";
        }

        // Exemple de récupération de tous les chats
        $allChats = $chatModel->getAllChatProfiles();
        echo "Profils de chats récupérés:\n";
        print_r($allChats);

    } catch (Throwable $th) {
        // Cette section capture les erreurs qui ne sont pas gérées par PDOExceptions dans la classe
        error_log("Erreur inattendue dans main(): " . $th->getMessage());
        echo "Une erreur inattendue s'est produite. Vérifiez les logs pour plus de détails.\n";
    }
}
main();

?>