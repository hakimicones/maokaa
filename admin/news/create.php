<?php
// admin/news/create.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../app/models/News.php';

requirePasswordChange();

$newsModel = new News($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'status' => $_POST['status'] ?? 'draft',
            'author_id' => $_SESSION['admin_id'] ?? null,
        ];

        if (empty($data['title'])) {
            $error = 'Le titre est requis';
        } else {
            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                $uploadDir = __DIR__ . '/../../assets/images/news/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $result = upload_image($_FILES['image'], $uploadDir, 'news');
                if (isset($result['error'])) {
                    $error = $result['error'];
                } else {
                    $imagePath = 'assets/images/news/' . $result['filename'];
                }
            }

            if (empty($error)) {
                $data['image'] = $imagePath;
                if ($newsModel->create($data)) {
                    setFlash('success', 'Actualité créée avec succès');
                    header('Location: ' . BASE_URL . 'admin/dashboard.php?section=news');
                    exit;
                }

                $error = 'Erreur lors de la création de l\'actualité';
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
    <title>Ajouter une actualité - Administrator</title>
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
            <h2 class="mb-1">Ajouter une actualité</h2>
            <p class="text-muted mb-0">Publiez une nouvelle actualité sur le site.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php?section=news" class="btn btn-outline-secondary">Retour</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="mb-3">
                <label class="form-label">Titre *</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Extrait</label>
                <textarea name="excerpt" rows="3" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Contenu</label>
                <textarea name="content" rows="8" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/webp">
            </div>
            <div class="mb-3">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    <option value="draft">Brouillon</option>
                    <option value="published">Publié</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Créer l'actualité</button>
        </form>
    </div>
</div>
</body>
</html>
