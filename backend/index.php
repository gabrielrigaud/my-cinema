<?php
/**
 * Point d'entrée unique de l'application (Front Controller)
 * Gère le routage des requêtes vers les contrôleurs appropriés
 */

// Démarrer la session
session_start();

// Récupérer le contrôleur et l'action depuis l'URL
$controller = $_GET['controller'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Charger le contrôleur approprié
try {
    switch ($controller) {
        case 'film':
            require_once __DIR__ . '/controllers/FilmController.php';
            $controllerInstance = new FilmController();
            break;
            
        case 'salle':
            require_once __DIR__ . '/controllers/SalleController.php';
            $controllerInstance = new SalleController();
            break;
            
        case 'seance':
            require_once __DIR__ . '/controllers/SeanceController.php';
            $controllerInstance = new SeanceController();
            break;
            
        case 'dashboard':
        default:
            require_once __DIR__ . '/controllers/DashboardController.php';
            $controllerInstance = new DashboardController();
            $action = 'index';
            break;
    }

    // Vérifier que la méthode existe
    if (method_exists($controllerInstance, $action)) {
        $controllerInstance->$action();
    } else {
        throw new Exception("Action '$action' introuvable dans le contrôleur '$controller'.");
    }
    
} catch (Exception $e) {
    // Gestion des erreurs
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header('Location: index.php?controller=dashboard');
    exit;
}
