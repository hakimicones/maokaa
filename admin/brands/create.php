<?php
// admin/brands/create.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../app/models/Brand.php';

requirePasswordChange();

$brandModel = new Brand($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
        ];

        if (empty($data['name'])) {
            $error = 'Le nom de la marque est requis';
        } else {
            $logoPath = null;
            if (!empty($_FILES['logo']['name'])) {
                $uploadDir = __DIR__ . '/../../assets/images/brands/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $result = upload_image($_FILES['logo'], $uploadDir, 'brand');
                if (isset($result['error'])) {
                    $error = $result['error'];
                } else {
                    $logoPath = 'assets/images/brands/' . $result['filename'];
                }
            }

            if (empty($error)) {
                $data['logo'] = $logoPath;
                if ($brandModel->create($data)) {
                    setFlash('success', 'Marque créée avec succès');
                    header('Location: ' . BASE_URL . 'admin/dashboard.php?section=brands');
                    exit;
                }

                $error = 'Erreur lors de la création de la marque';
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
    <title>Ajouter une marque - Administrator</title>
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
            <h2 class="mb-1">Ajouter une marque</h2>
            <p class="text-muted mb-0">Créez une marque pour l’afficher sur le site.</p>
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
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Site web</label>
                <input type="url" name="website" class="form-control" placeholder="https://example.com">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
            </div>
            <button type="submit" class="btn btn-primary">Créer la marque</button>
        </form>
    </div>
</div>
</body>
</html>
