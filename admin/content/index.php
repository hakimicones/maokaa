<?php
// admin/content/index.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/models/Content.php';

requirePasswordChange();

$model = new Content($pdo);
$pages = $model->listAll(false);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du contenu - Administrator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Gestion du contenu</h2>
            <p class="text-muted mb-0">Gérez les pages statiques du site.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline-secondary">Retour au dashboard</a>
    </div>

    <a href="create.php" class="btn btn-success mb-4">Ajouter une page</a>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Slug</th>
                    <th>Titre</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                    <tr>
                        <td><?php echo (int)$page['id']; ?></td>
                        <td><?php echo htmlspecialchars($page['slug']); ?></td>
                        <td><?php echo htmlspecialchars($page['title']); ?></td>
                        <td><?php echo htmlspecialchars($page['template']); ?></td>
                        <td><?php echo htmlspecialchars($page['status']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo (int)$page['id']; ?>" class="btn btn-sm btn-primary">Éditer</a>
                            <a href="<?php echo BASE_URL . htmlspecialchars($page['slug']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Voir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
