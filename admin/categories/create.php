<?php
// admin/categories/create.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/models/Category.php';

requirePasswordChange();

$categoryModel = new Category($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'display_order' => (int)($_POST['display_order'] ?? 0),
        ];

        if (empty($data['name'])) {
            $error = 'Le nom de la catégorie est requis';
        } else {
            if ($categoryModel->create($data)) {
                setFlash('success', 'Catégorie créée avec succès');
                header('Location: ' . BASE_URL . 'admin/dashboard.php?section=categories');
                exit;
            }

            $error = 'Erreur lors de la création de la catégorie';
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
    <title>Ajouter une catégorie - Administrator</title>
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
            <h2 class="mb-1">Ajouter une catégorie</h2>
            <p class="text-muted mb-0">Créez une catégorie pour organiser vos produits.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php?section=categories" class="btn btn-outline-secondary">Retour</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="mb-3">
                <label class="form-label">Nom de la catégorie *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Ordre d'affichage</label>
                <input type="number" name="display_order" class="form-control" value="0">
            </div>
            <button type="submit" class="btn btn-primary">Créer la catégorie</button>
        </form>
    </div>
</div>
</body>
</html>
