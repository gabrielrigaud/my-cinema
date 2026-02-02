<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Screening
 */
class Screening {
    private $db;
    private $table = 'screenings';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Récupère toutes les séances avec pagination
     */
    public function getAll($page = 1, $limit = ITEMS_PER_PAGE, $search = '', $roomId = '', $movieId = '') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT s.*, m.title as movie_title, m.duration as movie_duration, 
                       m.genre as movie_genre, m.director as movie_director,
                       r.name as room_name, r.capacity as room_capacity, r.type as room_type
                FROM {$this->table} s
                JOIN movies m ON s.movie_id = m.id
                JOIN rooms r ON s.room_id = r.id
                WHERE r.active = 1";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (m.title LIKE :search OR r.name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($roomId)) {
            $sql .= " AND s.room_id = :room_id";
            $params[':room_id'] = $roomId;
        }
        
        if (!empty($movieId)) {
            $sql .= " AND s.movie_id = :movie_id";
            $params[':movie_id'] = $movieId;
        }
        
        $sql .= " ORDER BY s.start_time DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Compte le nombre total de séances
     */
    public function count($search = '', $roomId = '', $movieId = '') {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} s
                JOIN movies m ON s.movie_id = m.id
                JOIN rooms r ON s.room_id = r.id
                WHERE r.active = 1";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (m.title LIKE :search OR r.name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($roomId)) {
            $sql .= " AND s.room_id = :room_id";
            $params[':room_id'] = $roomId;
        }
        
        if (!empty($movieId)) {
            $sql .= " AND s.movie_id = :movie_id";
            $params[':movie_id'] = $movieId;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    /**
     * Récupère une séance par son ID
     */
    public function getById($id) {
        $sql = "SELECT s.*, m.title as movie_title, m.duration as movie_duration, 
                       m.genre as movie_genre, m.director as movie_director,
                       r.name as room_name, r.capacity as room_capacity, r.type as room_type
                FROM {$this->table} s
                JOIN movies m ON s.movie_id = m.id
                JOIN rooms r ON s.room_id = r.id
                WHERE s.id = :id AND r.active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crée une nouvelle séance
     */
    public function create($data) {
        // Vérifier les conflits d'horaire
        if (!$this->checkConflict($data['room_id'], $data['start_time'], $data['movie_id'])) {
            throw new Exception("Schedule conflict detected in this room");
        }
        
        $sql = "INSERT INTO {$this->table} (movie_id, room_id, start_time) 
                VALUES (:movie_id, :room_id, :start_time)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':movie_id', $data['movie_id'], PDO::PARAM_INT);
        $stmt->bindValue(':room_id', $data['room_id'], PDO::PARAM_INT);
        $stmt->bindValue(':start_time', $data['start_time']);
        
        return $stmt->execute();
    }

    /**
     * Met à jour une séance
     */
    public function update($id, $data) {
        // Vérifier les conflits d'horaire
        if (!$this->checkConflict($data['room_id'], $data['start_time'], $data['movie_id'], $id)) {
            throw new Exception("Schedule conflict detected in this room");
        }
        
        $sql = "UPDATE {$this->table} SET 
                movie_id = :movie_id, 
                room_id = :room_id, 
                start_time = :start_time 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':movie_id', $data['movie_id'], PDO::PARAM_INT);
        $stmt->bindValue(':room_id', $data['room_id'], PDO::PARAM_INT);
        $stmt->bindValue(':start_time', $data['start_time']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Supprime une séance
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Vérifie les conflits d'horaire pour une séance
     */
    private function checkConflict($roomId, $startTime, $movieId, $excludeId = null) {
        // Récupérer la durée du film
        $sql = "SELECT duration FROM movies WHERE id = :movie_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':movie_id', $movieId, PDO::PARAM_INT);
        $stmt->execute();
        $movie = $stmt->fetch();
        
        if (!$movie) {
            throw new Exception("Movie not found");
        }
        
        $movieDuration = $movie['duration'];
        $start = new DateTime($startTime);
        $end = clone $start;
        $end->add(new DateInterval('PT' . $movieDuration . 'M'));
        
        // Récupérer toutes les séances dans la même salle
        $sql = "SELECT s.*, m.duration as movie_duration 
                FROM {$this->table} s
                JOIN movies m ON s.movie_id = m.id
                JOIN rooms r ON s.room_id = r.id
                WHERE s.room_id = :room_id AND r.active = 1";
        
        $params = [':room_id' => $roomId];
        
        if ($excludeId) {
            $sql .= " AND s.id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $screenings = $stmt->fetchAll();
        
        foreach ($screenings as $screening) {
            $screeningStart = new DateTime($screening['start_time']);
            $screeningEnd = clone $screeningStart;
            $screeningEnd->add(new DateInterval('PT' . $screening['movie_duration'] . 'M'));
            
            // Vérifier s'il y a un chevauchement
            if (($start >= $screeningStart && $start < $screeningEnd) ||
                ($end > $screeningStart && $end <= $screeningEnd) ||
                ($start <= $screeningStart && $end >= $screeningEnd)) {
                return false; // Conflit détecté
            }
        }
        
        return true; // Pas de conflit
    }

    /**
     * Récupère les séances pour une date donnée
     */
    public function getByDate($date, $roomId = null) {
        $sql = "SELECT s.*, m.title as movie_title, m.duration as movie_duration, 
                       m.genre as movie_genre, m.director as movie_director,
                       r.name as room_name, r.capacity as room_capacity, r.type as room_type
                FROM {$this->table} s
                JOIN movies m ON s.movie_id = m.id
                JOIN rooms r ON s.room_id = r.id
                WHERE DATE(s.start_time) = :date AND r.active = 1";
        
        $params = [':date' => $date];
        
        if ($roomId) {
            $sql .= " AND s.room_id = :room_id";
            $params[':room_id'] = $roomId;
        }
        
        $sql .= " ORDER BY s.start_time";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Récupère le planning par salle pour une période donnée
     */
    public function getPlanningByRoom($dateStart, $dateEnd) {
        $sql = "SELECT s.*, m.title as movie_title, m.duration as movie_duration, 
                       m.genre as movie_genre, m.director as movie_director,
                       r.name as room_name, r.capacity as room_capacity, r.type as room_type
                FROM {$this->table} s
                JOIN movies m ON s.movie_id = m.id
                JOIN rooms r ON s.room_id = r.id
                WHERE s.start_time BETWEEN :date_start AND :date_end AND r.active = 1
                ORDER BY r.name, s.start_time";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':date_start', $dateStart);
        $stmt->bindValue(':date_end', $dateEnd);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Récupère les séances à venir
     */
    public function getUpcoming($limit = 10) {
        $sql = "SELECT s.*, m.title as movie_title, m.duration as movie_duration, 
                       m.genre as movie_genre, m.director as movie_director,
                       r.name as room_name, r.capacity as room_capacity, r.type as room_type
                FROM {$this->table} s
                JOIN movies m ON s.movie_id = m.id
                JOIN rooms r ON s.room_id = r.id
                WHERE s.start_time > NOW() AND r.active = 1
                ORDER BY s.start_time
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Récupère les séances passées récentes
     */
    public function getRecentPast($limit = 10) {
        $sql = "SELECT s.*, m.title as movie_title, m.duration as movie_duration, 
                       m.genre as movie_genre, m.director as movie_director,
                       r.name as room_name, r.capacity as room_capacity, r.type as room_type
                FROM {$this->table} s
                JOIN movies m ON s.movie_id = m.id
                JOIN rooms r ON s.room_id = r.id
                WHERE s.start_time < NOW() AND r.active = 1
                ORDER BY s.start_time DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
