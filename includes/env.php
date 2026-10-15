<?php
// includes/env.php — Charger les variables d'environnement depuis .env

function loadEnv($path = null) {
    if (!$path) {
        $path = __DIR__ . '/../.env';
    }

    if (!file_exists($path)) {
        throw new Exception(".env file not found at $path");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $projectRoot = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;

    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parser la ligne KEY=VALUE
        if (strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Retirer les guillemets si présents
        if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
            $value = substr($value, 1, -1);
        }

        // Construire les chemins complets pour les répertoires
        if ($key === 'UPLOAD_DIR') {
            $value = $projectRoot . $value;
        } elseif ($key === 'BROCHURE_DIR') {
            $value = $projectRoot . $value;
        } elseif ($key === 'MAX_IMAGE_SIZE' || $key === 'MAX_PDF_SIZE') {
            $value = (int)$value;
        } elseif ($key === 'BASE_URL') {
            // Auto-détection calcule le chemin relatif au DOCUMENT_ROOT.
            // Fonctionne en sous-dossier (XAMPP: /Hebergement/maokaa/) comme à la racine (prod: /)
            if (php_sapi_name() !== 'cli' && isset($_SERVER['DOCUMENT_ROOT'])) {
                $docRoot   = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
                $projRoot  = rtrim(str_replace('\\', '/', $projectRoot), '/');
                if (strpos($projRoot, $docRoot) === 0) {
                    $value = substr($projRoot, strlen($docRoot)) . '/';
                }
            }
            // En CLI, garder la valeur .env comme fallback
        }

        // Définir la variable d'environnement et la constante
        putenv("$key=$value");
        if (!defined($key)) {
            define($key, $value);
        }
    }
}

// Charger le fichier .env au démarrage
loadEnv();

// Configuration du mode développement
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

// Activer l'affichage des erreurs en mode dev
if (APP_DEBUG === true || APP_DEBUG === 'true' || APP_DEBUG === 1 || APP_DEBUG === '1') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

