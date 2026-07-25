<?php
// admin/sliders/manage.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/models/Slider.php';

requirePasswordChange();

$slider_id   = (int)($_GET['slider_id'] ?? 0);
$sliderModel = new Slider($pdo);
$error       = '';

if ($slider_id <= 0) {
    setFlash('error', 'Identifiant de slider invalide.');
    header('Location: ' . BASE_URL . 'admin/sliders/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $id = (int)($_POST['slide_id'] ?? 0);
        if ($id > 0) {
            if ($sliderModel->delete($id)) {
                setFlash('success', 'Slide supprimé avec succès');
                header('Location: ' . BASE_URL . 'admin/sliders/manage.php?slider_id=' . $slider_id . $retParam);
                exit;
            }
            $error = 'Erreur lors de la suppression du slide';
        } else {
            $error = 'Identifiant de slide invalide';
        }
    }
}

$csrfToken = generateCSRFToken();
$retUrl    = $_GET['return_url'] ?? '';
$retParam  = (!empty($retUrl) && is_safe_url($retUrl)) ? '&return_url=' . urlencode($retUrl) : '';
$slides    = $sliderModel->getAllBySlider($slider_id);

$flash = function_exists('getFlash') ? getFlash() : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slider #<?php echo $slider_id; ?> — Gestion des slides - VEP Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }
        .form-card { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,.06); }
        .color-swatch { display: inline-block; width: 24px; height: 24px; border-radius: 4px; border: 1px solid rgba(0,0,0,.15); vertical-align: middle; margin-right: 6px; }
        .badge-active { background: #d4edda; color: #155724; border-radius: 20px; padding: 2px 10px; font-size: 0.8rem; }
        .badge-inactive { background: #f8d7da; color: #721c24; border-radius: 20px; padding: 2px 10px; font-size: 0.8rem; }
        .shortcode-box { background: #f0f4f8; border-radius: 8px; padding: 12px 16px; font-family: monospace; font-size: 0.9rem; color: #1a1a2e; border: 1px solid #dce4ef; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Slider #<?php echo $slider_id; ?> — Gestion des slides</h2>
            <p class="text-muted mb-0"><?php echo count($slides); ?> slide<?php echo count($slides) > 1 ? 's' : ''; ?> dans ce carousel.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>admin/sliders/create.php?slider_id=<?php echo $slider_id . $retParam; ?>"
               class="btn btn-primary">+ Ajouter un slide</a>
            <a href="<?php echo return_url(BASE_URL . 'admin/sliders/index.php'); ?>" class="btn btn-outline-secondary">Retour</a>
        </div>
    </div>

    <div class="shortcode-box mb-4">
        Shortcode a utiliser dans vos pages : <strong>[carousel slider_id="<?php echo $slider_id; ?>"]</strong>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['type'] ?? 'info'); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($flash['message'] ?? ''); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <?php if (empty($slides)): ?>
            <div class="text-center py-4">
                <p class="text-muted mb-3">Aucun slide dans ce carousel.</p>
                <a href="<?php echo BASE_URL; ?>admin/sliders/create.php?slider_id=<?php echo $slider_id; ?>"
                   class="btn btn-primary">Ajouter le premier slide</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ordre</th>
                            <th>Label</th>
                            <th>Sous-titre</th>
                            <th>Image</th>
                            <th>Couleur (bg)</th>
                            <th>Actif</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slides as $slide): ?>
                            <tr>
                                <td><?php echo (int)$slide['sort_order']; ?></td>
                                <td><?php echo htmlspecialchars($slide['label']); ?></td>
                                <td><?php echo htmlspecialchars($slide['subtitle'] ?? ''); ?>&nbsp;</td>
                                <td>
                                    <?php if (!empty($slide['image'])): ?>
                                        <img src="<?php echo BASE_URL . htmlspecialchars($slide['image']); ?>" alt=""
                                             style="max-height:50px;max-width:80px;border-radius:4px;object-fit:cover;">
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="color-swatch" style="background:<?php echo htmlspecialchars($slide['bg']); ?>;"></span>
                                    <code><?php echo htmlspecialchars($slide['bg']); ?></code>
                                </td>
                                <td>
                                    <?php if ($slide['active']): ?>
                                        <span class="badge-active">Actif</span>
                                    <?php else: ?>
                                        <span class="badge-inactive">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?php echo BASE_URL; ?>admin/sliders/edit.php?id=<?php echo (int)$slide['id']; ?>"
                                           class="btn btn-outline-primary btn-sm">Modifier</a>
                                        <form method="POST"
                                              onsubmit="return confirm('Supprimer ce slide ?');"
                                              class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                            <input type="hidden" name="slide_id" value="<?php echo (int)$slide['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
