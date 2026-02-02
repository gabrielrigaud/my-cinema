<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Film
 */
class Film {
    private $db;
    private $table = 'films';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Récupère tous les films avec pagination
     */
    public function getAll($page = 1, $limit = ITEMS_PER_PAGE, $search = '', $genre = '', $annee = '') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND titre LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($genre)) {
            $sql .= " AND genre = :genre";
            $params[':genre'] = $genre;
        }
        
        if (!empty($annee)) {
            $sql .= " AND annee_sortie = :annee";
            $params[':annee'] = $annee;
        }
        
        $sql .= " ORDER BY titre LIMIT :limit OFFSET :offset";
        
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
    public function count($search = '', $genre = '', $annee = '') {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND titre LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($genre)) {
            $sql .= " AND genre = :genre";
            $params[':genre'] = $genre;
        }
        
        if (!empty($annee)) {
            $sql .= " AND annee_sortie = :annee";
            $params[':annee'] = $annee;
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
        $sql = "INSERT INTO {$this->table} (titre, description, duree, annee_sortie, genre, affiche) 
                VALUES (:titre, :description, :duree, :annee_sortie, :genre, :affiche)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':titre', $data['titre']);
        $stmt->bindValue(':description', $data['description'] ?? '');
        $stmt->bindValue(':duree', $data['duree'], PDO::PARAM_INT);
        $stmt->bindValue(':annee_sortie', $data['annee_sortie'], PDO::PARAM_INT);
        $stmt->bindValue(':genre', $data['genre']);
        $stmt->bindValue(':affiche', $data['affiche'] ?? '');
        
        return $stmt->execute();
    }

    /**
     * Met à jour un film
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                titre = :titre, 
                description = :description, 
                duree = :duree, 
                annee_sortie = :annee_sortie, 
                genre = :genre, 
                affiche = :affiche 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':titre', $data['titre']);
        $stmt->bindValue(':description', $data['description'] ?? '');
        $stmt->bindValue(':duree', $data['duree'], PDO::PARAM_INT);
        $stmt->bindValue(':annee_sortie', $data['annee_sortie'], PDO::PARAM_INT);
        $stmt->bindValue(':genre', $data['genre']);
        $stmt->bindValue(':affiche', $data['affiche'] ?? '');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Supprime un film
     */
    public function delete($id) {
        // Vérifier si le film a des séances
        $sql = "SELECT COUNT(*) as count FROM seances WHERE film_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            throw new Exception("Impossible de supprimer ce film : il a des séances programmées");
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
        $sql = "SELECT DISTINCT genre FROM {$this->table} ORDER BY genre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère toutes les années distinctes
     */
    public function getAnnees() {
        $sql = "SELECT DISTINCT annee_sortie FROM {$this->table} ORDER BY annee_sortie DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
