<?php

class ChatModel {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    // Ajout du paramètre $photo
    public function addChatProfile($nom, $numeroPuce, $age, $sexe, $photo, $description, $localisation, $interets, $caracteristiques) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO chats (nom, numero_puce, age, sexe, photo, description, localisation, interets, caracteristiques) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $numeroPuce, $age, $sexe, $photo, $description, $localisation, $interets, $caracteristiques]);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur PDO lors de l'ajout du profil de chat: " . $e->getMessage());
            // Pour le débogage, vous pouvez afficher l'erreur, mais supprimez-le en production
            // echo "Erreur: " . $e->getMessage();
            return false;
        }
    }

    // Modification du paramètre $photo
    public function updateChatProfile($id, $nom, $numeroPuce, $age, $sexe, $photo, $description, $localisation, $interets, $caracteristiques) {
        try {
            $stmt = $this->conn->prepare("UPDATE chats SET nom = ?, numero_puce = ?, age = ?, sexe = ?, photo = ?, description = ?, localisation = ?, interets = ?, caracteristiques = ? WHERE id = ?");
            $stmt->execute([$nom, $numeroPuce, $age, $sexe, $photo, $description, $localisation, $interets, $caracteristiques, $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur PDO lors de la mise à jour du profil de chat: " . $e->getMessage());
            return false;
        }
    }

    public function deleteChatProfile($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM chats WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur PDO lors de la suppression du profil de chat: " . $e->getMessage());
            return false;
        }
    }

    public function getAllChatProfiles() {
        try {
            $stmt = $this->conn->query("SELECT * FROM chats ORDER BY nom ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur PDO lors de la récupération des profils de chat: " . $e->getMessage());
            return [];
        }
    }

    public function getChatProfileById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM chats WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur PDO lors de la récupération du profil de chat par ID: " . $e->getMessage());
            return false;
        }
    }
}