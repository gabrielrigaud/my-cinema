<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Salle
 */
class Salle {
    private $db;
    private $table = 'salles';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Récupère toutes les salles
     */
    public function getAll($page = 1, $limit = ITEMS_PER_PAGE, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE nom LIKE :search OR type LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY nom LIMIT :limit OFFSET :offset";
        
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
     * Compte le nombre total de salles
     */
    public function count($search = '') {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE nom LIKE :search OR type LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    /**
     * Récupère une salle par son ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crée une nouvelle salle
     */
    public function create($data) {
        // Vérifier si le nom existe déjà
        if ($this->getByName($data['nom'])) {
            throw new Exception("Une salle avec ce nom existe déjà");
        }
        
        $sql = "INSERT INTO {$this->table} (nom, capacite, type) 
                VALUES (:nom, :capacite, :type)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nom', $data['nom']);
        $stmt->bindValue(':capacite', $data['capacite'], PDO::PARAM_INT);
        $stmt->bindValue(':type', $data['type'] ?? 'Standard');
        
        return $stmt->execute();
    }

    /**
     * Met à jour une salle
     */
    public function update($id, $data) {
        // Vérifier si le nom existe déjà (pour une autre salle)
        $existing = $this->getByName($data['nom']);
        if ($existing && $existing['id'] != $id) {
            throw new Exception("Une salle avec ce nom existe déjà");
        }
        
        $sql = "UPDATE {$this->table} SET 
                nom = :nom, 
                capacite = :capacite, 
                type = :type 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nom', $data['nom']);
        $stmt->bindValue(':capacite', $data['capacite'], PDO::PARAM_INT);
        $stmt->bindValue(':type', $data['type'] ?? 'Standard');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Supprime une salle (soft delete si des séances sont associées)
     */
    public function delete($id) {
        // Vérifier si la salle a des séances futures
        $sql = "SELECT COUNT(*) as count FROM seances 
                WHERE salle_id = :id AND date_seance > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $futureSeances = $stmt->fetch()['count'];
        
        if ($futureSeances > 0) {
            throw new Exception("Impossible de supprimer cette salle : elle a des séances futures programmées");
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Récupère une salle par son nom
     */
    public function getByName($nom) {
        $sql = "SELECT * FROM {$this->table} WHERE nom = :nom";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nom', $nom);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Récupère toutes les salles (pour les listes déroulantes)
     */
    public function getAllForSelect() {
        $sql = "SELECT id, nom, capacite, type FROM {$this->table} ORDER BY nom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère les types de salles distincts
     */
    public function getTypes() {
        $sql = "SELECT DISTINCT type FROM {$this->table} ORDER BY type";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Vérifie la disponibilité d'une salle pour une période donnée
     */
    public function checkDisponibilite($salleId, $dateDebut, $dateFin, $excludeSeanceId = null) {
        $sql = "SELECT s.*, f.duree as film_duree 
                FROM seances s 
                JOIN films f ON s.film_id = f.id 
                WHERE s.salle_id = :salle_id";
        
        $params = [':salle_id' => $salleId];
        
        if ($excludeSeanceId) {
            $sql .= " AND s.id != :exclude_id";
            $params[':exclude_id'] = $excludeSeanceId;
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $seances = $stmt->fetchAll();
        
        foreach ($seances as $seance) {
            $seanceDebut = new DateTime($seance['date_seance']);
            $seanceFin = clone $seanceDebut;
            $seanceFin->add(new DateInterval('PT' . $seance['film_duree'] . 'M'));
            
            $debut = new DateTime($dateDebut);
            $fin = new DateTime($dateFin);
            
            // Vérifier s'il y a un chevauchement
            if (($debut >= $seanceDebut && $debut < $seanceFin) ||
                ($fin > $seanceDebut && $fin <= $seanceFin) ||
                ($debut <= $seanceDebut && $fin >= $seanceFin)) {
                return false; // Non disponible
            }
        }
        
        return true; // Disponible
    }
}
