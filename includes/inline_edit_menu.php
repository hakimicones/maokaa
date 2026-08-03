<?php
// includes/inline_edit_menu.php — AJAX : ajouter / modifier / supprimer un élément de menu depuis le header

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/app/models/Menu.php';

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

$menuModel = new Menu($pdo);
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $menuId  = (int)($_POST['menu_id'] ?? 0);
            $title   = trim($_POST['title'] ?? '');
            $url     = trim($_POST['url'] ?? '');
            if ($menuId <= 0 || $title === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Titre ou menu invalide']);
                exit;
            }
            $params = json_encode(['icon' => isset($_POST['show_icon']) ? 1 : 0]);
            $menuModel->addItem(
                $menuId,
                $title,
                $url !== '' ? $url : '#',
                (int)($_POST['position'] ?? 0),
                trim($_POST['icon'] ?? '') ?: null,
                !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                $params
            );
            logAudit('menu_add', "Ajout menu : {$title}");
            echo json_encode(['success' => true, 'message' => 'Élément ajouté']);
            break;

        case 'update':
            $itemId = (int)($_POST['id'] ?? 0);
            if ($itemId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Identifiant invalide']);
                exit;
            }
            $params = json_encode(['icon' => isset($_POST['show_icon']) ? 1 : 0]);
            $menuModel->updateItem($itemId, [
                'title'     => trim($_POST['title'] ?? ''),
                'url'       => trim($_POST['url'] ?? '') ?: '#',
                'icon'      => trim($_POST['icon'] ?? '') ?: null,
                'position'  => (int)($_POST['position'] ?? 0),
                'active'    => isset($_POST['active']) ? 1 : 0,
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'params'    => $params,
            ]);
            logAudit('menu_update', "Modification menu #{$itemId}");
            echo json_encode(['success' => true, 'message' => 'Élément mis à jour']);
            break;

        case 'delete':
            $itemId = (int)($_POST['id'] ?? 0);
            if ($itemId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Identifiant invalide']);
                exit;
            }
            $menuModel->deleteItem($itemId);
            logAudit('menu_delete', "Suppression menu #{$itemId}");
            echo json_encode(['success' => true, 'message' => 'Élément supprimé']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action inconnue']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
