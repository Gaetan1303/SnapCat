CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    numero_puce VARCHAR(100),
    age INT,
    sexe VARCHAR(10),
    photo VARCHAR(255) NOT NULL,
    description TEXT,
    localisation VARCHAR(100),
    interets TEXT,
    caracteristiques TEXT,
    caption VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);