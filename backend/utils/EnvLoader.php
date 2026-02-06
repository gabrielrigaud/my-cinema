<?php

/**
 * Classe pour charger les variables d'environnement depuis un fichier .env
 */
class EnvLoader {

    /**
     * Charge les variables d'environnement depuis le fichier .env
     */
    public static function load($path) {
        if (!file_exists($path)) {
            throw new Exception("Le fichier .env n'existe pas : $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorer les commentaires
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parser la ligne
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);

                $name = trim($name);
                $value = trim($value);

                // Retirer les guillemets
                $value = trim($value, '"\'');

                // Définir la variable d'environnement
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }
    }

    /**
     * Récupère une variable d'environnement
     */
    public static function get($key, $default = null) {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        // Convertir les booléens
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }

        // Convertir null
        if (strtolower($value) === 'null') {
            return null;
        }

        return $value;
    }

    /**
     * Vérifie si une variable d'environnement existe
     */
    public static function has($key) {
        return isset($_ENV[$key]) || getenv($key) !== false;
    }

    /**
     * Récupère toutes les variables d'environnement
     */
    public static function all() {
        return $_ENV;
    }
}
