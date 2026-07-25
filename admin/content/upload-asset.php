<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';

requirePasswordChange();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$uploadDir = __DIR__ . '/../../assets/images/uploads/';

$result = upload_image($_FILES['file'] ?? [], $uploadDir, 'img', [
    'image/jpeg'    => 'jpg',
    'image/png'     => 'png',
    'image/gif'     => 'gif',
    'image/webp'    => 'webp',
]);

if (isset($result['error'])) {
    http_response_code(400);
    echo json_encode(['error' => $result['error']]);
    exit;
}

$filename = $result['filename'];
$url = BASE_URL . 'assets/images/uploads/' . $filename;
echo json_encode(['data' => [['src' => $url, 'name' => $filename]]]);
