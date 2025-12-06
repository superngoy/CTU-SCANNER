<?php
/**
 * Security Sanitization & Validation Functions
 * Prevents XSS, SQL Injection, and other web vulnerabilities
 */

/**
 * Escape HTML special characters for safe output
 * @param string $value - The value to escape
 * @param string $context - 'html', 'attr', or 'js' for different contexts
 * @return string - Escaped value safe for output
 */
function escapeOutput($value, $context = 'html') {
    if ($value === null) return '';
    
    switch ($context) {
        case 'attr':
            // Escape for HTML attributes
            return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        case 'js':
            // Escape for JavaScript context
            return json_encode($value, JSON_UNESCAPED_SLASHES);
        case 'html':
        default:
            // Escape for HTML content
            return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

/**
 * Sanitize input data (strip tags, trim whitespace)
 * @param string $value - The input value
 * @return string - Sanitized value
 */
function sanitizeInput($value) {
    if (is_array($value)) {
        return array_map('sanitizeInput', $value);
    }
    
    // Remove null bytes
    $value = str_replace("\0", "", $value);
    
    // Trim whitespace
    $value = trim($value);
    
    // Remove any script tags or dangerous HTML
    $value = strip_tags($value);
    
    return $value;
}

/**
 * Validate email address
 * @param string $email - Email to validate
 * @return bool - True if valid email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Philippine format and common patterns)
 * @param string $phone - Phone number to validate
 * @return bool - True if valid phone
 */
function validatePhoneNumber($phone) {
    // Allow digits, spaces, hyphens, parentheses, plus sign
    return preg_match('/^[\d\s\-\+\(\)]+$/', $phone) && strlen($phone) >= 7;
}

/**
 * Validate name (alphanumeric, spaces, apostrophes, hyphens only)
 * @param string $name - Name to validate
 * @return bool - True if valid name
 */
function validateName($name) {
    return preg_match("/^[a-zA-Z\s\-'\.]+$/", $name) && strlen($name) > 0 && strlen($name) <= 100;
}

/**
 * Validate ID format (alphanumeric)
 * @param string $id - ID to validate
 * @return bool - True if valid ID
 */
function validateID($id) {
    return preg_match('/^[a-zA-Z0-9\-]+$/', $id) && strlen($id) > 0 && strlen($id) <= 50;
}

/**
 * Validate date format (YYYY-MM-DD)
 * @param string $date - Date to validate
 * @return bool - True if valid date
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Sanitize filename (remove dangerous characters)
 * @param string $filename - Filename to sanitize
 * @return string - Safe filename
 */
function sanitizeFilename($filename) {
    // Remove path components
    $filename = basename($filename);
    
    // Replace dangerous characters with underscores
    $filename = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $filename);
    
    // Remove multiple consecutive underscores
    $filename = preg_replace('/_+/', '_', $filename);
    
    // Remove leading/trailing dots and dashes
    $filename = trim($filename, '._-');
    
    return $filename;
}

/**
 * Get sanitized GET parameter
 * @param string $key - Parameter key
 * @param string $default - Default value if not set
 * @param string $type - 'string', 'int', 'date', 'email'
 * @return mixed - Sanitized parameter value
 */
function getSafeGET($key, $default = '', $type = 'string') {
    if (!isset($_GET[$key])) {
        return $default;
    }
    
    $value = $_GET[$key];
    
    switch ($type) {
        case 'int':
            return (int)$value;
        case 'date':
            return validateDate($value) ? $value : $default;
        case 'email':
            return validateEmail($value) ? sanitizeInput($value) : $default;
        case 'string':
        default:
            return sanitizeInput($value);
    }
}

/**
 * Get sanitized POST parameter
 * @param string $key - Parameter key
 * @param string $default - Default value if not set
 * @param string $type - 'string', 'int', 'date', 'email'
 * @return mixed - Sanitized parameter value
 */
function getSafePOST($key, $default = '', $type = 'string') {
    if (!isset($_POST[$key])) {
        return $default;
    }
    
    $value = $_POST[$key];
    
    switch ($type) {
        case 'int':
            return (int)$value;
        case 'date':
            return validateDate($value) ? $value : $default;
        case 'email':
            return validateEmail($value) ? sanitizeInput($value) : $default;
        case 'string':
        default:
            return sanitizeInput($value);
    }
}

/**
 * Safely encode data for JSON output
 * Prevents XSS in JSON responses
 * @param mixed $data - Data to encode
 * @return string - JSON encoded string
 */
function safeJSONEncode($data) {
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
}

/**
 * Create a safe error message for display
 * @param string $message - Error message
 * @return string - Escaped error message
 */
function safeErrorMessage($message) {
    return escapeOutput($message, 'html');
}

/**
 * Validate user input for database insertion
 * @param array $data - Input data to validate
 * @param array $rules - Validation rules
 * @return array - ['valid' => bool, 'errors' => array]
 */
function validateFormData($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $validation) {
        $value = $data[$field] ?? '';
        
        // Check required
        if ($validation['required'] && empty($value)) {
            $errors[$field] = $validation['error_required'] ?? "$field is required";
            continue;
        }
        
        if (empty($value)) continue; // Skip optional empty fields
        
        // Check type-specific validation
        switch ($validation['type'] ?? 'string') {
            case 'email':
                if (!validateEmail($value)) {
                    $errors[$field] = $validation['error'] ?? "Invalid email format";
                }
                break;
            case 'phone':
                if (!validatePhoneNumber($value)) {
                    $errors[$field] = $validation['error'] ?? "Invalid phone number";
                }
                break;
            case 'name':
                if (!validateName($value)) {
                    $errors[$field] = $validation['error'] ?? "Invalid name format";
                }
                break;
            case 'id':
                if (!validateID($value)) {
                    $errors[$field] = $validation['error'] ?? "Invalid ID format";
                }
                break;
            case 'date':
                if (!validateDate($value)) {
                    $errors[$field] = $validation['error'] ?? "Invalid date format (YYYY-MM-DD)";
                }
                break;
        }
        
        // Check length constraints
        if (isset($validation['max_length']) && strlen($value) > $validation['max_length']) {
            $errors[$field] = $validation['error_max'] ?? "Maximum length is {$validation['max_length']} characters";
        }
        
        if (isset($validation['min_length']) && strlen($value) < $validation['min_length']) {
            $errors[$field] = $validation['error_min'] ?? "Minimum length is {$validation['min_length']} characters";
        }
    }
    
    return [
        'valid' => count($errors) === 0,
        'errors' => $errors
    ];
}

/**
 * Log security events for audit trail
 * @param string $event - Event type
 * @param string $message - Event message
 * @param array $details - Additional details
 * @return bool - Success
 */
function logSecurityEvent($event, $message, $details = []) {
    $timestamp = date('Y-m-d H:i:s');
    $user = $_SESSION['admin_id'] ?? $_SESSION['security_id'] ?? $_SESSION['scanner_user_id'] ?? 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $logEntry = [
        'timestamp' => $timestamp,
        'event' => $event,
        'message' => $message,
        'user' => $user,
        'ip' => $ip,
        'details' => $details
    ];
    
    // Log to file (optional - uncomment to enable)
    // error_log(json_encode($logEntry));
    
    return true;
}
?>
