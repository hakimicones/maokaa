<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/theme.php';

requirePasswordChange();
requireRole('admin');

ThemeManager::init($pdo);
$themes  = ThemeManager::list();
$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['activate'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide.';
        $msgType = 'danger';
    } else {
        $folder = preg_replace('/[^a-z0-9_-]/', '', $_POST['activate']);
        if (ThemeManager::setActive($pdo, $folder)) {
            $message = 'Thème « ' . htmlspecialchars($folder) . ' » activé.';
            $msgType = 'success';
            $themes  = ThemeManager::list();
        } else {
            $message = 'Impossible d\'activer ce thème.';
            $msgType = 'danger';
        }
    }
}

$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des thèmes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-palette me-2"></i>Thèmes</h1>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($themes as $theme): ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm <?php echo $theme['active'] ? 'border-primary border-2' : ''; ?>">
                <?php if ($theme['preview']): ?>
                <img src="<?php echo htmlspecialchars($theme['preview']); ?>" class="card-img-top" alt="preview" style="height:180px;object-fit:cover;">
                <?php else: ?>
                <div class="bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="height:180px;">
                    <i class="fas fa-image fa-3x text-muted"></i>
                </div>
                <?php endif; ?>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($theme['name'] ?? $theme['folder']); ?></h5>
                        <?php if ($theme['active']): ?>
                        <span class="badge bg-primary">Actif</span>
                        <?php endif; ?>
                    </div>
                    <p class="card-text text-muted small"><?php echo htmlspecialchars($theme['description'] ?? ''); ?></p>
                    <p class="small text-muted mb-3">Version : <?php echo htmlspecialchars($theme['version'] ?? '—'); ?></p>
                    <?php if (!$theme['active']): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                        <input type="hidden" name="activate"   value="<?php echo htmlspecialchars($theme['folder']); ?>">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-check me-1"></i>Activer
                        </button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-outline-primary btn-sm w-100" disabled>Thème actif</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-5 p-4 bg-white rounded shadow-sm">
        <h5><i class="fas fa-info-circle me-2"></i>Créer un nouveau thème</h5>
        <p class="text-muted mb-1">Créez un dossier dans <code>themes/{nom-du-theme}/</code> avec la structure suivante :</p>
        <pre class="bg-light p-3 rounded small">themes/mon-theme/
├── theme.json          ← métadonnées
├── preview.png         ← aperçu (optionnel)
├── templates/
│   ├── home.php
│   ├── default.php
│   ├── page.php
│   └── ...
└── partials/
    ├── navbar.php
    └── footer.php</pre>
        <p class="small text-muted mb-0">Les templates manquants tombent automatiquement en fallback sur le thème <strong>default</strong>.</p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
