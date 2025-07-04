-- Table pour les utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Mot_de_passe VARCHAR(255) NOT NULL,
    photo_de_profil VARCHAR(255) -- chemin de l'image de profil
);

-- Table pour les messages (snapcat)
CREATE TABLE snapcat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL, -- Référence à l'utilisateur qui envoie
    receiver_id INT NOT NULL, -- Référence à l'utilisateur qui reçoit
    image_ID VARCHAR(255), -- chemin de l'image (snap)
    video_ID VARCHAR(255), -- chemin de la vidéo (optionnel)
    message TEXT, -- Message texte (optionnel)
    expiration_time TIMESTAMP NOT NULL, -- Temps d'expiration du snap
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Quand le snap a été envoyé
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Table pour les amis (relations d'amitié)
CREATE TABLE friends (
    user_id INT NOT NULL, -- L'utilisateur
    friend_id INT NOT NULL, -- L'ami
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending', -- statut de la demande d'ami
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, friend_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (friend_id) REFERENCES users(id)
);

-- Table pour les notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- L'utilisateur qui reçoit la notification
    message VARCHAR(255) NOT NULL, -- Message de la notification
    is_read BOOLEAN DEFAULT FALSE, -- Si la notification a été lue
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
