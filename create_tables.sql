CREATE DATABASE snapcat;
CREATE USER 'snapcatuser'@'localhost' IDENTIFIED BY 'snapcatpass';
GRANT ALL PRIVILEGES ON snapcat.* TO 'snapcatuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE snaps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    photo VARCHAR(255) NOT NULL,
    caption VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);