<?php
// includes/api_menu_items.php — API GET : items de niveau supérieur d'un menu (pour dropdown parent_id)

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/models/Menu.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$menuName = trim($_GET['menu'] ?? 'main');
$menuModel = new Menu($pdo);
$menu = $menuModel->getByName($menuName);

if (!$menu) {
    echo json_encode([]);
    exit;
}

$items = $menuModel->getItems($menu['id']);
$result = [];
foreach ($items as $item) {
    $result[] = [
        'id'    => (int)$item['id'],
        'title' => $item['title'],
        'url'   => $item['url'],
    ];
}

echo json_encode($result);
