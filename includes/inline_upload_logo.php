<?php
// includes/inline_upload_logo.php — Upload AJAX du logo depuis le front (clic sur le logo)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/settings_helpers.php';
require_once __DIR__ . '/upload.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
    exit;
}

// Suppression du logo
if (!empty($_POST['remove_logo'])) {
    set_setting($pdo, 'site_logo', '');
    logAudit('remove_logo', 'Logo supprimé');
    echo json_encode(['success' => true, 'message' => 'Logo supprimé']);
    exit;
}

// Upload d'un nouveau logo
if (empty($_FILES['site_logo']['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu']);
    exit;
}

$uploadDir = __DIR__ . '/../assets/images/settings/';
$result = upload_image($_FILES['site_logo'], $uploadDir, 'logo');

if (!empty($result['error'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $result['error']]);
    exit;
}

$relative = 'assets/images/settings/' . $result['filename'];
set_setting($pdo, 'site_logo', $relative);
logAudit('upload_logo', 'Logo modifié');

echo json_encode([
    'success' => true,
    'message' => 'Logo modifié',
    'url'     => BASE_URL . $relative,
]);
