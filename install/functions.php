<?php
// install/functions.php — Fonctions utilitaires pour l'installation Maokaa CMS

/**
 * Vérifier que les extensions PHP requises sont disponibles
 */
function check_php_extensions(array $required): bool
{
    $missing = [];
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }
    return empty($missing);
}

/**
 * Vérifier qu'un répertoire est writable (et s'il existe)
 */
function is_writable_path(string $path): bool
{
    if (!is_dir($path)) {
        // Tentative de création
        @mkdir($path, 0755, true);
        return is_dir($path);
    }
    return is_writable($path);
}

/**
 * Générer une BASE_URL automatique
 */
function detect_base_url(): string
{
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        $projRoot = 'C:/xampp/htdocs/Hebergement/maokaa'; // Cible Windows
        if (strpos($projRoot, $docRoot) === 0) {
            return substr($projRoot, strlen($docRoot)) . '/';
        }
    }
    // Fallback : construire à partir de l'URL actuelle
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_URI'] ?? "{$protocol}://{$host}/";
    return rtrim(dirname($script), '/') . '/';
}

/**
 * Initialiser la connexion à la base de données
 */
function init_db(string $dsn, string $user, string $pass): ?PDO
{
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("DB Connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Vérifier et appliquer les permissions de répertoires
 */
function setup_directory_permissions(array $dirs): bool
{
    $ok = true;
    foreach ($dirs as $dir) {
        if (!is_writable_path($dir)) {
            error_log("Impossible de définir les permissions sur : $dir");
            $ok = false;
        } else {
            @chmod($dir, 0755);
        }
    }
    return $ok;
}

/**
 * Créer le template .env s'il n'existe pas
 */
function create_env_template(string $path): bool
{
    $template = '# Configuration d\'environnement Maokaa CMS
# Copier ce fichier en .env et modifier selon vos besoins

BASE_URL=/Hebergement/maokaa/
DB_HOST=localhost
DB_NAME=maokaa
DB_USER=root
DB_PASS=

UPLOAD_DIR=assets/images/
BROCHURE_DIR=assets/brochures/
MAX_IMAGE_SIZE=2097152
MAX_PDF_SIZE=5242880

# Google reCAPTCHA v2 (optionnel)
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=

# IA Integration
AI_API_URL=https://api.deepseek.com/v1
AI_API_KEY=
AI_MODEL=deepseek-v4-flash
';

// Le fichier .env.template existe déjà, on ne l'écrit que si demandé
// ou si le fichier .env n'existe pas
if (!file_exists($path)) {
    return file_put_contents($path, $template);
}
    return true;
}

/**
 * Validation du mot de passe admin
 */
function is_valid_password(string $password): bool
{
    // Minimum 8 caractères, 1 majuscule, 1 chiffre
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[0-9]/', $password);
}