<?php

require_once __DIR__ . '/../models/Film.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Contrôleur pour les films
 */
class FilmController {
    private $filmModel;

    public function __construct() {
        $this->filmModel = new Film();
    }

    /**
     * Récupère tous les films
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
            $genre = isset($_GET['genre']) ? sanitizeInput($_GET['genre']) : '';
            $annee = isset($_GET['annee']) ? sanitizeInput($_GET['annee']) : '';

            $films = $this->filmModel->getAll($page, ITEMS_PER_PAGE, $search, $genre, $annee);
            $total = $this->filmModel->count($search, $genre, $annee);
            $totalPages = ceil($total / ITEMS_PER_PAGE);

            successResponse([
                'films' => $films,
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
     * Récupère un film par son ID
     */
    public function show($id) {
        try {
            $film = $this->filmModel->getById($id);
            
            if (!$film) {
                errorResponse('Film non trouvé', 404);
            }

            successResponse($film);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Crée un nouveau film
     */
    public function create() {
        try {
            $data = getJsonInput();
            
            // Validation des données requises
            validateRequired($data, ['titre', 'duree', 'annee_sortie', 'genre']);
            
            // Validation supplémentaire
            if (strlen($data['titre']) < 2 || strlen($data['titre']) > 255) {
                errorResponse('Le titre doit contenir entre 2 et 255 caractères');
            }
            
            if ($data['duree'] < 1 || $data['duree'] > 600) {
                errorResponse('La durée doit être comprise entre 1 et 600 minutes');
            }
            
            if ($data['annee_sortie'] < 1900 || $data['annee_sortie'] > date('Y') + 5) {
                errorResponse('L\'année de sortie doit être comprise entre 1900 et ' . (date('Y') + 5));
            }
            
            if (strlen($data['genre']) < 2 || strlen($data['genre']) > 100) {
                errorResponse('Le genre doit contenir entre 2 et 100 caractères');
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->filmModel->create($data)) {
                successResponse(null, 'Film créé avec succès');
            } else {
                errorResponse('Erreur lors de la création du film', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Met à jour un film
     */
    public function update($id) {
        try {
            $data = getJsonInput();
            
            // Validation des données requises
            validateRequired($data, ['titre', 'duree', 'annee_sortie', 'genre']);
            
            // Validation supplémentaire
            if (strlen($data['titre']) < 2 || strlen($data['titre']) > 255) {
                errorResponse('Le titre doit contenir entre 2 et 255 caractères');
            }
            
            if ($data['duree'] < 1 || $data['duree'] > 600) {
                errorResponse('La durée doit être comprise entre 1 et 600 minutes');
            }
            
            if ($data['annee_sortie'] < 1900 || $data['annee_sortie'] > date('Y') + 5) {
                errorResponse('L\'année de sortie doit être comprise entre 1900 et ' . (date('Y') + 5));
            }
            
            if (strlen($data['genre']) < 2 || strlen($data['genre']) > 100) {
                errorResponse('Le genre doit contenir entre 2 et 100 caractères');
            }
            
            // Vérifier si le film existe
            $film = $this->filmModel->getById($id);
            if (!$film) {
                errorResponse('Film non trouvé', 404);
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->filmModel->update($id, $data)) {
                successResponse(null, 'Film mis à jour avec succès');
            } else {
                errorResponse('Erreur lors de la mise à jour du film', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Supprime un film
     */
    public function delete($id) {
        try {
            // Vérifier si le film existe
            $film = $this->filmModel->getById($id);
            if (!$film) {
                errorResponse('Film non trouvé', 404);
            }
            
            if ($this->filmModel->delete($id)) {
                successResponse(null, 'Film supprimé avec succès');
            } else {
                errorResponse('Erreur lors de la suppression du film', 500);
            }
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère les genres disponibles
     */
    public function getGenres() {
        try {
            $genres = $this->filmModel->getGenres();
            successResponse($genres);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère les années disponibles
     */
    public function getAnnees() {
        try {
            $annees = $this->filmModel->getAnnees();
            successResponse($annees);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }
}
