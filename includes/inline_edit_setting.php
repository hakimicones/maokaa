<?php
// includes/inline_edit_setting.php — AJAX handler pour l'édition inline des settings (footer)

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/settings_helpers.php';

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

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$csrf = $input['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
    exit;
}

$allowed_keys = ['site_name', 'site_name_html', 'site_name_color', 'site_name_color_accent', 'site_name_font_family', 'site_name_font_size', 'site_name_bold', 'site_name_italic', 'site_name_underline', 'footer_phone', 'footer_email', 'footer_address', 'footer_description', 'footer_copyright', 'footer_columns'];
$key = $input['key'] ?? '';
if (!in_array($key, $allowed_keys, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Clé non autorisée']);
    exit;
}

// site_name_html : HTML riche (couleurs par mot), nettoyé via sanitize_brand_html
if ($key === 'site_name_html') {
    $value = sanitize_brand_html((string)($input['value'] ?? ''));
} else {
    $value = strip_tags($input['value'] ?? '');
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Remplacer les espaces insécables (&nbsp;) par des espaces normales
    $value = str_replace("\xC2\xA0", ' ', $value);
    $value = trim($value);
}

try {
    set_setting($pdo, $key, $value);
    logAudit('inline_edit_setting', "Setting '{$key}' modifié");
    echo json_encode(['success' => true, 'message' => 'Modification enregistrée']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
