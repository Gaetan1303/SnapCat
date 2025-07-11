<?php

class ChatModel {
    private $conn;

    /**
     * Constructeur du ChatModel.
     * @param PDO $conn L'objet de connexion PDO à la base de données.
     */
    public function __construct(PDO $conn) {
        $this->conn = $conn;
        // Optionnel: Définir le mode d'erreur de PDO pour faciliter le débogage
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Récupère un profil de chat par son ID.
     * Si un userId est fourni, vérifie que le chat appartient bien à cet utilisateur.
     *
     * @param int $chatId L'ID du chat.
     * @param int|null $userId L'ID de l'utilisateur propriétaire (optionnel, pour vérification).
     * @return array|false Les données du chat ou false si non trouvé/non autorisé.
     */
    public function getCatProfileById(int $chatId, ?int $userId = null) {
        $query = "SELECT id, user_id, nom, age, sexe, race, localisation, description, photo FROM chats WHERE id = ?";
        $params = [$chatId];

        if ($userId !== null) {
            $query .= " AND user_id = ?";
            $params[] = $userId;
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $chat = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($chat) {
                // Pour la démo, les intérêts sont génériques. Dans une vraie app, ce serait une relation Many-to-Many.
                $chat['interets'] = ['Jouer', 'Dormir', 'Manger', 'Câlins'];
            }
            return $chat;

        } catch (PDOException $e) {
            error_log("Erreur PDO dans getCatProfileById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les chats pour le carrousel.
     * Peut être adapté pour exclure les chats de l'utilisateur actuel si nécessaire.
     *
     * @param int|null $excludeUserId L'ID de l'utilisateur dont les chats doivent être exclus (si applicable).
     * @return array Une liste de profils de chats.
     */
    public function getAllCatsForCarousel(?int $excludeUserId = null): array {
        $query = "SELECT id, user_id, nom, age, sexe, race, localisation, description, photo FROM chats";
        $params = [];

        if ($excludeUserId !== null) {
            $query .= " WHERE user_id != ?";
            $params[] = $excludeUserId;
        }
        $query .= " ORDER BY created_at DESC"; // Ou RAND() pour un ordre aléatoire

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ajouter des intérêts fictifs pour le carrousel si besoin
            foreach ($cats as &$cat) {
                $cat['interets'] = ['Jouer', 'Dormir', 'Manger', 'Câlins']; // Simule des intérêts
            }
            return $cats;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans getAllCatsForCarousel: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ajoute un nouveau profil de chat.
     *
     * @param int $userId L'ID de l'utilisateur propriétaire du chat.
     * @param string $nom Le nom du chat.
     * @param int|null $age L'âge du chat.
     * @param string|null $sexe Le sexe du chat ('Mâle' ou 'Femelle').
     * @param string|null $race La race du chat.
     * @param string|null $localisation La localisation du chat.
     * @param string|null $description La description du chat.
     * @param string|null $photoPath Le chemin d'accès à la photo du chat.
     * @return bool True si l'ajout a réussi, false sinon.
     */
    public function addCat(
        int $userId,
        string $nom,
        ?int $age,
        ?string $sexe,
        ?string $race,
        ?string $localisation,
        ?string $description,
        ?string $photoPath
    ): bool {
        $query = "INSERT INTO chats (user_id, nom, age, sexe, race, localisation, description, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                $userId,
                $nom,
                $age,
                $sexe,
                $race,
                $localisation,
                $description,
                $photoPath
            ]);
        } catch (PDOException $e) {
            error_log("Erreur PDO dans addCat: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour un profil de chat existant.
     * Vérifie que le chat appartient à l'utilisateur spécifié.
     *
     * @param int $chatId L'ID du chat à mettre à jour.
     * @param int $userId L'ID de l'utilisateur propriétaire.
     * @param string $nom Le nouveau nom.
     * @param int|null $age Le nouvel âge.
     * @param string|null $sexe Le nouveau sexe.
     * @param string|null $race La nouvelle race.
     * @param string|null $localisation La nouvelle localisation.
     * @param string|null $description La nouvelle description.
     * @param string|null $photoPath Le nouveau chemin de la photo (peut être null si inchangé).
     * @return bool True si la mise à jour a réussi, false sinon.
     */
    public function updateCat(
        int $chatId,
        int $userId,
        string $nom,
        ?int $age,
        ?string $sexe,
        ?string $race,
        ?string $localisation,
        ?string $description,
        ?string $photoPath = null
    ): bool {
        $query = "UPDATE chats SET nom = ?, age = ?, sexe = ?, race = ?, localisation = ?, description = ?";
        $params = [$nom, $age, $sexe, $race, $localisation, $description];

        if ($photoPath !== null) { // Seulement si une nouvelle photo est fournie ou supprimée
            $query .= ", photo = ?";
            $params[] = $photoPath;
        }

        $query .= " WHERE id = ? AND user_id = ?";
        $params[] = $chatId;
        $params[] = $userId;

        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur PDO dans updateCat: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un profil de chat.
     * Vérifie que le chat appartient à l'utilisateur spécifié.
     *
     * @param int $chatId L'ID du chat à supprimer.
     * @param int $userId L'ID de l'utilisateur propriétaire.
     * @return bool True si la suppression a réussi, false sinon.
     */
    public function deleteCat(int $chatId, int $userId): bool {
        $query = "DELETE FROM chats WHERE id = ? AND user_id = ?";
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$chatId, $userId]);
        } catch (PDOException $e) {
            error_log("Erreur PDO dans deleteCat: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les chats appartenant à un utilisateur spécifique.
     * Utile pour afficher les chats dans le dashboard de l'utilisateur.
     *
     * @param int $userId L'ID de l'utilisateur.
     * @return array Une liste des chats de l'utilisateur.
     */
    public function getCatsByUserId(int $userId): array {
        $query = "SELECT id, nom, age, sexe, race, localisation, description, photo FROM chats WHERE user_id = ? ORDER BY created_at DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur PDO dans getCatsByUserId: " . $e->getMessage());
            return [];
        }
    }
}