<?php

require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Contrôleur pour les salles
 */
class RoomController {
    private $roomModel;

    public function __construct() {
        $this->roomModel = new Room();
    }

    /**
     * Récupère toutes les salles
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

            $rooms = $this->roomModel->getAll($page, ITEMS_PER_PAGE, $search);
            $total = $this->roomModel->count($search);
            $totalPages = ceil($total / ITEMS_PER_PAGE);

            successResponse([
                'rooms' => $rooms,
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
     * Récupère une salle par son ID
     */
    public function show($id) {
        try {
            $room = $this->roomModel->getById($id);
            
            if (!$room) {
                errorResponse('Room not found', 404);
            }

            successResponse($room);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Crée une nouvelle salle
     */
    public function create() {
        try {
            $data = getJsonInput();
            
            // Validation des données requises
            validateRequired($data, ['name', 'capacity']);
            
            // Validation supplémentaire
            if (strlen($data['name']) < 2 || strlen($data['name']) > 100) {
                errorResponse('Name must be between 2 and 100 characters');
            }
            
            if ($data['capacity'] < 1 || $data['capacity'] > 1000) {
                errorResponse('Capacity must be between 1 and 1000');
            }
            
            if (isset($data['type']) && strlen($data['type']) > 50) {
                errorResponse('Type must be less than 50 characters');
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->roomModel->create($data)) {
                successResponse(null, 'Room created successfully');
            } else {
                errorResponse('Error creating room', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Met à jour une salle
     */
    public function update($id) {
        try {
            $data = getJsonInput();
            
            // Validation des données requises
            validateRequired($data, ['name', 'capacity']);
            
            // Validation supplémentaire
            if (strlen($data['name']) < 2 || strlen($data['name']) > 100) {
                errorResponse('Name must be between 2 and 100 characters');
            }
            
            if ($data['capacity'] < 1 || $data['capacity'] > 1000) {
                errorResponse('Capacity must be between 1 and 1000');
            }
            
            if (isset($data['type']) && strlen($data['type']) > 50) {
                errorResponse('Type must be less than 50 characters');
            }
            
            // Vérifier si la salle existe
            $room = $this->roomModel->getById($id);
            if (!$room) {
                errorResponse('Room not found', 404);
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->roomModel->update($id, $data)) {
                successResponse(null, 'Room updated successfully');
            } else {
                errorResponse('Error updating room', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Supprime une salle (soft delete)
     */
    public function delete($id) {
        try {
            // Vérifier si la salle existe
            $room = $this->roomModel->getById($id);
            if (!$room) {
                errorResponse('Room not found', 404);
            }
            
            if ($this->roomModel->delete($id)) {
                successResponse(null, 'Room deleted successfully');
            } else {
                errorResponse('Error deleting room', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère toutes les salles pour les listes déroulantes
     */
    public function getAllForSelect() {
        try {
            $rooms = $this->roomModel->getAllForSelect();
            successResponse($rooms);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère les types de salles distincts
     */
    public function getTypes() {
        try {
            $types = $this->roomModel->getTypes();
            successResponse($types);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Vérifie la disponibilité d'une salle
     */
    public function checkAvailability() {
        try {
            $data = getJsonInput();
            
            validateRequired($data, ['room_id', 'start_time', 'duration']);
            
            $available = $this->roomModel->checkAvailability(
                $data['room_id'],
                $data['start_time'],
                $data['duration'],
                $data['exclude_screening_id'] ?? null
            );
            
            successResponse([
                'available' => $available,
                'message' => $available ? 'Room available' : 'Room not available'
            ]);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Active ou désactive une salle
     */
    public function toggleActive($id) {
        try {
            $data = getJsonInput();
            
            if (!isset($data['active'])) {
                errorResponse('Active status is required');
            }
            
            // Vérifier si la salle existe
            $room = $this->roomModel->getById($id);
            if (!$room) {
                errorResponse('Room not found', 404);
            }
            
            if ($this->roomModel->toggleActive($id, $data['active'])) {
                $status = $data['active'] ? 'activated' : 'deactivated';
                successResponse(null, "Room $status successfully");
            } else {
                errorResponse('Error updating room status', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }
}
