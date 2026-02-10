<?php
/**
 * Repository pour la gestion des séances
 * Gère toutes les interactions avec la table 'seances'
 */

class SeanceRepository {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les séances avec les informations du film et de la salle
     * @param array $filters Filtres optionnels (date, salle_id, film_id)
     * @return array Liste des séances
     */
    public function findAll($filters = []) {
        $query = "SELECT s.*, 
                  f.titre as film_titre, f.duree as film_duree, f.genre as film_genre,
                  sa.nom as salle_nom, sa.capacite as salle_capacite, sa.type as salle_type
                  FROM seances s
                  INNER JOIN films f ON s.film_id = f.id
                  INNER JOIN salles sa ON s.salle_id = sa.id
                  WHERE 1=1";
        $params = [];

        // Filtre par date
        if (!empty($filters['date'])) {
            $query .= " AND s.date = ?";
            $params[] = $filters['date'];
        }

        // Filtre par salle
        if (!empty($filters['salle_id'])) {
            $query .= " AND s.salle_id = ?";
            $params[] = $filters['salle_id'];
        }

        // Filtre par film
        if (!empty($filters['film_id'])) {
            $query .= " AND s.film_id = ?";
            $params[] = $filters['film_id'];
        }

        $query .= " ORDER BY s.date DESC, s.heure ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupère une séance par son ID
     * @param int $id ID de la séance
     * @return array|false Données de la séance ou false
     */
    public function findById(int $id) {
        $query = "SELECT s.*, 
                  f.titre as film_titre, f.duree as film_duree,
                  sa.nom as salle_nom
                  FROM seances s
                  INNER JOIN films f ON s.film_id = f.id
                  INNER JOIN salles sa ON s.salle_id = sa.id
                  WHERE s.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crée une nouvelle séance
     * @param int $filmId ID du film
     * @param int $salleId ID de la salle
     * @param string $date Date de la séance (YYYY-MM-DD)
     * @param string $heure Heure de la séance (HH:MM:SS)
     * @return bool Succès de l'opération
     */
    public function create(int $filmId, int $salleId, string $date, string $heure) {
        $query = "INSERT INTO seances (film_id, salle_id, date, heure, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$filmId, $salleId, $date, $heure]);
    }

    /**
     * Met à jour une séance existante
     * @param int $id ID de la séance
     * @param int $filmId Nouveau film
     * @param int $salleId Nouvelle salle
     * @param string $date Nouvelle date
     * @param string $heure Nouvelle heure
     * @return bool Succès de l'opération
     */
    public function update(int $id, int $filmId, int $salleId, string $date, string $heure) {
        $query = "UPDATE seances 
                  SET film_id = ?, salle_id = ?, date = ?, heure = ?, updated_at = NOW() 
                  WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$filmId, $salleId, $date, $heure, $id]);
    }

    /**
     * Supprime une séance
     * @param int $id ID de la séance
     * @return bool Succès de l'opération
     */
    public function delete(int $id) {
        $query = "DELETE FROM seances WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Vérifie s'il y a un chevauchement de séances dans une salle
     * @param int $salleId ID de la salle
     * @param string $date Date de la séance
     * @param string $heure Heure de début
     * @param int $duree Durée du film en minutes
     * @param int|null $excludeId ID de la séance à exclure (pour modification)
     * @return bool True s'il y a un conflit
     */
    public function checkOverlap(int $salleId, string $date, string $heure, int $duree, ?int $excludeId = null): bool {
        // Calcul de l'heure de fin (avec 15 min de battement pour le nettoyage)
        $heureDebut = new DateTime($date . ' ' . $heure);
        $heureFin = clone $heureDebut;
        $heureFin->modify('+' . ($duree + 15) . ' minutes');

        $query = "SELECT COUNT(*) FROM seances s
                  INNER JOIN films f ON s.film_id = f.id
                  WHERE s.salle_id = ? 
                  AND s.date = ?
                  AND (
                      (s.heure < ? AND DATE_ADD(CONCAT(s.date, ' ', s.heure), INTERVAL (f.duree + 15) MINUTE) > ?)
                      OR (s.heure >= ? AND s.heure < ?)
                  )";
        
        $params = [
            $salleId,
            $date,
            $heureFin->format('H:i:s'),
            $heureDebut->format('H:i:s'),
            $heureDebut->format('H:i:s'),
            $heureFin->format('H:i:s')
        ];

        if ($excludeId !== null) {
            $query .= " AND s.id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère les séances du jour
     * @return int Nombre de séances aujourd'hui
     */
    public function countToday(): int {
        $query = "SELECT COUNT(*) FROM seances WHERE date = CURDATE()";
        $stmt = $this->db->query($query);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Récupère les séances de la semaine
     * @return int Nombre de séances cette semaine
     */
    public function countThisWeek(): int {
        $query = "SELECT COUNT(*) FROM seances 
                  WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)";
        $stmt = $this->db->query($query);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Récupère les séances à venir
     * @param int $days Nombre de jours à venir (par défaut 7)
     * @return int Nombre de séances à venir
     */
    public function countUpcoming(int $days = 7): int {
        $query = "SELECT COUNT(*) FROM seances 
                  WHERE date >= CURDATE() 
                  AND date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$days]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Récupère les prochaines séances
     * @param int $limit Nombre de séances à récupérer
     * @return array Liste des prochaines séances
     */
    public function getUpcoming(int $limit = 5): array {
        $query = "SELECT s.*, 
                  f.titre as film_titre, f.duree,
                  sa.nom as salle_nom
                  FROM seances s
                  INNER JOIN films f ON s.film_id = f.id
                  INNER JOIN salles sa ON s.salle_id = sa.id
                  WHERE s.date >= CURDATE()
                  ORDER BY s.date ASC, s.heure ASC
                  LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
