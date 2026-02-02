<?php

/**
 * Configuration de la base de données
 */

class Database {
    private $host = 'localhost';
    private $dbname = 'cinema';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $pdo;

    public function __construct() {
        $this->connect();
    }

    /**
     * Connexion à la base de données avec PDO
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new Exception("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    /**
     * Retourne l'instance PDO
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Prépare et exécute une requête
     */
    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }

    /**
     * Exécute une requête SQL
     */
    public function query($sql) {
        return $this->pdo->query($sql);
    }

    /**
     * Démarre une transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Valide une transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Annule une transaction
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }

    /**
     * Retourne le dernier ID inséré
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}
