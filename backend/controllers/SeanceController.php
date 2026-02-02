<?php

require_once __DIR__ . '/../models/Seance.php';
require_once __DIR__ . '/../models/Film.php';
require_once __DIR__ . '/../models/Salle.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Contrôleur pour les séances
 */
class SeanceController {
    private $seanceModel;
    private $filmModel;
    private $salleModel;

    public function __construct() {
        $this->seanceModel = new Seance();
        $this->filmModel = new Film();
        $this->salleModel = new Salle();
    }

    /**
     * Récupère toutes les séances
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
            $salleId = isset($_GET['salle_id']) ? (int)$_GET['salle_id'] : '';
            $filmId = isset($_GET['film_id']) ? (int)$_GET['film_id'] : '';

            $seances = $this->seanceModel->getAll($page, ITEMS_PER_PAGE, $search, $salleId, $filmId);
            $total = $this->seanceModel->count($search, $salleId, $filmId);
            $totalPages = ceil($total / ITEMS_PER_PAGE);

            successResponse([
                'seances' => $seances,
                'pagination' => [
                    'page' => $page,
                    'items_per_page' => ITEMS_PER_PAGE,
                    'total_items' => $total,
                    'total_pages' => $totalPages
                ]
            ]);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère une séance par son ID
     */
    public function show($id) {
        try {
            $seance = $this->seanceModel->getById($id);
            
            if (!$seance) {
                errorResponse('Séance non trouvée', 404);
            }

            successResponse($seance);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Crée une nouvelle séance
     */
    public function create() {
        try {
            $data = getJsonInput();
            
            // Validation des données requises
            validateRequired($data, ['film_id', 'salle_id', 'date_seance']);
            
            // Validation supplémentaire
            if ($data['film_id'] < 1) {
                errorResponse('ID du film invalide');
            }
            
            if ($data['salle_id'] < 1) {
                errorResponse('ID de la salle invalide');
            }
            
            if (!strtotime($data['date_seance'])) {
                errorResponse('Format de date invalide');
            }
            
            $dateSeance = new DateTime($data['date_seance']);
            $now = new DateTime();
            
            if ($dateSeance < $now) {
                errorResponse('La date de la séance ne peut pas être dans le passé');
            }
            
            // Vérifier si le film existe
            $film = $this->filmModel->getById($data['film_id']);
            if (!$film) {
                errorResponse('Film non trouvé', 404);
            }
            
            // Vérifier si la salle existe
            $salle = $this->salleModel->getById($data['salle_id']);
            if (!$salle) {
                errorResponse('Salle non trouvée', 404);
            }
            
            // Validation du prix
            if (isset($data['prix']) && ($data['prix'] < 0 || $data['prix'] > 100)) {
                errorResponse('Le prix doit être compris entre 0 et 100 €');
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            $data['prix'] = $data['prix'] ?? 8.50;
            
            if ($this->seanceModel->create($data)) {
                successResponse(null, 'Séance créée avec succès');
            } else {
                errorResponse('Erreur lors de la création de la séance', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Met à jour une séance
     */
    public function update($id) {
        try {
            $data = getJsonInput();
            
            // Validation des données requises
            validateRequired($data, ['film_id', 'salle_id', 'date_seance']);
            
            // Validation supplémentaire
            if ($data['film_id'] < 1) {
                errorResponse('ID du film invalide');
            }
            
            if ($data['salle_id'] < 1) {
                errorResponse('ID de la salle invalide');
            }
            
            if (!strtotime($data['date_seance'])) {
                errorResponse('Format de date invalide');
            }
            
            $dateSeance = new DateTime($data['date_seance']);
            $now = new DateTime();
            
            if ($dateSeance < $now) {
                errorResponse('La date de la séance ne peut pas être dans le passé');
            }
            
            // Vérifier si la séance existe
            $seance = $this->seanceModel->getById($id);
            if (!$seance) {
                errorResponse('Séance non trouvée', 404);
            }
            
            // Vérifier si le film existe
            $film = $this->filmModel->getById($data['film_id']);
            if (!$film) {
                errorResponse('Film non trouvé', 404);
            }
            
            // Vérifier si la salle existe
            $salle = $this->salleModel->getById($data['salle_id']);
            if (!$salle) {
                errorResponse('Salle non trouvée', 404);
            }
            
            // Validation du prix
            if (isset($data['prix']) && ($data['prix'] < 0 || $data['prix'] > 100)) {
                errorResponse('Le prix doit être compris entre 0 et 100 €');
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->seanceModel->update($id, $data)) {
                successResponse(null, 'Séance mise à jour avec succès');
            } else {
                errorResponse('Erreur lors de la mise à jour de la séance', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Supprime une séance
     */
    public function delete($id) {
        try {
            // Vérifier si la séance existe
            $seance = $this->seanceModel->getById($id);
            if (!$seance) {
                errorResponse('Séance non trouvée', 404);
            }
            
            if ($this->seanceModel->delete($id)) {
                successResponse(null, 'Séance supprimée avec succès');
            } else {
                errorResponse('Erreur lors de la suppression de la séance', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère les séances pour une date donnée
     */
    public function getByDate($date) {
        try {
            if (!strtotime($date)) {
                errorResponse('Format de date invalide');
            }
            
            $salleId = isset($_GET['salle_id']) ? (int)$_GET['salle_id'] : null;
            $seances = $this->seanceModel->getByDate($date, $salleId);
            
            successResponse($seances);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère le planning par salle
     */
    public function getPlanning() {
        try {
            $dateDebut = isset($_GET['date_debut']) ? $_GET['date_debut'] : date('Y-m-d');
            $dateFin = isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-m-d', strtotime('+7 days'));
            
            if (!strtotime($dateDebut) || !strtotime($dateFin)) {
                errorResponse('Format de date invalide');
            }
            
            if ($dateDebut > $dateFin) {
                errorResponse('La date de début doit être antérieure à la date de fin');
            }
            
            $planning = $this->seanceModel->getPlanningBySalle($dateDebut, $dateFin);
            
            // Organiser les données par salle
            $planningParSalle = [];
            foreach ($planning as $seance) {
                $salleNom = $seance['salle_nom'];
                if (!isset($planningParSalle[$salleNom])) {
                    $planningParSalle[$salleNom] = [
                        'salle_id' => $seance['salle_id'],
                        'nom' => $seance['salle_nom'],
                        'capacite' => $seance['salle_capacite'],
                        'type' => $seance['salle_type'],
                        'seances' => []
                    ];
                }
                $planningParSalle[$salleNom]['seances'][] = $seance;
            }
            
            successResponse(array_values($planningParSalle));
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère les séances à venir
     */
    public function getUpcoming() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $limit = min($limit, 50); // Limiter à 50 maximum
            
            $seances = $this->seanceModel->getUpcoming($limit);
            
            successResponse($seances);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }
}
