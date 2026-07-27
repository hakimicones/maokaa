<?php
// admin/brands/edit.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../app/models/Brand.php';

requirePasswordChange();

$brandModel = new Brand($pdo);
$brandId = (int)($_GET['id'] ?? 0);
$brand = $brandModel->getById($brandId);

if (!$brand) {
    http_response_code(404);
    echo '<h1>Marque introuvable</h1>';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];

        if (empty($data['name'])) {
            $error = 'Le nom de la marque est requis';
        } else {
            $data['logo'] = $brand['logo'] ?? null;
            if (!empty($_FILES['logo']['name'])) {
                $result = upload_image($_FILES['logo'], __DIR__ . '/../../assets/images/brands/', 'brand');
                if (isset($result['error'])) {
                    $error = $result['error'];
                } else {
                    $data['logo'] = 'assets/images/brands/' . $result['filename'];
                }
            }

            if (empty($error)) {
                if ($brandModel->update($brandId, $data)) {
                    setFlash('success', 'Marque mise à jour avec succès');
                    header('Location: ' . BASE_URL . 'admin/dashboard.php?section=brands');
                    exit;
                }
                $error = 'Erreur lors de la mise à jour de la marque';
            }
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
    <title>Modifier une marque - Administrator</title>
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
            <h2 class="mb-1">Modifier une marque</h2>
            <p class="text-muted mb-0">Mettez à jour les informations de la marque.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php?section=brands" class="btn btn-outline-secondary">Retour</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="mb-3">
                <label class="form-label">Nom de la marque *</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($brand['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Site web</label>
                <input type="url" name="website" class="form-control" value="<?php echo htmlspecialchars($brand['website'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control"><?php echo htmlspecialchars($brand['description'] ?? ''); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="display_order" class="form-control" value="<?php echo (int)($brand['display_order'] ?? 0); ?>">
                </div>
                <div class="col-md-6 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="active" name="active" <?php echo !empty($brand['active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="active">Active</label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Nouveau logo</label>
                <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                <?php if (!empty($brand['logo'])): ?>
                    <div class="mt-2"><img src="<?php echo htmlspecialchars($brand['logo']); ?>" style="max-width: 180px; border-radius: 8px;"></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>
</body>
</html>
