<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Séance
 */
class Seance {
    private $db;
    private $table = 'seances';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Récupère toutes les séances avec pagination
     */
    public function getAll($page = 1, $limit = ITEMS_PER_PAGE, $search = '', $salleId = '', $filmId = '') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT s.*, f.titre as film_titre, f.duree as film_duree, 
                       sa.nom as salle_nom, sa.capacite as salle_capacite, sa.type as salle_type
                FROM {$this->table} s
                JOIN films f ON s.film_id = f.id
                JOIN salles sa ON s.salle_id = sa.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (f.titre LIKE :search OR sa.nom LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($salleId)) {
            $sql .= " AND s.salle_id = :salle_id";
            $params[':salle_id'] = $salleId;
        }
        
        if (!empty($filmId)) {
            $sql .= " AND s.film_id = :film_id";
            $params[':film_id'] = $filmId;
        }
        
        $sql .= " ORDER BY s.date_seance LIMIT :limit OFFSET :offset";
        
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
    public function count($search = '', $salleId = '', $filmId = '') {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} s
                JOIN films f ON s.film_id = f.id
                JOIN salles sa ON s.salle_id = sa.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (f.titre LIKE :search OR sa.nom LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($salleId)) {
            $sql .= " AND s.salle_id = :salle_id";
            $params[':salle_id'] = $salleId;
        }
        
        if (!empty($filmId)) {
            $sql .= " AND s.film_id = :film_id";
            $params[':film_id'] = $filmId;
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
        $sql = "SELECT s.*, f.titre as film_titre, f.duree as film_duree, 
                       sa.nom as salle_nom, sa.capacite as salle_capacite, sa.type as salle_type
                FROM {$this->table} s
                JOIN films f ON s.film_id = f.id
                JOIN salles sa ON s.salle_id = sa.id
                WHERE s.id = :id";
        
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
        if (!$this->verifierConflit($data['salle_id'], $data['date_seance'], $data['film_id'])) {
            throw new Exception("Conflit d'horaire détecté dans cette salle");
        }
        
        $sql = "INSERT INTO {$this->table} (film_id, salle_id, date_seance, prix) 
                VALUES (:film_id, :salle_id, :date_seance, :prix)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':film_id', $data['film_id'], PDO::PARAM_INT);
        $stmt->bindValue(':salle_id', $data['salle_id'], PDO::PARAM_INT);
        $stmt->bindValue(':date_seance', $data['date_seance']);
        $stmt->bindValue(':prix', $data['prix']);
        
        return $stmt->execute();
    }

    /**
     * Met à jour une séance
     */
    public function update($id, $data) {
        // Vérifier les conflits d'horaire
        if (!$this->verifierConflit($data['salle_id'], $data['date_seance'], $data['film_id'], $id)) {
            throw new Exception("Conflit d'horaire détecté dans cette salle");
        }
        
        $sql = "UPDATE {$this->table} SET 
                film_id = :film_id, 
                salle_id = :salle_id, 
                date_seance = :date_seance, 
                prix = :prix 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':film_id', $data['film_id'], PDO::PARAM_INT);
        $stmt->bindValue(':salle_id', $data['salle_id'], PDO::PARAM_INT);
        $stmt->bindValue(':date_seance', $data['date_seance']);
        $stmt->bindValue(':prix', $data['prix']);
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
    private function verifierConflit($salleId, $dateSeance, $filmId, $excludeId = null) {
        // Récupérer la durée du film
        $sql = "SELECT duree FROM films WHERE id = :film_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':film_id', $filmId, PDO::PARAM_INT);
        $stmt->execute();
        $film = $stmt->fetch();
        
        if (!$film) {
            throw new Exception("Film non trouvé");
        }
        
        $dureeFilm = $film['duree'];
        $debut = new DateTime($dateSeance);
        $fin = clone $debut;
        $fin->add(new DateInterval('PT' . $dureeFilm . 'M'));
        
        // Récupérer toutes les séances dans la même salle
        $sql = "SELECT s.*, f.duree as film_duree 
                FROM {$this->table} s
                JOIN films f ON s.film_id = f.id
                WHERE s.salle_id = :salle_id";
        
        $params = [':salle_id' => $salleId];
        
        if ($excludeId) {
            $sql .= " AND s.id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
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
            
            // Vérifier s'il y a un chevauchement
            if (($debut >= $seanceDebut && $debut < $seanceFin) ||
                ($fin > $seanceDebut && $fin <= $seanceFin) ||
                ($debut <= $seanceDebut && $fin >= $seanceFin)) {
                return false; // Conflit détecté
            }
        }
        
        return true; // Pas de conflit
    }

    /**
     * Récupère les séances pour une date donnée
     */
    public function getByDate($date, $salleId = null) {
        $sql = "SELECT s.*, f.titre as film_titre, f.duree as film_duree, 
                       sa.nom as salle_nom, sa.capacite as salle_capacite, sa.type as salle_type
                FROM {$this->table} s
                JOIN films f ON s.film_id = f.id
                JOIN salles sa ON s.salle_id = sa.id
                WHERE DATE(s.date_seance) = :date";
        
        $params = [':date' => $date];
        
        if ($salleId) {
            $sql .= " AND s.salle_id = :salle_id";
            $params[':salle_id'] = $salleId;
        }
        
        $sql .= " ORDER BY s.date_seance";
        
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
    public function getPlanningBySalle($dateDebut, $dateFin) {
        $sql = "SELECT s.*, f.titre as film_titre, f.duree as film_duree, 
                       sa.nom as salle_nom, sa.capacite as salle_capacite, sa.type as salle_type
                FROM {$this->table} s
                JOIN films f ON s.film_id = f.id
                JOIN salles sa ON s.salle_id = sa.id
                WHERE s.date_seance BETWEEN :date_debut AND :date_fin
                ORDER BY sa.nom, s.date_seance";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':date_debut', $dateDebut);
        $stmt->bindValue(':date_fin', $dateFin);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Récupère les séances à venir
     */
    public function getUpcoming($limit = 10) {
        $sql = "SELECT s.*, f.titre as film_titre, f.duree as film_duree, 
                       sa.nom as salle_nom, sa.capacite as salle_capacite, sa.type as salle_type
                FROM {$this->table} s
                JOIN films f ON s.film_id = f.id
                JOIN salles sa ON s.salle_id = sa.id
                WHERE s.date_seance > NOW()
                ORDER BY s.date_seance
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
