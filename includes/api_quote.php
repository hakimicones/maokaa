<?php
// includes/api_quote.php — API AJAX pour soumettre une demande de devis
// Protégé par : CSRF token, reCAPTCHA v3, rate limiting par IP

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../app/models/Contact.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// 1. Rate limiting : max 5 requêtes par IP toutes les 15 minutes
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS quote_rate_limit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip (ip)
    ) ENGINE=InnoDB");
    $pdo->prepare("DELETE FROM quote_rate_limit WHERE created_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)")->execute();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quote_rate_limit WHERE ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$ip]);
    if ((int)$stmt->fetchColumn() >= 5) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Trop de requêtes. Réessayez dans 15 minutes.']);
        exit;
    }
} catch (PDOException $e) {
    // Table non créée, on continue sans rate limiting
}

// 2. CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}
$csrfToken = $input['_token'] ?? $_POST['_token'] ?? '';
if (!verifyCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide.']);
    exit;
}

// 3. reCAPTCHA v3
$recaptchaToken = $input['g-recaptcha-response'] ?? $_POST['g-recaptcha-response'] ?? '';
$recaptchaSecret = defined('RECAPTCHA_SECRET_KEY') ? RECAPTCHA_SECRET_KEY : '';
if ($recaptchaToken !== '' && $recaptchaSecret !== '') {
    $resp = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'secret'   => $recaptchaSecret,
                'response' => $recaptchaToken,
                'remoteip' => $ip,
            ]),
        ],
    ]));
    if ($resp === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur vérification anti-spam.']);
        exit;
    }
    $json = json_decode($resp, true);
    if (!($json['success'] ?? false) || ($json['score'] ?? 0) < 0.5) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Vérification anti-spam échouée.']);
        exit;
    }
} elseif ($recaptchaSecret !== '') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token reCAPTCHA manquant.']);
    exit;
}

// 4. Validation et insertion
$nom      = trim($input['nom']      ?? '');
$email    = trim($input['email']    ?? '');
$telephone= trim($input['telephone']?? '');
$produit  = trim($input['produit']  ?? '');
$quantite = (int)($input['quantite']?? 1);
$message  = trim($input['message']  ?? '');

if (empty($nom) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nom et email requis.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email invalide.']);
    exit;
}

$fullMessage = "Produit : " . htmlspecialchars($produit, ENT_QUOTES, 'UTF-8')
             . "\nQuantité : " . $quantite
             . "\n\nMessage : " . htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

$model = new Contact($pdo);
$ok = $model->create([
    'nom'       => $nom,
    'email'     => $email,
    'telephone' => $telephone,
    'sujet'     => '[Demande de devis] ' . $produit,
    'message'   => $fullMessage,
]);

// 5. Enregistrer la tentative (rate limiting)
if ($ok) {
    try {
        $pdo->prepare("INSERT INTO quote_rate_limit (ip) VALUES (?)")->execute([$ip]);
    } catch (PDOException $e) {}
    echo json_encode(['success' => true, 'message' => 'Votre demande de devis a bien été envoyée.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi.']);
}
