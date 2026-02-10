<?php
/**
 * Contrôleur pour la gestion des films
 */

require_once __DIR__ . '/../repositories/FilmRepository.php';

class FilmController {
    private $filmRepository;
    private $db;

    public function __construct() {
        $this->db = require __DIR__ . '/../config/database.php';
        $this->filmRepository = new FilmRepository($this->db);
        
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Affiche la liste des films
     */
    public function liste() {
        // Récupérer les filtres
        $filters = [
            'search' => $_GET['search'] ?? '',
            'genre' => $_GET['genre'] ?? '',
            'annee' => $_GET['annee'] ?? ''
        ];

        $films = $this->filmRepository->findAll($filters);
        
        // Inclure header, contenu et footer
        $pageTitle = 'Liste des films - My Cinema';
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/films/liste.php';
        include __DIR__ . '/../includes/footer.php';
    }

    /**
     * Affiche le formulaire d'ajout d'un film
     */
    public function ajouter() {
        $pageTitle = 'Ajouter un film - My Cinema';
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/films/ajouter.php';
        include __DIR__ . '/../includes/footer.php';
    }

    /**
     * Traite l'ajout d'un film
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation des données
            $titre = trim($_POST['titre'] ?? '');
            $duree = (int)($_POST['duree'] ?? 0);
            $annee = (int)($_POST['annee'] ?? 0);
            $genre = trim($_POST['genre'] ?? '');

            // Validation
            $errors = [];
            
            if (empty($titre)) {
                $errors[] = "Le titre est obligatoire.";
            }
            
            if ($duree <= 0 || $duree > 500) {
                $errors[] = "La durée doit être entre 1 et 500 minutes.";
            }
            
            if ($annee < 1888 || $annee > (date('Y') + 2)) {
                $errors[] = "L'année est invalide.";
            }
            
            if (empty($genre)) {
                $errors[] = "Le genre est obligatoire.";
            }

            if (empty($errors)) {
                if ($this->filmRepository->create($titre, $duree, $annee, $genre)) {
                    $_SESSION['success'] = "Le film '$titre' a été ajouté avec succès.";
                    header('Location: index.php?controller=film&action=liste');
                    exit;
                } else {
                    $_SESSION['error'] = "Erreur lors de l'ajout du film.";
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }
        
        // Redirection en cas d'erreur
        header('Location: index.php?controller=film&action=ajouter');
        exit;
    }

    /**
     * Affiche le formulaire de modification d'un film
     */
    public function modifier() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID de film invalide.";
            header('Location: index.php?controller=film&action=liste');
            exit;
        }

        $film = $this->filmRepository->findById($id);
        
        if (!$film) {
            $_SESSION['error'] = "Film introuvable.";
            header('Location: index.php?controller=film&action=liste');
            exit;
        }

        $pageTitle = 'Modifier un film - My Cinema';
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/films/modifier.php';
        include __DIR__ . '/../includes/footer.php';
    }

    /**
     * Traite la modification d'un film
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $titre = trim($_POST['titre'] ?? '');
            $duree = (int)($_POST['duree'] ?? 0);
            $annee = (int)($_POST['annee'] ?? 0);
            $genre = trim($_POST['genre'] ?? '');

            // Validation
            $errors = [];
            
            if ($id <= 0) {
                $errors[] = "ID invalide.";
            }
            
            if (empty($titre)) {
                $errors[] = "Le titre est obligatoire.";
            }
            
            if ($duree <= 0 || $duree > 500) {
                $errors[] = "La durée doit être entre 1 et 500 minutes.";
            }
            
            if ($annee < 1888 || $annee > (date('Y') + 2)) {
                $errors[] = "L'année est invalide.";
            }
            
            if (empty($genre)) {
                $errors[] = "Le genre est obligatoire.";
            }

            if (empty($errors)) {
                if ($this->filmRepository->update($id, $titre, $duree, $annee, $genre)) {
                    $_SESSION['success'] = "Le film a été modifié avec succès.";
                    header('Location: index.php?controller=film&action=liste');
                    exit;
                } else {
                    $_SESSION['error'] = "Erreur lors de la modification du film.";
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }

        header('Location: index.php?controller=film&action=modifier&id=' . $id);
        exit;
    }

    /**
     * Supprime un film (soft delete)
     */
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID de film invalide.";
            header('Location: index.php?controller=film&action=liste');
            exit;
        }

        // Vérifier si le film a des séances associées
        if ($this->filmRepository->hasSeances($id)) {
            $_SESSION['error'] = "Impossible de supprimer ce film car il a des séances associées.";
        } else {
            $film = $this->filmRepository->findById($id);
            if ($this->filmRepository->delete($id)) {
                $_SESSION['success'] = "Le film '{$film['titre']}' a été supprimé avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression du film.";
            }
        }

        header('Location: index.php?controller=film&action=liste');
        exit;
    }
}
