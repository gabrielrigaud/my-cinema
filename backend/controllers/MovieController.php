<?php

require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Contrôleur pour les films
 */
class MovieController {
    private $movieModel;

    public function __construct() {
        $this->movieModel = new Movie();
    }

    /**
     * Récupère tous les films
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
            $genre = isset($_GET['genre']) ? sanitizeInput($_GET['genre']) : '';
            $year = isset($_GET['year']) ? sanitizeInput($_GET['year']) : '';

            $movies = $this->movieModel->getAll($page, ITEMS_PER_PAGE, $search, $genre, $year);
            $total = $this->movieModel->count($search, $genre, $year);
            $totalPages = ceil($total / ITEMS_PER_PAGE);

            successResponse([
                'movies' => $movies,
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
            $movie = $this->movieModel->getById($id);
            
            if (!$movie) {
                errorResponse('Movie not found', 404);
            }

            successResponse($movie);
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
            validateRequired($data, ['title', 'duration', 'release_year']);
            
            // Validation supplémentaire
            if (strlen($data['title']) < 2 || strlen($data['title']) > 255) {
                errorResponse('Title must be between 2 and 255 characters');
            }
            
            if ($data['duration'] < 1 || $data['duration'] > 600) {
                errorResponse('Duration must be between 1 and 600 minutes');
            }
            
            if ($data['release_year'] < 1900 || $data['release_year'] > date('Y') + 5) {
                errorResponse('Release year must be between 1900 and ' . (date('Y') + 5));
            }
            
            if (isset($data['genre']) && strlen($data['genre']) > 100) {
                errorResponse('Genre must be less than 100 characters');
            }
            
            if (isset($data['director']) && strlen($data['director']) > 255) {
                errorResponse('Director must be less than 255 characters');
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->movieModel->create($data)) {
                successResponse(null, 'Movie created successfully');
            } else {
                errorResponse('Error creating movie', 500);
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
            validateRequired($data, ['title', 'duration', 'release_year']);
            
            // Validation supplémentaire
            if (strlen($data['title']) < 2 || strlen($data['title']) > 255) {
                errorResponse('Title must be between 2 and 255 characters');
            }
            
            if ($data['duration'] < 1 || $data['duration'] > 600) {
                errorResponse('Duration must be between 1 and 600 minutes');
            }
            
            if ($data['release_year'] < 1900 || $data['release_year'] > date('Y') + 5) {
                errorResponse('Release year must be between 1900 and ' . (date('Y') + 5));
            }
            
            if (isset($data['genre']) && strlen($data['genre']) > 100) {
                errorResponse('Genre must be less than 100 characters');
            }
            
            if (isset($data['director']) && strlen($data['director']) > 255) {
                errorResponse('Director must be less than 255 characters');
            }
            
            // Vérifier si le film existe
            $movie = $this->movieModel->getById($id);
            if (!$movie) {
                errorResponse('Movie not found', 404);
            }
            
            // Nettoyage des données
            $data = sanitizeInput($data);
            
            if ($this->movieModel->update($id, $data)) {
                successResponse(null, 'Movie updated successfully');
            } else {
                errorResponse('Error updating movie', 500);
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
            $movie = $this->movieModel->getById($id);
            if (!$movie) {
                errorResponse('Movie not found', 404);
            }
            
            if ($this->movieModel->delete($id)) {
                successResponse(null, 'Movie deleted successfully');
            } else {
                errorResponse('Error deleting movie', 500);
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
            $genres = $this->movieModel->getGenres();
            successResponse($genres);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère les années disponibles
     */
    public function getYears() {
        try {
            $years = $this->movieModel->getYears();
            successResponse($years);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Récupère les réalisateurs disponibles
     */
    public function getDirectors() {
        try {
            $directors = $this->movieModel->getDirectors();
            successResponse($directors);
        } catch (Exception $e) {
            errorResponse($e->getMessage(), 500);
        }
    }
}
