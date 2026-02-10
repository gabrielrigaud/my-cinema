<?php
/**
 * Repository pour la gestion des salles
 * Gère toutes les interactions avec la table 'salles'
 */

class SalleRepository {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les salles non supprimées
     * @return array Liste des salles
     */
    public function findAll() {
        $query = "SELECT s.*,
                  (SELECT COUNT(*) FROM seances WHERE salle_id = s.id) as nb_seances
                  FROM salles s 
                  WHERE s.is_deleted = 0 
                  ORDER BY s.nom ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll();
    }

    /**
     * Récupère une salle par son ID
     * @param int $id ID de la salle
     * @return array|false Données de la salle ou false
     */
    public function findById(int $id) {
        $query = "SELECT s.*,
                  (SELECT COUNT(*) FROM seances WHERE salle_id = s.id) as nb_seances
                  FROM salles s 
                  WHERE s.id = ? AND s.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crée une nouvelle salle
     * @param string $nom Nom de la salle
     * @param int $capacite Capacité en nombre de places
     * @param string $type Type de salle (Standard, 3D, IMAX)
     * @return bool Succès de l'opération
     */
    public function create(string $nom, int $capacite, string $type = 'Standard') {
        $query = "INSERT INTO salles (nom, capacite, type, created_at) 
                  VALUES (?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$nom, $capacite, $type]);
    }

    /**
     * Met à jour une salle existante
     * @param int $id ID de la salle
     * @param string $nom Nouveau nom
     * @param int $capacite Nouvelle capacité
     * @param string $type Nouveau type
     * @return bool Succès de l'opération
     */
    public function update(int $id, string $nom, int $capacite, string $type) {
        $query = "UPDATE salles 
                  SET nom = ?, capacite = ?, type = ?, updated_at = NOW() 
                  WHERE id = ? AND is_deleted = 0";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$nom, $capacite, $type, $id]);
    }

    /**
     * Suppression logique d'une salle (soft delete)
     * @param int $id ID de la salle
     * @return bool Succès de l'opération
     */
    public function delete(int $id) {
        $query = "UPDATE salles SET is_deleted = 1, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Vérifie si une salle a des séances associées
     * @param int $id ID de la salle
     * @return bool True si la salle a des séances
     */
    public function hasSeances(int $id): bool {
        $query = "SELECT COUNT(*) FROM seances WHERE salle_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Compte le nombre total de salles
     * @return int Nombre de salles
     */
    public function count(): int {
        $query = "SELECT COUNT(*) FROM salles WHERE is_deleted = 0";
        $stmt = $this->db->query($query);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Calcule la capacité totale de toutes les salles
     * @return int Capacité totale
     */
    public function getTotalCapacity(): int {
        $query = "SELECT SUM(capacite) FROM salles WHERE is_deleted = 0";
        $stmt = $this->db->query($query);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Vérifie si un nom de salle existe déjà
     * @param string $nom Nom à vérifier
     * @param int|null $excludeId ID à exclure (pour la modification)
     * @return bool True si le nom existe
     */
    public function nameExists(string $nom, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) FROM salles WHERE nom = ? AND is_deleted = 0";
        $params = [$nom];
        
        if ($excludeId !== null) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
