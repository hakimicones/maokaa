<?php
// admin/messages/view.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/models/Contact.php';

requirePasswordChange();

$contactModel = new Contact($pdo);
$messageId = (int)($_GET['id'] ?? 0);
$message = $contactModel->getById($messageId);

if (!$message) {
    http_response_code(404);
    echo '<h1>Message introuvable</h1>';
    exit;
}

if (empty($message['lu'])) {
    $contactModel->markAsRead($messageId);
    $message['lu'] = 1;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message de contact - Administrator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Message de contact</h2>
            <p class="text-muted mb-0">Consultez et archivez le message.</p>
        </div>
        <a href="<?php echo return_url(BASE_URL . 'admin/dashboard.php?section=messages'); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card">
        <div class="card-body">
            <p><strong>Nom :</strong> <?php echo htmlspecialchars($message['nom']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($message['email']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($message['telephone'] ?? ''); ?></p>
            <p><strong>Sujet :</strong> <?php echo htmlspecialchars($message['sujet'] ?? ''); ?></p>
            <hr>
            <p><strong>Message :</strong></p>
            <div class="border rounded p-3 bg-light"><?php echo nl2br(htmlspecialchars($message['message'] ?? '')); ?></div>
            <p class="mt-3 mb-0"><strong>Créé le :</strong> <?php echo htmlspecialchars($message['created_at']); ?></p>
        </div>
    </div>
</div>
</body>
</html>
