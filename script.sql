-- Base de données pour le système de gestion de cinéma
-- Créé le 26/01/2026

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS cinema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cinema;

-- Table des films (movies)
CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    release_year INT NOT NULL,
    genre VARCHAR(100),
    director VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_release_year (release_year),
    INDEX idx_genre (genre),
    INDEX idx_director (director)
) ENGINE=InnoDB;

-- Table des salles (rooms)
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    capacity INT NOT NULL,
    type VARCHAR(50),
    active BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Soft delete flag',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_type (type),
    INDEX idx_active (active)
) ENGINE=InnoDB;

-- Table des séances (screenings)
CREATE TABLE screenings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    room_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE RESTRICT,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
    INDEX idx_movie (movie_id),
    INDEX idx_room (room_id),
    INDEX idx_start_time (start_time),
    INDEX idx_movie_room (movie_id, room_id),
    UNIQUE KEY unique_screening (room_id, start_time)
) ENGINE=InnoDB;

-- Insertion de données de test
INSERT INTO movies (title, description, duration, release_year, genre, director) VALUES
('Inception', 'A thief who enters the dreams of others to steal their secrets.', 148, 2010, 'Science Fiction', 'Christopher Nolan'),
('The Lord of the Rings', 'A hobbit must destroy a powerful ring to save the world.', 178, 2001, 'Fantasy', 'Peter Jackson'),
('Interstellar', 'Explorers travel through a wormhole to save humanity.', 169, 2014, 'Science Fiction', 'Christopher Nolan'),
('La La Land', 'A love story between a jazz musician and an actress in Los Angeles.', 128, 2016, 'Romance', 'Damien Chazelle'),
('Parasite', 'A poor family infiltrates the life of a rich family.', 132, 2019, 'Thriller', 'Bong Joon-ho');

INSERT INTO rooms (name, capacity, type, active) VALUES
('Room 1 - Standard', 120, 'Standard', true),
('Room 2 - 3D', 80, '3D', true),
('Room 3 - IMAX', 200, 'IMAX', true),
('Room 4 - VIP', 40, 'VIP', true),
('Room 5 - Standard', 100, 'Standard', true);

INSERT INTO screenings (movie_id, room_id, start_time) VALUES
(1, 1, '2026-01-27 14:00:00'),
(1, 1, '2026-01-27 17:30:00'),
(2, 3, '2026-01-27 15:00:00'),
(3, 3, '2026-01-27 20:00:00'),
(4, 4, '2026-01-27 19:00:00'),
(5, 2, '2026-01-27 16:00:00'),
(5, 2, '2026-01-27 21:00:00');

-- Vue pour les séances avec informations complètes
CREATE VIEW v_screenings_complete AS
SELECT 
    s.id,
    s.start_time,
    m.title as movie_title,
    m.duration as movie_duration,
    m.genre as movie_genre,
    m.director as movie_director,
    r.name as room_name,
    r.capacity as room_capacity,
    r.type as room_type,
    DATE_ADD(s.start_time, INTERVAL m.duration MINUTE) as end_time
FROM screenings s
JOIN movies m ON s.movie_id = m.id
JOIN rooms r ON s.room_id = r.id
WHERE r.active = TRUE;

-- Trigger pour empêcher la suppression de films avec des séances
DELIMITER //

CREATE TRIGGER before_movie_delete
BEFORE DELETE ON movies
FOR EACH ROW
BEGIN
    DECLARE v_screening_count INT;
    
    SELECT COUNT(*) INTO v_screening_count
    FROM screenings
    WHERE movie_id = OLD.id;
    
    IF v_screening_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete a movie with scheduled screenings';
    END IF;
END//

DELIMITER ;

-- Trigger pour empêcher la suppression de salles avec des séances futures
DELIMITER //

CREATE TRIGGER before_room_delete
BEFORE DELETE ON rooms
FOR EACH ROW
BEGIN
    DECLARE v_future_screening_count INT;
    
    SELECT COUNT(*) INTO v_future_screening_count
    FROM screenings
    WHERE room_id = OLD.id AND start_time > NOW();
    
    IF v_future_screening_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete a room with future scheduled screenings';
    END IF;
END//

DELIMITER ;
