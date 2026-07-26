<?php
// index.php - Front Controller Principal

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/theme.php';
require_once 'includes/modules.php';
require_once 'app/models/Content.php';

// Initialiser les modules
ModuleRegistry::init($pdo);

// Initialiser le gestionnaire de thème
ThemeManager::init($pdo);

// Résoudre le slug depuis l'URL
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/');
$path     = $uri;

if ($basePath && strpos($uri, $basePath) === 0) {
    $path = substr($uri, strlen($basePath));
}

$path = trim($path, '/');
$slug = empty($path) || basename($path) === 'index.php' ? 'home' : basename($path, '.php');
$slug = strtolower(preg_replace('/[^a-z0-9\-_]/', '', $slug));

// Routes spéciales
if ($slug === 'login' || $slug === 'login.php') {
    include __DIR__ . '/login.php';
    exit;
}

if (strpos($slug, 'admin') === 0) {
    if (isLoggedIn()) {
        include __DIR__ . '/admin/dashboard.php';
    } else {
        header('Location: ' . BASE_URL . 'login.php');
    }
    exit;
}

// Charger la page depuis la table content
$contentModel = new Content($pdo);
$page         = $contentModel->findBySlug($slug);

if (!$page) {
    $page404 = $contentModel->findBySlug('404');
    if ($page404) {
        http_response_code(404);
        $page = $page404;
    } else {
        http_response_code(404);
        include __DIR__ . '/app/views/errors/404.php';
        exit;
    }
}

// Charger le template depuis le thème actif
$template     = !empty($page['template']) ? $page['template'] : 'default';
$templateFile = ThemeManager::template($template);

if (empty($templateFile)) {
    http_response_code(500);
    echo '<p>Template introuvable : ' . htmlspecialchars($template) . '</p>';
    exit;
}

include $templateFile;
