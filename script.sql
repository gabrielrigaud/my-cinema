-- =========================================
-- Script de création de la base de données
-- My Cinema - Application de gestion de cinéma
-- =========================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS my_cinema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cinema;

-- =========================================
-- Table: films
-- =========================================
CREATE TABLE IF NOT EXISTS films (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    duree INT NOT NULL COMMENT 'Durée en minutes',
    annee INT NOT NULL COMMENT 'Année de sortie',
    genre VARCHAR(50) NOT NULL,
    is_deleted TINYINT(1) DEFAULT 0 COMMENT 'Suppression logique (0=actif, 1=supprimé)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_genre (genre),
    INDEX idx_annee (annee),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Table: salles
-- =========================================
CREATE TABLE IF NOT EXISTS salles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    capacite INT NOT NULL COMMENT 'Nombre de places',
    type ENUM('Standard', '3D', 'IMAX', '4DX') DEFAULT 'Standard',
    is_deleted TINYINT(1) DEFAULT 0 COMMENT 'Suppression logique',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_deleted (is_deleted),
    CHECK (capacite > 0 AND capacite <= 1000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Table: seances
-- =========================================
CREATE TABLE IF NOT EXISTS seances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    film_id INT NOT NULL,
    salle_id INT NOT NULL,
    date DATE NOT NULL,
    heure TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE RESTRICT,
    FOREIGN KEY (salle_id) REFERENCES salles(id) ON DELETE RESTRICT,
    INDEX idx_date (date),
    INDEX idx_film_id (film_id),
    INDEX idx_salle_id (salle_id),
    INDEX idx_date_salle (date, salle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Insertion de données de test
-- =========================================

-- Films
INSERT INTO films (titre, duree, annee, genre) VALUES
('Inception', 148, 2010, 'Science-Fiction'),
('The Dark Knight', 152, 2008, 'Action'),
('Interstellar', 169, 2014, 'Science-Fiction'),
('Pulp Fiction', 154, 1994, 'Thriller'),
('Forrest Gump', 142, 1994, 'Drame'),
('The Matrix', 136, 1999, 'Science-Fiction'),
('Gladiator', 155, 2000, 'Action'),
('Le Seigneur des Anneaux : La Communauté de l\'anneau', 178, 2001, 'Fantastique'),
('Avatar', 162, 2009, 'Science-Fiction'),
('Titanic', 195, 1997, 'Romance'),
('Parasite', 132, 2019, 'Thriller'),
('Joker', 122, 2019, 'Drame'),
('Avengers: Endgame', 181, 2019, 'Action'),
('The Shawshank Redemption', 142, 1994, 'Drame'),
('La La Land', 128, 2016, 'Musical');

-- Salles
INSERT INTO salles (nom, capacite, type) VALUES
('Salle 1 - Auditorium', 300, 'Standard'),
('Salle 2 - Premium', 150, '3D'),
('Salle 3 - IMAX', 400, 'IMAX'),
('Salle 4 - Petite salle', 80, 'Standard'),
('Salle 5 - 4DX', 120, '4DX');

-- Séances (exemples pour les 7 prochains jours)
INSERT INTO seances (film_id, salle_id, date, heure) VALUES
-- Aujourd'hui
(1, 1, CURDATE(), '14:00:00'),
(2, 2, CURDATE(), '16:30:00'),
(3, 3, CURDATE(), '19:00:00'),
(4, 4, CURDATE(), '21:00:00'),

-- Demain
(5, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00'),
(6, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00'),
(7, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:30:00'),
(8, 5, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '21:00:00'),

-- Après-demain
(9, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00'),
(10, 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '17:00:00'),
(11, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '20:00:00'),

-- Dans 3 jours
(12, 4, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00'),
(13, 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '18:00:00'),
(14, 2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '20:30:00'),

-- Dans 4 jours
(15, 3, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '14:30:00'),
(1, 5, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '17:00:00'),
(2, 4, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '19:30:00');

-- =========================================
-- Statistiques de la base
-- =========================================
SELECT 
    'Base de données créée avec succès!' AS Message,
    (SELECT COUNT(*) FROM films WHERE is_deleted = 0) AS Films,
    (SELECT COUNT(*) FROM salles WHERE is_deleted = 0) AS Salles,
    (SELECT COUNT(*) FROM seances) AS Seances;
