<?php

/**
 * Classe utilitaire pour la sécurité
 */
class Security {
    
    /**
     * Génère un token CSRF
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifie un token CSRF
     */
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Nettoie les entrées utilisateur
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valide et nettoie les données d'un formulaire
     */
    public static function validateAndSanitize($data, $rules) {
        $validator = new Validator();
        return $validator->validateArray($data, $rules);
    }
    
    /**
     * Hache un mot de passe
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    /**
     * Vérifie un mot de passe
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Génère une chaîne aléatoire
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Limite les tentatives de connexion
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
        $key = "rate_limit_" . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
        }
        
        $data = $_SESSION[$key];
        
        // Réinitialiser si la fenêtre de temps est dépassée
        if (time() - $data['first_attempt'] > $timeWindow) {
            $_SESSION[$key] = ['attempts' => 1, 'first_attempt' => time()];
            return true;
        }
        
        // Vérifier si la limite est dépassée
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }
        
        // Incrémenter le compteur
        $_SESSION[$key]['attempts']++;
        return true;
    }
    
    /**
     * Obtient l'adresse IP réelle du client
     */
    public static function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Journalise les événements de sécurité
     */
    public static function logSecurityEvent($event, $details = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => self::getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'details' => $details
        ];
        
        $logMessage = json_encode($logEntry) . PHP_EOL;
        
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Détecte les tentatives d'injection SQL
     */
    public static function detectSQLInjection($input) {
        $patterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
            '/(--|\/\*|\*\/|;|\'|")/',
            '/(\bOR\b.*=\b.*\bOR\b)/i',
            '/(\bAND\b.*=\b.*\bAND\b)/i',
            '/(\bWHERE\b.*=\b.*\bOR\b)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Détecte les tentatives XSS
     */
    public static function detectXSS($input) {
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<img[^>]*src[^>]*javascript:/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Valide la sécurité des entrées
     */
    public static function validateInputSecurity($input) {
        if (is_array($input)) {
            foreach ($input as $value) {
                if (!self::validateInputSecurity($value)) {
                    return false;
                }
            }
            return true;
        }
        
        // Détecter les injections SQL
        if (self::detectSQLInjection($input)) {
            self::logSecurityEvent('SQL_INJECTION_ATTEMPT', ['input' => $input]);
            return false;
        }
        
        // Détecter les XSS
        if (self::detectXSS($input)) {
            self::logSecurityEvent('XSS_ATTEMPT', ['input' => $input]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Configure les headers de sécurité
     */
    public static function setSecurityHeaders() {
        // Protection contre le clickjacking
        header('X-Frame-Options: DENY');
        
        // Protection contre le MIME-type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Protection XSS
        header('X-XSS-Protection: 1; mode=block');
        
        // Politique de sécurité de contenu
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com; img-src \'self\' data: https:; font-src \'self\' https://cdnjs.cloudflare.com; connect-src \'self\';');
        
        // Référent policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
    
    /**
     * Vérifie si la requête est une requête AJAX
     */
    public static function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Vérifie la méthode HTTP
     */
    public static function validateHttpMethod($allowedMethods) {
        if (!is_array($allowedMethods)) {
            $allowedMethods = [$allowedMethods];
        }
        
        return in_array($_SERVER['REQUEST_METHOD'], $allowedMethods);
    }
    
    /**
     * Limite la taille des requêtes
     */
    public static function validateRequestSize($maxSize = 1048576) { // 1MB par défaut
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
        return $contentLength <= $maxSize;
    }
    
    /**
     * Nettoie les fichiers uploadés
     */
    public static function sanitizeUploadedFile($file) {
        // Vérifier si c'est bien un fichier uploadé
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Fichier non valide');
        }
        
        // Vérifier la taille
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            throw new Exception('Fichier trop volumineux');
        }
        
        // Vérifier le type MIME
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Type de fichier non autorisé');
        }
        
        // Générer un nom de fichier sécurisé
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = self::generateRandomString() . '.' . $extension;
        
        return [
            'tmp_name' => $file['tmp_name'],
            'name' => $newName,
            'size' => $file['size'],
            'type' => $mimeType
        ];
    }
}
