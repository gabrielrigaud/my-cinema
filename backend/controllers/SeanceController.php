<?php
/**
 * Contrôleur pour la gestion des séances
 */

require_once __DIR__ . '/../repositories/SeanceRepository.php';
require_once __DIR__ . '/../repositories/FilmRepository.php';
require_once __DIR__ . '/../repositories/SalleRepository.php';

class SeanceController {
    private $seanceRepository;
    private $filmRepository;
    private $salleRepository;
    private $db;

    public function __construct() {
        $this->db = require __DIR__ . '/../config/database.php';
        $this->seanceRepository = new SeanceRepository($this->db);
        $this->filmRepository = new FilmRepository($this->db);
        $this->salleRepository = new SalleRepository($this->db);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Affiche la liste des séances
     */
    public function liste() {
        $filters = [
            'date' => $_GET['date'] ?? '',
            'salle_id' => $_GET['salle_id'] ?? '',
            'film_id' => $_GET['film_id'] ?? ''
        ];

        $seances = $this->seanceRepository->findAll($filters);
        $films = $this->filmRepository->findAll();
        $salles = $this->salleRepository->findAll();
        
        include __DIR__ . '/../views/seances/liste.php';
    }

    /**
     * Affiche le formulaire d'ajout d'une séance
     */
    public function ajouter() {
        $films = $this->filmRepository->findAll();
        $salles = $this->salleRepository->findAll();
        include __DIR__ . '/../views/seances/ajouter.php';
    }

    /**
     * Traite l'ajout d'une séance
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filmId = (int)($_POST['film_id'] ?? 0);
            $salleId = (int)($_POST['salle_id'] ?? 0);
            $date = trim($_POST['date'] ?? '');
            $heure = trim($_POST['heure'] ?? '');

            // Validation
            $errors = [];
            
            if ($filmId <= 0) {
                $errors[] = "Veuillez sélectionner un film.";
            } else {
                $film = $this->filmRepository->findById($filmId);
                if (!$film) {
                    $errors[] = "Film invalide.";
                }
            }
            
            if ($salleId <= 0) {
                $errors[] = "Veuillez sélectionner une salle.";
            } else {
                $salle = $this->salleRepository->findById($salleId);
                if (!$salle) {
                    $errors[] = "Salle invalide.";
                }
            }
            
            if (empty($date)) {
                $errors[] = "La date est obligatoire.";
            } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
                $errors[] = "La date ne peut pas être dans le passé.";
            }
            
            if (empty($heure)) {
                $errors[] = "L'heure est obligatoire.";
            }

            // Vérifier les chevauchements si pas d'erreurs de validation
            if (empty($errors) && isset($film)) {
                if ($this->seanceRepository->checkOverlap($salleId, $date, $heure, $film['duree'])) {
                    $errors[] = "Conflit d'horaire : une autre séance est programmée dans cette salle à ce moment.";
                }
            }

            if (empty($errors)) {
                if ($this->seanceRepository->create($filmId, $salleId, $date, $heure)) {
                    $_SESSION['success'] = "La séance a été créée avec succès.";
                    header('Location: index.php?controller=seance&action=liste');
                    exit;
                } else {
                    $_SESSION['error'] = "Erreur lors de la création de la séance.";
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }
        
        header('Location: index.php?controller=seance&action=ajouter');
        exit;
    }

    /**
     * Affiche le formulaire de modification d'une séance
     */
    public function modifier() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID de séance invalide.";
            header('Location: index.php?controller=seance&action=liste');
            exit;
        }

        $seance = $this->seanceRepository->findById($id);
        
        if (!$seance) {
            $_SESSION['error'] = "Séance introuvable.";
            header('Location: index.php?controller=seance&action=liste');
            exit;
        }

        $films = $this->filmRepository->findAll();
        $salles = $this->salleRepository->findAll();
        
        include __DIR__ . '/../views/seances/modifier.php';
    }

    /**
     * Traite la modification d'une séance
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $filmId = (int)($_POST['film_id'] ?? 0);
            $salleId = (int)($_POST['salle_id'] ?? 0);
            $date = trim($_POST['date'] ?? '');
            $heure = trim($_POST['heure'] ?? '');

            // Validation similaire à create()
            $errors = [];
            
            if ($id <= 0) {
                $errors[] = "ID invalide.";
            }
            
            if ($filmId <= 0) {
                $errors[] = "Veuillez sélectionner un film.";
            } else {
                $film = $this->filmRepository->findById($filmId);
                if (!$film) {
                    $errors[] = "Film invalide.";
                }
            }
            
            if ($salleId <= 0) {
                $errors[] = "Veuillez sélectionner une salle.";
            }
            
            if (empty($date)) {
                $errors[] = "La date est obligatoire.";
            }
            
            if (empty($heure)) {
                $errors[] = "L'heure est obligatoire.";
            }

            // Vérifier les chevauchements (en excluant la séance en cours de modification)
            if (empty($errors) && isset($film)) {
                if ($this->seanceRepository->checkOverlap($salleId, $date, $heure, $film['duree'], $id)) {
                    $errors[] = "Conflit d'horaire : une autre séance est programmée dans cette salle à ce moment.";
                }
            }

            if (empty($errors)) {
                if ($this->seanceRepository->update($id, $filmId, $salleId, $date, $heure)) {
                    $_SESSION['success'] = "La séance a été modifiée avec succès.";
                    header('Location: index.php?controller=seance&action=liste');
                    exit;
                } else {
                    $_SESSION['error'] = "Erreur lors de la modification de la séance.";
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }

        header('Location: index.php?controller=seance&action=modifier&id=' . $id);
        exit;
    }

    /**
     * Supprime une séance
     */
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID de séance invalide.";
            header('Location: index.php?controller=seance&action=liste');
            exit;
        }

        if ($this->seanceRepository->delete($id)) {
            $_SESSION['success'] = "La séance a été supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de la séance.";
        }

        header('Location: index.php?controller=seance&action=liste');
        exit;
    }
}
