<?php
/**
 * Router pour le serveur de développement PHP builtin
 * Usage: php -S localhost:8000 -t backend backend/router.php
 */

$requestUri = $_SERVER['REQUEST_URI'];
$uri = parse_url($requestUri, PHP_URL_PATH);

// Vérifier si c'est une requête pour un fichier statique du frontend
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/', $uri)) {
    // Construire le chemin vers le fichier frontend
    $frontendPath = __DIR__ . '/../frontend' . $uri;

    if (file_exists($frontendPath) && is_file($frontendPath)) {
        // Déterminer le type MIME
        $extension = pathinfo($frontendPath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        header("Content-Type: $mimeType");
        readfile($frontendPath);
        return true;
    }

    // Si le fichier n'existe pas, retourner 404
    http_response_code(404);
    echo "File not found: $uri";
    return false;
}

// Pour toutes les autres requêtes, passer à index.php
require __DIR__ . '/index.php';
