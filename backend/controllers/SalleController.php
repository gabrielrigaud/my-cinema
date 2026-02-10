<?php
/**
 * Contrôleur pour la gestion des salles
 */

require_once __DIR__ . '/../repositories/SalleRepository.php';

class SalleController {
    private $salleRepository;
    private $db;

    public function __construct() {
        $this->db = require __DIR__ . '/../config/database.php';
        $this->salleRepository = new SalleRepository($this->db);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Affiche la liste des salles
     */
    public function liste() {
        $salles = $this->salleRepository->findAll();
        include __DIR__ . '/../views/salles/liste.php';
    }

    /**
     * Affiche le formulaire d'ajout d'une salle
     */
    public function ajouter() {
        include __DIR__ . '/../views/salles/ajouter.php';
    }

    /**
     * Traite l'ajout d'une salle
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $capacite = (int)($_POST['capacite'] ?? 0);
            $type = trim($_POST['type'] ?? 'Standard');

            // Validation
            $errors = [];
            
            if (empty($nom)) {
                $errors[] = "Le nom de la salle est obligatoire.";
            } elseif ($this->salleRepository->nameExists($nom)) {
                $errors[] = "Une salle avec ce nom existe déjà.";
            }
            
            if ($capacite <= 0 || $capacite > 1000) {
                $errors[] = "La capacité doit être entre 1 et 1000 places.";
            }
            
            $typesValides = ['Standard', '3D', 'IMAX', '4DX'];
            if (!in_array($type, $typesValides)) {
                $errors[] = "Type de salle invalide.";
            }

            if (empty($errors)) {
                if ($this->salleRepository->create($nom, $capacite, $type)) {
                    $_SESSION['success'] = "La salle '$nom' a été créée avec succès.";
                    header('Location: index.php?controller=salle&action=liste');
                    exit;
                } else {
                    $_SESSION['error'] = "Erreur lors de la création de la salle.";
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }
        
        header('Location: index.php?controller=salle&action=ajouter');
        exit;
    }

    /**
     * Affiche le formulaire de modification d'une salle
     */
    public function modifier() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID de salle invalide.";
            header('Location: index.php?controller=salle&action=liste');
            exit;
        }

        $salle = $this->salleRepository->findById($id);
        
        if (!$salle) {
            $_SESSION['error'] = "Salle introuvable.";
            header('Location: index.php?controller=salle&action=liste');
            exit;
        }

        include __DIR__ . '/../views/salles/modifier.php';
    }

    /**
     * Traite la modification d'une salle
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $capacite = (int)($_POST['capacite'] ?? 0);
            $type = trim($_POST['type'] ?? 'Standard');

            // Validation
            $errors = [];
            
            if ($id <= 0) {
                $errors[] = "ID invalide.";
            }
            
            if (empty($nom)) {
                $errors[] = "Le nom de la salle est obligatoire.";
            } elseif ($this->salleRepository->nameExists($nom, $id)) {
                $errors[] = "Une salle avec ce nom existe déjà.";
            }
            
            if ($capacite <= 0 || $capacite > 1000) {
                $errors[] = "La capacité doit être entre 1 et 1000 places.";
            }
            
            $typesValides = ['Standard', '3D', 'IMAX', '4DX'];
            if (!in_array($type, $typesValides)) {
                $errors[] = "Type de salle invalide.";
            }

            if (empty($errors)) {
                if ($this->salleRepository->update($id, $nom, $capacite, $type)) {
                    $_SESSION['success'] = "La salle a été modifiée avec succès.";
                    header('Location: index.php?controller=salle&action=liste');
                    exit;
                } else {
                    $_SESSION['error'] = "Erreur lors de la modification de la salle.";
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }

        header('Location: index.php?controller=salle&action=modifier&id=' . $id);
        exit;
    }

    /**
     * Supprime une salle (soft delete)
     */
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID de salle invalide.";
            header('Location: index.php?controller=salle&action=liste');
            exit;
        }

        if ($this->salleRepository->hasSeances($id)) {
            $_SESSION['error'] = "Impossible de supprimer cette salle car elle a des séances associées.";
        } else {
            $salle = $this->salleRepository->findById($id);
            if ($this->salleRepository->delete($id)) {
                $_SESSION['success'] = "La salle '{$salle['nom']}' a été supprimée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de la salle.";
            }
        }

        header('Location: index.php?controller=salle&action=liste');
        exit;
    }
}
