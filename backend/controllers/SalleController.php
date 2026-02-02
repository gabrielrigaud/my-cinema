<?php

require_once __DIR__ . '/../models/Salle.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Contrôleur pour les salles
 */
class SalleController {
    private $salleModel;

    public function __construct() {
        $this->salleModel = new Salle();
    }

    /**
     * Récupère toutes les salles
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

            $salles = $this->salleModel->getAll($page, ITEMS_PER_PAGE, $search);
            $total = $this->salleModel->count($search);
            $totalPages = ceil($total / ITEMS_PER_PAGE);

            successResponse([
                'salles' => $salles,
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
            $salle = $this->salleModel->getById($id);
            
            if (!$salle) {
                errorResponse('Salle non trouvée', 404);
            }

            successResponse($salle);
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
            validateRequired($data, ['nom', 'capacite']);
            
            // Validation supplémentaire
            if (strlen($data['nom']) < 2 || strlen($data['nom']) > 100) {
                errorResponse('Le nom doit contenir entre 2 et 100 caractères');
            }
            
            if ($data['capacite'] < 1 || $data['capacite'] > 1000) {
                errorResponse('La capacité doit être comprise entre 1 et 1000 places');
            }
            
            if (isset($data['type']) && (strlen($data['type']) < 2 || strlen($data['type']) > 50)) {
                errorResponse('Le type doit contenir entre 2 et 50 caractères');
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->salleModel->create($data)) {
                successResponse(null, 'Salle créée avec succès');
            } else {
                errorResponse('Erreur lors de la création de la salle', 500);
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
            validateRequired($data, ['nom', 'capacite']);
            
            // Validation supplémentaire
            if (strlen($data['nom']) < 2 || strlen($data['nom']) > 100) {
                errorResponse('Le nom doit contenir entre 2 et 100 caractères');
            }
            
            if ($data['capacite'] < 1 || $data['capacite'] > 1000) {
                errorResponse('La capacité doit être comprise entre 1 et 1000 places');
            }
            
            if (isset($data['type']) && (strlen($data['type']) < 2 || strlen($data['type']) > 50)) {
                errorResponse('Le type doit contenir entre 2 et 50 caractères');
            }
            
            // Vérifier si la salle existe
            $salle = $this->salleModel->getById($id);
            if (!$salle) {
                errorResponse('Salle non trouvée', 404);
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->salleModel->update($id, $data)) {
                successResponse(null, 'Salle mise à jour avec succès');
            } else {
                errorResponse('Erreur lors de la mise à jour de la salle', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Supprime une salle
     */
    public function delete($id) {
        try {
            // Vérifier si la salle existe
            $salle = $this->salleModel->getById($id);
            if (!$salle) {
                errorResponse('Salle non trouvée', 404);
            }
            
            if ($this->salleModel->delete($id)) {
                successResponse(null, 'Salle supprimée avec succès');
            } else {
                errorResponse('Erreur lors de la suppression de la salle', 500);
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
            $salles = $this->salleModel->getAllForSelect();
            successResponse($salles);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère les types de salles disponibles
     */
    public function getTypes() {
        try {
            $types = $this->salleModel->getTypes();
            successResponse($types);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Vérifie la disponibilité d'une salle
     */
    public function checkDisponibilite() {
        try {
            $data = getJsonInput();
            
            validateRequired($data, ['salle_id', 'date_debut', 'date_fin']);
            
            $disponible = $this->salleModel->checkDisponibilite(
                $data['salle_id'],
                $data['date_debut'],
                $data['date_fin'],
                $data['exclude_seance_id'] ?? null
            );
            
            successResponse([
                'disponible' => $disponible,
                'message' => $disponible ? 'Salle disponible' : 'Salle non disponible'
            ]);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }
}
