<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Movie
 */
class Movie {
    private $db;
    private $table = 'movies';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Récupère tous les films avec pagination
     */
    public function getAll($page = 1, $limit = ITEMS_PER_PAGE, $search = '', $genre = '', $year = '') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND title LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($genre)) {
            $sql .= " AND genre = :genre";
            $params[':genre'] = $genre;
        }
        
        if (!empty($year)) {
            $sql .= " AND release_year = :year";
            $params[':year'] = $year;
        }
        
        $sql .= " ORDER BY title LIMIT :limit OFFSET :offset";
        
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
     * Compte le nombre total de films
     */
    public function count($search = '', $genre = '', $year = '') {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND title LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($genre)) {
            $sql .= " AND genre = :genre";
            $params[':genre'] = $genre;
        }
        
        if (!empty($year)) {
            $sql .= " AND release_year = :year";
            $params[':year'] = $year;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    /**
     * Récupère un film par son ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crée un nouveau film
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (title, description, duration, release_year, genre, director) 
                VALUES (:title, :description, :duration, :release_year, :genre, :director)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':description', $data['description'] ?? '');
        $stmt->bindValue(':duration', $data['duration'], PDO::PARAM_INT);
        $stmt->bindValue(':release_year', $data['release_year'], PDO::PARAM_INT);
        $stmt->bindValue(':genre', $data['genre'] ?? '');
        $stmt->bindValue(':director', $data['director'] ?? '');
        
        return $stmt->execute();
    }

    /**
     * Met à jour un film
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                title = :title, 
                description = :description, 
                duration = :duration, 
                release_year = :release_year, 
                genre = :genre, 
                director = :director 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':description', $data['description'] ?? '');
        $stmt->bindValue(':duration', $data['duration'], PDO::PARAM_INT);
        $stmt->bindValue(':release_year', $data['release_year'], PDO::PARAM_INT);
        $stmt->bindValue(':genre', $data['genre'] ?? '');
        $stmt->bindValue(':director', $data['director'] ?? '');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Supprime un film
     */
    public function delete($id) {
        // Vérifier si le film a des séances
        $sql = "SELECT COUNT(*) as count FROM screenings WHERE movie_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            throw new Exception("Cannot delete a movie with scheduled screenings");
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Récupère tous les genres distincts
     */
    public function getGenres() {
        $sql = "SELECT DISTINCT genre FROM {$this->table} WHERE genre IS NOT NULL AND genre != '' ORDER BY genre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère toutes les années distinctes
     */
    public function getYears() {
        $sql = "SELECT DISTINCT release_year FROM {$this->table} ORDER BY release_year DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère tous les réalisateurs distincts
     */
    public function getDirectors() {
        $sql = "SELECT DISTINCT director FROM {$this->table} WHERE director IS NOT NULL AND director != '' ORDER BY director";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
