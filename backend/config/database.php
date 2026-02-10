<?php
/**
 * Configuration de la connexion à la base de données
 * My Cinema - Gestion de cinéma
 */

$dbHost = 'localhost';
$dbName = 'cinema';
$dbUser = 'root';
$dbPass = '';

try {
    $db = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", 
        $dbUser, 
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    return $db;
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}