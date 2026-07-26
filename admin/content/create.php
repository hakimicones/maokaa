<?php
// admin/content/create.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/theme.php';
require_once __DIR__ . '/../../app/models/Content.php';

requirePasswordChange();

$model = new Content($pdo);
$error = '';

$availableTemplates = ThemeManager::getAvailableTemplates();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'slug' => trim($_POST['slug'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'subtitle' => trim($_POST['subtitle'] ?? ''),
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'template' => in_array($_POST['template'] ?? '', $availableTemplates, true) ? $_POST['template'] : 'default',
            'status' => $_POST['status'] ?? 'draft',
            'language' => $_POST['language'] ?? 'fr',
        ];

        $reservedSlugs = ['admin', 'login', 'logout', 'setup', 'sitemap', 'index', 'router'];
        if (empty($data['slug']) || empty($data['title'])) {
            $error = 'Slug et titre requis.';
        } elseif (in_array(strtolower($data['slug']), $reservedSlugs)) {
            $error = 'Ce slug est réservé par le système (' . implode(', ', $reservedSlugs) . ').';
        } else {
            $newId = $model->create($data);
            setFlash('success', 'Page créée avec succès');
            header('Location: ' . BASE_URL . 'admin/content/body-editor.php?id=' . (int)$newId);
            exit;
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une page - Administrator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }
        .form-card { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,.06); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Ajouter une page</h2>
            <p class="text-muted mb-0">Créez une nouvelle page statique.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/content/index.php" class="btn btn-outline-secondary">Retour</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="mb-3"><label class="form-label">Slug *</label><input name="slug" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Titre *</label><input name="title" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Sous-titre</label><input name="subtitle" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Meta title</label><input name="meta_title" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Meta description</label><textarea name="meta_description" class="form-control" rows="3"></textarea></div>
            <div class="mb-3"><label class="form-label">Template</label>
                <select name="template" class="form-select">
                    <?php foreach ($availableTemplates as $tpl): ?>
                    <option value="<?php echo htmlspecialchars($tpl); ?>" <?php echo $tpl === 'default' ? 'selected' : ''; ?>><?php echo htmlspecialchars($tpl); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="published">published</option>
                    <option value="draft">draft</option>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Langue</label><input name="language" class="form-control" value="fr"></div>
            <div class="alert alert-info mb-3">
                Le contenu visuel sera édité dans une vue dédiée après création de la page.
            </div>
            <button class="btn btn-primary">Créer et ouvrir l’éditeur</button>
        </form>
    </div>
</div>
</body>
</html>
