<?php

// Charger les variables d'environnement
require_once __DIR__ . '/../utils/EnvLoader.php';

try {
    EnvLoader::load(__DIR__ . '/../.env');
} catch (Exception $e) {
    die('Erreur de chargement du fichier .env : ' . $e->getMessage());
}

// Configuration de l'application
define('APP_NAME', EnvLoader::get('APP_NAME', 'Cinema Management System'));
define('APP_VERSION', EnvLoader::get('APP_VERSION', '1.0.0'));
define('APP_DEBUG', EnvLoader::get('APP_DEBUG', false));
define('APP_ENV', EnvLoader::get('APP_ENV', 'production'));

define('BASE_URL', EnvLoader::get('BASE_URL', 'http://localhost:8000'));
define('FRONTEND_URL', EnvLoader::get('FRONTEND_URL', BASE_URL));
define('API_BASE_URL', BASE_URL . '/backend/api');

// Configuration de la pagination
define('ITEMS_PER_PAGE', (int)EnvLoader::get('ITEMS_PER_PAGE', 10));

// Configuration des headers CORS
$allowedOrigins = EnvLoader::get('CORS_ALLOWED_ORIGINS', '*');
$allowedMethods = EnvLoader::get('CORS_ALLOWED_METHODS', 'GET, POST, PUT, DELETE, OPTIONS');
$allowedHeaders = EnvLoader::get('CORS_ALLOWED_HEADERS', 'Content-Type, Authorization');

header("Access-Control-Allow-Origin: $allowedOrigins");
header("Access-Control-Allow-Methods: $allowedMethods");
header("Access-Control-Allow-Headers: $allowedHeaders");

// Gestion des requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Configuration du fuseau horaire
date_default_timezone_set(EnvLoader::get('TIMEZONE', 'Europe/Paris'));

// Headers de sécurité
require_once __DIR__ . '/../utils/Security.php';
Security::setSecurityHeaders();

// Configuration de l'affichage des erreurs
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Fonctions utilitaires globales
/**
 * Retourne une réponse JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Retourne une réponse d'erreur
 */
function errorResponse($message, $statusCode = 400) {
    jsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

/**
 * Retourne une réponse de succès
 */
function successResponse($data = null, $message = 'Opération réussie') {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * Récupère les données JSON de la requête
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}

/**
 * Valide les données requises
 */
function validateRequired($data, $requiredFields) {
    $missing = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        errorResponse('Champs requis manquants: ' . implode(', ', $missing));
    }
}

/**
 * Nettoie les entrées utilisateur
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Logger simple
 */
function logMessage($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
