<?php

/**
 * Classe de validation des données
 */
class Validator {
    
    /**
     * Valide un email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Valide un entier
     */
    public static function validateInt($value, $min = null, $max = null) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $int = (int)$value;
        
        if ($min !== null && $int < $min) {
            return false;
        }
        
        if ($max !== null && $int > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Valide un nombre décimal
     */
    public static function validateFloat($value, $min = null, $max = null) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $float = (float)$value;
        
        if ($min !== null && $float < $min) {
            return false;
        }
        
        if ($max !== null && $float > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Valide la longueur d'une chaîne
     */
    public static function validateLength($value, $min = null, $max = null) {
        $length = strlen($value);
        
        if ($min !== null && $length < $min) {
            return false;
        }
        
        if ($max !== null && $length > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Valide une date
     */
    public static function validateDate($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Valide une date/heure pour les séances
     */
    public static function validateDateTime($dateTime) {
        return self::validateDate($dateTime, 'Y-m-d\TH:i');
    }
    
    /**
     * Valide qu'une date est dans le futur
     */
    public static function validateFutureDate($date) {
        $dateObj = new DateTime($date);
        $now = new DateTime();
        return $dateObj > $now;
    }
    
    /**
     * Valide un nom (lettres, espaces, tirets)
     */
    public static function validateName($name) {
        return preg_match('/^[a-zA-ZàâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\s\-\'\.]+$/', $name);
    }
    
    /**
     * Valide un titre de film
     */
    public static function validateFilmTitle($title) {
        return self::validateLength($title, 2, 255);
    }
    
    /**
     * Valide un genre de film
     */
    public static function validateGenre($genre) {
        return self::validateLength($genre, 2, 100) && preg_match('/^[a-zA-ZàâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\s\-]+$/', $genre);
    }
    
    /**
     * Valide une description
     */
    public static function validateDescription($description) {
        return self::validateLength($description, 0, 2000);
    }
    
    /**
     * Valide une durée en minutes
     */
    public static function validateDuration($duration) {
        return self::validateInt($duration, 1, 600);
    }
    
    /**
     * Valide une année de sortie
     */
    public static function validateYear($year) {
        return self::validateInt($year, 1900, date('Y') + 5);
    }
    
    /**
     * Valide une URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Valide le nom d'une salle
     */
    public static function validateSalleName($name) {
        return self::validateLength($name, 2, 100) && preg_match('/^[a-zA-Z0-9àâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\s\-]+$/', $name);
    }
    
    /**
     * Valide la capacité d'une salle
     */
    public static function validateCapacity($capacity) {
        return self::validateInt($capacity, 1, 1000);
    }
    
    /**
     * Valide le type de salle
     */
    public static function validateSalleType($type) {
        $allowedTypes = ['Standard', '3D', 'IMAX', 'VIP'];
        return in_array($type, $allowedTypes);
    }
    
    /**
     * Valide un prix
     */
    public static function validatePrice($price) {
        return self::validateFloat($price, 0, 100);
    }
    
    /**
     * Nettoie et valide une chaîne de caractères
     */
    public static function sanitizeString($value) {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valide un tableau de données selon des règles
     */
    public static function validateArray($data, $rules) {
        $errors = [];
        $sanitized = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            // Vérifier si le champ est requis
            if (in_array('required', $fieldRules) && ($value === null || $value === '')) {
                $errors[$field] = "Le champ '$field' est requis";
                continue;
            }
            
            // Si le champ n'est pas requis et vide, on passe au suivant
            if (!in_array('required', $fieldRules) && ($value === null || $value === '')) {
                continue;
            }
            
            // Appliquer les règles de validation
            foreach ($fieldRules as $rule) {
                if (is_string($rule)) {
                    switch ($rule) {
                        case 'email':
                            if (!self::validateEmail($value)) {
                                $errors[$field] = "L'email n'est pas valide";
                            }
                            break;
                            
                        case 'int':
                            if (!self::validateInt($value)) {
                                $errors[$field] = "La valeur doit être un entier";
                            }
                            break;
                            
                        case 'float':
                            if (!self::validateFloat($value)) {
                                $errors[$field] = "La valeur doit être un nombre";
                            }
                            break;
                            
                        case 'film_title':
                            if (!self::validateFilmTitle($value)) {
                                $errors[$field] = "Le titre doit contenir entre 2 et 255 caractères";
                            }
                            break;
                            
                        case 'genre':
                            if (!self::validateGenre($value)) {
                                $errors[$field] = "Le genre n'est pas valide";
                            }
                            break;
                            
                        case 'description':
                            if (!self::validateDescription($value)) {
                                $errors[$field] = "La description est trop longue (max 2000 caractères)";
                            }
                            break;
                            
                        case 'duration':
                            if (!self::validateDuration($value)) {
                                $errors[$field] = "La durée doit être comprise entre 1 et 600 minutes";
                            }
                            break;
                            
                        case 'year':
                            if (!self::validateYear($value)) {
                                $errors[$field] = "L'année doit être comprise entre 1900 et " . (date('Y') + 5);
                            }
                            break;
                            
                        case 'url':
                            if (!self::validateUrl($value)) {
                                $errors[$field] = "L'URL n'est pas valide";
                            }
                            break;
                            
                        case 'salle_name':
                            if (!self::validateSalleName($value)) {
                                $errors[$field] = "Le nom de la salle n'est pas valide";
                            }
                            break;
                            
                        case 'capacity':
                            if (!self::validateCapacity($value)) {
                                $errors[$field] = "La capacité doit être comprise entre 1 et 1000";
                            }
                            break;
                            
                        case 'salle_type':
                            if (!self::validateSalleType($value)) {
                                $errors[$field] = "Le type de salle n'est pas valide";
                            }
                            break;
                            
                        case 'price':
                            if (!self::validatePrice($value)) {
                                $errors[$field] = "Le prix doit être compris entre 0 et 100 €";
                            }
                            break;
                            
                        case 'datetime':
                            if (!self::validateDateTime($value)) {
                                $errors[$field] = "La date/heure n'est pas valide";
                            }
                            break;
                            
                        case 'future_date':
                            if (!self::validateFutureDate($value)) {
                                $errors[$field] = "La date doit être dans le futur";
                            }
                            break;
                    }
                } elseif (is_array($rule)) {
                    // Règles avec paramètres (ex: ['length', 2, 255])
                    $ruleName = $rule[0];
                    
                    switch ($ruleName) {
                        case 'length':
                            if (!self::validateLength($value, $rule[1] ?? null, $rule[2] ?? null)) {
                                $min = $rule[1] ?? 0;
                                $max = $rule[2] ?? '∞';
                                $errors[$field] = "La longueur doit être entre $min et $max caractères";
                            }
                            break;
                            
                        case 'int_range':
                            if (!self::validateInt($value, $rule[1] ?? null, $rule[2] ?? null)) {
                                $min = $rule[1] ?? '-∞';
                                $max = $rule[2] ?? '∞';
                                $errors[$field] = "La valeur doit être un entier entre $min et $max";
                            }
                            break;
                            
                        case 'float_range':
                            if (!self::validateFloat($value, $rule[1] ?? null, $rule[2] ?? null)) {
                                $min = $rule[1] ?? '-∞';
                                $max = $rule[2] ?? '∞';
                                $errors[$field] = "La valeur doit être un nombre entre $min et $max";
                            }
                            break;
                    }
                }
            }
            
            // Nettoyer la valeur si aucune erreur
            if (!isset($errors[$field])) {
                $sanitized[$field] = is_string($value) ? self::sanitizeString($value) : $value;
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'sanitized' => $sanitized
        ];
    }
}
