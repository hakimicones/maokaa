<?php
require_once __DIR__ . '/captcha.php';
$data = initCaptcha();
header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
// Passer le token via un cookie ou le retourner dans l'en-tête
// On utilise un header personnalisé + fallback query string
if (!headers_sent()) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    setcookie('captcha_token', $data['token'], [
        'expires'  => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);
}
echo $data['svg'];
