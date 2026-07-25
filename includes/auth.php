<?php
// includes/auth.php
// Gestion des sessions et authentification admin

if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Vérifier si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Vérifier si l'utilisateur est connecté, sinon rediriger vers login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

function isPasswordChangeRequired() {
    return !empty($_SESSION['must_change_password']);
}

function setPasswordChangeRequired($required = true) {
    if ($required) {
        $_SESSION['must_change_password'] = true;
    } else {
        unset($_SESSION['must_change_password']);
    }
}

function requirePasswordChange() {
    requireLogin();

    if (isPasswordChangeRequired()) {
        header('Location: ' . BASE_URL . 'admin/change-password.php');
        exit;
    }
}

/**
 * Vérifie que l'utilisateur a le rôle requis (ou est admin).
 * admin a tous les droits. editor a un accès limité.
 */
function requireRole(string $role): void {
    requireLogin();
    $currentRole = $_SESSION['admin_role'] ?? 'admin';
    $allowed = ($role === 'admin') ? ['admin'] : ['admin', $role];
    if (!in_array($currentRole, $allowed, true)) {
        http_response_code(403);
        echo 'Accès interdit.';
        exit;
    }
}

/**
 * Vérifie si l'utilisateur connecté a un rôle donné.
 */
function hasRole(string $role): bool {
    $currentRole = $_SESSION['admin_role'] ?? 'admin';
    return $currentRole === $role || $currentRole === 'admin';
}

/**
 * Connexion utilisateur
 */
function login($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, fullname, must_change_password, role FROM admins WHERE username = ? AND active = 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_fullname'] = $admin['fullname'];
            $_SESSION['admin_role'] = $admin['role'];
            setPasswordChangeRequired(!empty($admin['must_change_password']));
            clearFailedLogins();
            return true;
        }

        recordFailedLogin();
        setPasswordChangeRequired(false);
        return false;
    } catch (PDOException $e) {
        setPasswordChangeRequired(false);
        return false;
    }
}

/**
 * Déconnexion utilisateur
 */
function logout() {
    session_destroy();
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

/**
 * Obtenir les données de l'utilisateur connecté
 */
function getCurrentAdmin() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, fullname, email FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Validation d'un formulaire de formulaire
 */
function validateForm($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = isset($data[$field]) ? trim($data[$field]) : '';
        
        if (strpos($rule, 'required') !== false && empty($value)) {
            $errors[$field] = "Ce champ est requis.";
        }
        
        if (strpos($rule, 'email') !== false && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = "Email invalide.";
        }
        
        if (strpos($rule, 'min:') !== false && !empty($value)) {
            preg_match('/min:(\d+)/', $rule, $matches);
            if (strlen($value) < $matches[1]) {
                $errors[$field] = "Minimum {$matches[1]} caractères requis.";
            }
        }
    }
    
    return $errors;
}

/**
 * Sanitiser l'entrée utilisateur
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return trim((string)$input);
}

/**
 * CSRF token generation
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token verification
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash message functions
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Vérifie si l'IP courante est bloquée par le rate-limiter de connexion.
 */
function isLoginRateLimited(): bool {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip VARCHAR(45) NOT NULL,
            attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip (ip)
        ) ENGINE=InnoDB");
        $pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)")->execute();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $stmt->execute([$ip]);
        return (int)$stmt->fetchColumn() >= 5;
    } catch (PDOException $e) {
        return false;
    }
}

function recordFailedLogin(): void {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    try {
        $pdo->prepare("INSERT INTO login_attempts (ip) VALUES (?)")->execute([$ip]);
    } catch (PDOException $e) {}
}

function clearFailedLogins(): void {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    try {
        $pdo->prepare("DELETE FROM login_attempts WHERE ip = ?")->execute([$ip]);
    } catch (PDOException $e) {}
}

/**
 * Supprime les vecteurs XSS d'un contenu HTML CMS.
 * Utilise DOMDocument pour une sanitisation fiable (pas de regex).
 * Autorise les balises courantes (p, h1-h6, strong, em, a, img, ul, ol, li, table, etc.)
 * Supprime les scripts, iframes, forms, event handlers, javascript: URLs.
 */
function sanitize_body_html(string $html): string {
    if (trim($html) === '') return '';

    $allowedTags = [
        'p','br','b','strong','i','em','u','s','sub','sup',
        'h1','h2','h3','h4','h5','h6',
        'a','img','figure','figcaption',
        'ul','ol','li',
        'table','thead','tbody','tr','th','td',
        'blockquote','pre','code',
        'div','span','section','article',
        'hr','abbr','small','mark',
    ];
    $allowedAttributes = [
        'href','src','alt','title','width','height','class','id','style',
        'target','rel','colspan','rowspan','scope','align','valign',
    ];

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>' . $html . '</body></html>', LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $body = $doc->getElementsByTagName('body')->item(0);
    if (!$body) return '';

    $toRemove = [];

    foreach ($body->getElementsByTagName('*') as $node) {
        if ($node->nodeType !== XML_ELEMENT_NODE) continue;
        $tag = strtolower($node->tagName);

        if (in_array($tag, ['script','style','iframe','object','embed','applet','form','input','textarea','select','button','link','meta','head'], true)) {
            $toRemove[] = $node;
            continue;
        }

        if (!in_array($tag, $allowedTags, true)) {
            while ($node->firstChild) {
                $node->parentNode->insertBefore($node->firstChild, $node);
            }
            $toRemove[] = $node;
            continue;
        }

        foreach ($node->attributes as $attr) {
            $name = strtolower($attr->name);
            $keep = in_array($name, $allowedAttributes, true) || str_starts_with($name, 'data-');
            if (!$keep || preg_match('/^on/i', $name)) {
                $node->removeAttributeNode($attr);
            }
        }

        foreach (['href','src','action'] as $urlAttr) {
            if ($node->hasAttribute($urlAttr) && preg_match('/^\s*javascript\s*:/i', $node->getAttribute($urlAttr))) {
                $node->removeAttribute($urlAttr);
            }
        }

        if ($node->hasAttribute('style') && preg_match('/expression\s*\(|url\s*\(\s*["\']?\s*javascript:/i', $node->getAttribute('style'))) {
            $node->removeAttribute('style');
        }
    }

    foreach ($toRemove as $node) {
        if ($node->parentNode) $node->parentNode->removeChild($node);
    }

    $result = '';
    foreach ($body->childNodes as $child) {
        if ($child->nodeType === XML_ELEMENT_NODE || $child->nodeType === XML_TEXT_NODE) {
            $result .= $doc->saveHTML($child);
        }
    }
    return trim($result);
}

/**
 * Log audit
 */
function logAudit($action, $details = '') {
    global $pdo;
    
    if (!isLoggedIn()) {
        return;
    }
    
    try {
        // Créer la table audit si elle n'existe pas
        $createTable = "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT,
            action VARCHAR(255),
            details TEXT,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
        ) ENGINE=InnoDB";
        
        $pdo->exec($createTable);
        
        $stmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['admin_id'],
            $action,
            $details,
            $_SERVER['REMOTE_ADDR']
        ]);
    } catch (PDOException $e) {
        // Silencieusement échouer
    }
}
