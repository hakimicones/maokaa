<?php
// includes/api_page_create.php — API AJAX : créer une page (+ optionnellement un menu item)
// Appelé depuis la barre admin front (#ie-toolbar)

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/theme.php';
require_once __DIR__ . '/../app/models/Content.php';
require_once __DIR__ . '/../app/models/Menu.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

if (isPasswordChangeRequired()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Changement de mot de passe requis']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

if (!verifyCSRFToken($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
    exit;
}

$title = trim($input['title'] ?? '');
$slug  = trim($input['slug'] ?? '');

if ($title === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le titre est requis']);
    exit;
}

if ($slug === '') {
    $slug = strtolower($title);
    $slug = preg_replace('/[\x{0300}-\x{036f}]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug));
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
}

$reservedSlugs = ['admin', 'login', 'logout', 'setup', 'sitemap', 'index', 'router'];
if (in_array(strtolower($slug), $reservedSlugs, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ce slug est réservé par le système']);
    exit;
}

$existing = $pdo->prepare("SELECT id FROM content WHERE slug = ?");
$existing->execute([$slug]);
if ($existing->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Ce slug existe déjà. Choisissez un autre.']);
    exit;
}

$availableTemplates = ThemeManager::getAvailableTemplates();
$template = in_array($input['template'] ?? '', $availableTemplates, true) ? $input['template'] : 'default';
$status   = in_array($input['status'] ?? '', ['published', 'draft'], true) ? $input['status'] : 'draft';

$contentModel = new Content($pdo);

$pageId = $contentModel->create([
    'slug'    => $slug,
    'title'   => $title,
    'template' => $template,
    'status'  => $status,
    'body'    => '',
    'language' => 'fr',
]);

if (!$pageId) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la création de la page']);
    exit;
}

$menuItemId = null;
$addToMenu = !empty($input['add_to_menu']);

if ($addToMenu) {
    $menuName = trim($input['menu_name'] ?? 'main');
    $menuModel = new Menu($pdo);
    $menu = $menuModel->getByName($menuName);

    if ($menu) {
        $parentId = null;
        if (!empty($input['menu_parent_id'])) {
            $parentId = (int)$input['menu_parent_id'];
        }

        $maxPos = $pdo->prepare("SELECT COALESCE(MAX(position), 0) + 1 FROM menu_items WHERE menu_id = ?");
        $maxPos->execute([$menu['id']]);
        $position = (int)$maxPos->fetchColumn();

        $menuModel->addItem(
            $menu['id'],
            $title,
            '/' . $slug,
            $position,
            null,
            $parentId
        );
        $menuItemId = $pdo->lastInsertId();
    }
}

echo json_encode([
    'success' => true,
    'page_id' => (int)$pageId,
    'menu_item_id' => $menuItemId ? (int)$menuItemId : null,
    'url' => BASE_URL . $slug,
    'edit_url' => BASE_URL . 'admin/content/body-editor.php?id=' . (int)$pageId,
]);
