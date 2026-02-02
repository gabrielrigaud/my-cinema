<?php

require_once __DIR__ . '/../models/Screening.php';
require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Contrôleur pour les séances
 */
class ScreeningController {
    private $screeningModel;
    private $movieModel;
    private $roomModel;

    public function __construct() {
        $this->screeningModel = new Screening();
        $this->movieModel = new Movie();
        $this->roomModel = new Room();
    }

    /**
     * Récupère toutes les séances
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
            $roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : '';
            $movieId = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : '';

            $screenings = $this->screeningModel->getAll($page, ITEMS_PER_PAGE, $search, $roomId, $movieId);
            $total = $this->screeningModel->count($search, $roomId, $movieId);
            $totalPages = ceil($total / ITEMS_PER_PAGE);

            successResponse([
                'screenings' => $screenings,
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
            $screening = $this->screeningModel->getById($id);
            
            if (!$screening) {
                errorResponse('Screening not found', 404);
            }

            successResponse($screening);
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
            validateRequired($data, ['movie_id', 'room_id', 'start_time']);
            
            // Validation supplémentaire
            if ($data['movie_id'] < 1) {
                errorResponse('Invalid movie ID');
            }
            
            if ($data['room_id'] < 1) {
                errorResponse('Invalid room ID');
            }
            
            if (!strtotime($data['start_time'])) {
                errorResponse('Invalid start time format');
            }
            
            $startTime = new DateTime($data['start_time']);
            $now = new DateTime();
            
            if ($startTime < $now) {
                errorResponse('Start time cannot be in the past');
            }
            
            // Vérifier si le film existe
            $movie = $this->movieModel->getById($data['movie_id']);
            if (!$movie) {
                errorResponse('Movie not found', 404);
            }
            
            // Vérifier si la salle existe et est active
            $room = $this->roomModel->getById($data['room_id']);
            if (!$room) {
                errorResponse('Room not found', 404);
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->screeningModel->create($data)) {
                successResponse(null, 'Screening created successfully');
            } else {
                errorResponse('Error creating screening', 500);
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
            validateRequired($data, ['movie_id', 'room_id', 'start_time']);
            
            // Validation supplémentaire
            if ($data['movie_id'] < 1) {
                errorResponse('Invalid movie ID');
            }
            
            if ($data['room_id'] < 1) {
                errorResponse('Invalid room ID');
            }
            
            if (!strtotime($data['start_time'])) {
                errorResponse('Invalid start time format');
            }
            
            $startTime = new DateTime($data['start_time']);
            $now = new DateTime();
            
            if ($startTime < $now) {
                errorResponse('Start time cannot be in the past');
            }
            
            // Vérifier si la séance existe
            $screening = $this->screeningModel->getById($id);
            if (!$screening) {
                errorResponse('Screening not found', 404);
            }
            
            // Vérifier si le film existe
            $movie = $this->movieModel->getById($data['movie_id']);
            if (!$movie) {
                errorResponse('Movie not found', 404);
            }
            
            // Vérifier si la salle existe et est active
            $room = $this->roomModel->getById($data['room_id']);
            if (!$room) {
                errorResponse('Room not found', 404);
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->screeningModel->update($id, $data)) {
                successResponse(null, 'Screening updated successfully');
            } else {
                errorResponse('Error updating screening', 500);
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
            $screening = $this->screeningModel->getById($id);
            if (!$screening) {
                errorResponse('Screening not found', 404);
            }
            
            if ($this->screeningModel->delete($id)) {
                successResponse(null, 'Screening deleted successfully');
            } else {
                errorResponse('Error deleting screening', 500);
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
                errorResponse('Invalid date format');
            }
            
            $roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : null;
            $screenings = $this->screeningModel->getByDate($date, $roomId);
            
            successResponse($screenings);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère le planning par salle
     */
    public function getPlanning() {
        try {
            $dateStart = isset($_GET['date_start']) ? $_GET['date_start'] : date('Y-m-d');
            $dateEnd = isset($_GET['date_end']) ? $_GET['date_end'] : date('Y-m-d', strtotime('+7 days'));
            
            if (!strtotime($dateStart) || !strtotime($dateEnd)) {
                errorResponse('Invalid date format');
            }
            
            if ($dateStart > $dateEnd) {
                errorResponse('Start date must be before end date');
            }
            
            $planning = $this->screeningModel->getPlanningByRoom($dateStart, $dateEnd);
            
            // Organiser les données par salle
            $planningByRoom = [];
            foreach ($planning as $screening) {
                $roomName = $screening['room_name'];
                if (!isset($planningByRoom[$roomName])) {
                    $planningByRoom[$roomName] = [
                        'room_id' => $screening['room_id'],
                        'name' => $screening['room_name'],
                        'capacity' => $screening['room_capacity'],
                        'type' => $screening['room_type'],
                        'screenings' => []
                    ];
                }
                $planningByRoom[$roomName]['screenings'][] = $screening;
            }
            
            successResponse(array_values($planningByRoom));
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
            
            $screenings = $this->screeningModel->getUpcoming($limit);
            
            successResponse($screenings);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère les séances passées récentes
     */
    public function getRecentPast() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $limit = min($limit, 50); // Limiter à 50 maximum
            
            $screenings = $this->screeningModel->getRecentPast($limit);
            
            successResponse($screenings);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }
}
