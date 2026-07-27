<?php
// admin/content/edit.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/theme.php';
require_once __DIR__ . '/../../app/models/Content.php';

requirePasswordChange();

$model = new Content($pdo);
$pageId = (int)($_GET['id'] ?? 0);
$page = $model->findById($pageId);

if (!$page) {
    http_response_code(404);
    echo '<h1>Page introuvable</h1>';
    exit;
}

$error = '';
$successMessage = '';

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
            'body' => $page['body'] ?? '',
            'template' => in_array($_POST['template'] ?? '', $availableTemplates, true) ? $_POST['template'] : 'default',
            'status' => $_POST['status'] ?? 'draft',
            'language' => $_POST['language'] ?? 'fr',
        ];

        $reservedSlugs = ['admin', 'login', 'logout', 'setup', 'sitemap', 'index', 'router'];
        if (empty($data['slug']) || empty($data['title'])) {
            $error = 'Slug et titre requis.';
        } elseif (
            in_array(strtolower($data['slug']), $reservedSlugs) &&
            strtolower($data['slug']) !== strtolower($page['slug'])
        ) {
            $error = 'Ce slug est réservé par le système (' . implode(', ', $reservedSlugs) . ').';
        } else {
            $model->update($pageId, $data);
            $successMessage = 'Métadonnées mises à jour avec succès.';

            if (($_POST['action'] ?? '') === 'open_editor') {
                setFlash('success', 'Métadonnées mises à jour. Vous pouvez maintenant éditer le contenu visuel.');
                header('Location: ' . BASE_URL . 'admin/content/body-editor.php?id=' . $pageId);
                exit;
            }

            setFlash('success', 'Page mise à jour avec succès');
            header('Location: ' . BASE_URL . 'admin/content/index.php');
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
    <title>Modifier une page - Administrator</title>
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
            <h2 class="mb-1">Modifier une page</h2>
            <p class="text-muted mb-0">Modifiez les métadonnées de la page puis ouvrez l’éditeur visuel dédié.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php?section=content" class="btn btn-outline-secondary">Retour</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="mb-3"><label class="form-label">Slug *</label><input name="slug" class="form-control" value="<?php echo htmlspecialchars($page['slug']); ?>" required></div>
            <div class="mb-3"><label class="form-label">Titre *</label><input name="title" class="form-control" value="<?php echo htmlspecialchars($page['title']); ?>" required></div>
            <div class="mb-3"><label class="form-label">Sous-titre</label><input name="subtitle" class="form-control" value="<?php echo htmlspecialchars($page['subtitle'] ?? ''); ?>"></div>
            <div class="mb-3"><label class="form-label">Meta title</label><input name="meta_title" class="form-control" value="<?php echo htmlspecialchars($page['meta_title'] ?? ''); ?>"></div>
            <div class="mb-3"><label class="form-label">Meta description</label><textarea name="meta_description" class="form-control" rows="3"><?php echo htmlspecialchars($page['meta_description'] ?? ''); ?></textarea></div>
            <div class="mb-3"><label class="form-label">Template</label>
                <select name="template" class="form-select">
                    <?php foreach ($availableTemplates as $tpl): ?>
                    <option value="<?php echo htmlspecialchars($tpl); ?>" <?php echo $page['template'] === $tpl ? 'selected' : ''; ?>><?php echo htmlspecialchars($tpl); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="published" <?php echo $page['status'] === 'published' ? 'selected' : ''; ?>>published</option>
                    <option value="draft" <?php echo $page['status'] === 'draft' ? 'selected' : ''; ?>>draft</option>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Langue</label><input name="language" class="form-control" value="<?php echo htmlspecialchars($page['language'] ?? 'fr'); ?>"></div>
            <div class="alert alert-info mb-3">
                Le contenu visuel est édité dans une vue dédiée. Vous pouvez enregistrer les métadonnées ici puis ouvrir l’éditeur.
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer les métadonnées</button>
                <button type="submit" name="action" value="open_editor" class="btn btn-outline-primary">Ouvrir l’éditeur visuel</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
