<?php
/**
 * Repository pour la gestion des films
 * Gère toutes les interactions avec la table 'films'
 */

class FilmRepository {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Récupère tous les films non supprimés
     * @param array $filters Filtres optionnels (search, genre, annee)
     * @return array Liste des films
     */
    public function findAll($filters = []) {
        $query = "SELECT * FROM films WHERE is_deleted = 0";
        $params = [];

        // Filtre par recherche de titre
        if (!empty($filters['search'])) {
            $query .= " AND titre LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        // Filtre par genre
        if (!empty($filters['genre'])) {
            $query .= " AND genre = ?";
            $params[] = $filters['genre'];
        }

        // Filtre par année
        if (!empty($filters['annee'])) {
            $query .= " AND annee = ?";
            $params[] = $filters['annee'];
        }

        $query .= " ORDER BY titre ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupère un film par son ID
     * @param int $id ID du film
     * @return array|false Données du film ou false
     */
    public function findById(int $id) {
        $query = "SELECT f.*, 
                  (SELECT COUNT(*) FROM seances WHERE film_id = f.id) as nb_seances
                  FROM films f 
                  WHERE f.id = ? AND f.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crée un nouveau film
     * @param string $titre Titre du film
     * @param int $duree Durée en minutes
     * @param int $annee Année de sortie
     * @param string $genre Genre du film
     * @return bool Succès de l'opération
     */
    public function create(string $titre, int $duree, int $annee, string $genre) {
        $query = "INSERT INTO films (titre, duree, annee, genre, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$titre, $duree, $annee, $genre]);
    }

    /**
     * Met à jour un film existant
     * @param int $id ID du film
     * @param string $titre Nouveau titre
     * @param int $duree Nouvelle durée
     * @param int $annee Nouvelle année
     * @param string $genre Nouveau genre
     * @return bool Succès de l'opération
     */
    public function update(int $id, string $titre, int $duree, int $annee, string $genre) {
        $query = "UPDATE films 
                  SET titre = ?, duree = ?, annee = ?, genre = ?, updated_at = NOW() 
                  WHERE id = ? AND is_deleted = 0";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$titre, $duree, $annee, $genre, $id]);
    }

    /**
     * Suppression logique d'un film (soft delete)
     * @param int $id ID du film
     * @return bool Succès de l'opération
     */
    public function delete(int $id) {
        $query = "UPDATE films SET is_deleted = 1, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Vérifie si un film a des séances associées
     * @param int $id ID du film
     * @return bool True si le film a des séances
     */
    public function hasSeances(int $id): bool {
        $query = "SELECT COUNT(*) FROM seances WHERE film_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Compte le nombre total de films
     * @return int Nombre de films
     */
    public function count(): int {
        $query = "SELECT COUNT(*) FROM films WHERE is_deleted = 0";
        $stmt = $this->db->query($query);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Récupère les films ajoutés ce mois
     * @return int Nombre de films ajoutés ce mois
     */
    public function countThisMonth(): int {
        $query = "SELECT COUNT(*) FROM films 
                  WHERE is_deleted = 0 
                  AND MONTH(created_at) = MONTH(CURRENT_DATE())
                  AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $stmt = $this->db->query($query);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Récupère tous les genres distincts
     * @return array Liste des genres
     */
    public function getGenres(): array {
        $query = "SELECT DISTINCT genre FROM films WHERE is_deleted = 0 ORDER BY genre";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
