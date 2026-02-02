<?php

require_once __DIR__ . '/config/config.php';

/**
 * Routeur principal de l'application
 */

// Activer le reporting d'erreurs en mode développement
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Parser l'URL
$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Nettoyer l'URL pour enlever les paramètres GET
$uri = parse_url($requestUri, PHP_URL_PATH);
$uri = str_replace('/backend', '', $uri); // Retirer le préfixe /backend

// Router les requêtes
switch ($uri) {
    // Routes API Movies
    case '/api/movies':
        require_once __DIR__ . '/controllers/MovieController.php';
        $controller = new MovieController();
        
        switch ($method) {
            case 'GET':
                $controller->index();
                break;
            case 'POST':
                $controller->create();
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
        break;
        
    case '/api/movies/genres':
        require_once __DIR__ . '/controllers/MovieController.php';
        $controller = new MovieController();
        
        if ($method === 'GET') {
            $controller->getGenres();
        } else {
            errorResponse('Method not allowed', 405);
        }
        break;
        
    case '/api/movies/years':
        require_once __DIR__ . '/controllers/MovieController.php';
        $controller = new MovieController();
        
        if ($method === 'GET') {
            $controller->getYears();
        } else {
            errorResponse('Method not allowed', 405);
        }
        break;
        
    case '/api/movies/directors':
        require_once __DIR__ . '/controllers/MovieController.php';
        $controller = new MovieController();
        
        if ($method === 'GET') {
            $controller->getDirectors();
        } else {
            errorResponse('Method not allowed', 405);
        }
        break;
        
    case preg_match('/^\/api\/movies\/(\d+)$/', $uri, $matches) ? true : false:
        require_once __DIR__ . '/controllers/MovieController.php';
        $controller = new MovieController();
        $id = $matches[1];
        
        switch ($method) {
            case 'GET':
                $controller->show($id);
                break;
            case 'PUT':
                $controller->update($id);
                break;
            case 'DELETE':
                $controller->delete($id);
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
        break;
        
    // Routes API Rooms
    case '/api/rooms':
        require_once __DIR__ . '/controllers/RoomController.php';
        $controller = new RoomController();
        
        switch ($method) {
            case 'GET':
                $controller->index();
                break;
            case 'POST':
                $controller->create();
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
        break;
        
    case '/api/rooms/select':
        require_once __DIR__ . '/controllers/RoomController.php';
        $controller = new RoomController();
        
        if ($method === 'GET') {
            $controller->getAllForSelect();
        } else {
            errorResponse('Method not allowed', 405);
        }
        break;
        
    case '/api/rooms/types':
        require_once __DIR__ . '/controllers/RoomController.php';
        $controller = new RoomController();
        
        if ($method === 'GET') {
            $controller->getTypes();
        } else {
            errorResponse('Method not allowed', 405);
        }
        break;
        
    case '/api/rooms/check-availability':
        require_once __DIR__ . '/controllers/RoomController.php';
        $controller = new RoomController();
        
        if ($method === 'POST') {
            $controller->checkAvailability();
        } else {
            errorResponse('Method not allowed', 405);
        }
        break;
        
    case preg_match('/^\/api\/rooms\/(\d+)$/', $uri, $matches) ? true : false:
        require_once __DIR__ . '/controllers/RoomController.php';
        $controller = new RoomController();
        $id = $matches[1];
        
        switch ($method) {
            case 'GET':
                $controller->show($id);
                break;
            case 'PUT':
                $controller->update($id);
                break;
            case 'DELETE':
                $controller->delete($id);
                break;
            case 'PATCH':
                $controller->toggleActive($id);
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
        break;
        
    // Routes API Screenings
    case '/api/screenings':
        require_once __DIR__ . '/controllers/ScreeningController.php';
        $controller = new ScreeningController();
        
        switch ($method) {
            case 'GET':
                $controller->index();
                break;
            case 'POST':
                $controller->create();
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
        break;
        
    case '/api/screenings/planning':
        require_once __DIR__ . '/controllers/ScreeningController.php';
        $controller = new ScreeningController();
        
        if ($method === 'GET') {
            $controller->getPlanning();
        } else {
            errorResponse('Method not allowed', 405);
        }
        break;
        
    case '/api/screenings/upcoming':
        require_once __DIR__ . '/controllers/ScreeningController.php';
        $controller = new ScreeningController();
        
        if ($method === 'GET') {
            $controller->getUpcoming();
        } else {
            errorResponse('Method not allowed', 405);
        }
        break;
        
    case '/api/screenings/recent-past':
        require_once __DIR__ . '/controllers/ScreeningController.php';
        $controller = new ScreeningController();
        
        if ($method === 'GET') {
            $controller->getRecentPast();
        } else {
            errorResponse('Method not allowed', 405);
        }
        break;
        
    case preg_match('/^\/api\/screenings\/date\/([^\/]+)$/', $uri, $matches) ? true : false:
        require_once __DIR__ . '/controllers/ScreeningController.php';
        $controller = new ScreeningController();
        $date = urldecode($matches[1]);
        
        if ($method === 'GET') {
            $controller->getByDate($date);
        } else {
            errorResponse('Method not allowed', 405);
        }
        break;
        
    case preg_match('/^\/api\/screenings\/(\d+)$/', $uri, $matches) ? true : false:
        require_once __DIR__ . '/controllers/ScreeningController.php';
        $controller = new ScreeningController();
        $id = $matches[1];
        
        switch ($method) {
            case 'GET':
                $controller->show($id);
                break;
            case 'PUT':
                $controller->update($id);
                break;
            case 'DELETE':
                $controller->delete($id);
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
        break;
        
    // Route par défaut - Page d'accueil du frontend
    default:
        // Servir le frontend
        $frontendPath = __DIR__ . '/../frontend';
        $requestFile = $frontendPath . $uri;
        
        // Si c'est la racine, servir index.html
        if ($uri === '/' || $uri === '') {
            $requestFile = $frontendPath . '/index.html';
        }
        
        // Si le fichier existe, le servir
        if (file_exists($requestFile) && is_file($requestFile)) {
            $extension = pathinfo($requestFile, PATHINFO_EXTENSION);
            
            switch ($extension) {
                case 'css':
                    header('Content-Type: text/css');
                    break;
                case 'js':
                    header('Content-Type: application/javascript');
                    break;
                case 'png':
                    header('Content-Type: image/png');
                    break;
                case 'jpg':
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'svg':
                    header('Content-Type: image/svg+xml');
                    break;
                case 'html':
                default:
                    header('Content-Type: text/html; charset=utf-8');
                    break;
            }
            
            readfile($requestFile);
        } else {
            // Pour les routes SPA, toujours servir index.html
            if (strpos($uri, '/api/') !== 0) {
                header('Content-Type: text/html; charset=utf-8');
                readfile($frontendPath . '/index.html');
            } else {
                errorResponse('Endpoint non trouvé', 404);
            }
        }
        break;
}
