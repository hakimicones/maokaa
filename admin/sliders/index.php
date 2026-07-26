<?php
// admin/sliders/index.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/models/Slider.php';

requirePasswordChange();

$sliderModel = new Slider($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $slider_id = (int)($_POST['slider_id'] ?? 0);
        if ($slider_id > 0) {
            if ($sliderModel->deleteSlider($slider_id)) {
                setFlash('success', 'Slider #' . $slider_id . ' supprimé avec succès');
                header('Location: ' . BASE_URL . 'admin/sliders/index.php');
                exit;
            }
            $error = 'Erreur lors de la suppression du slider';
        } else {
            $error = 'Identifiant de slider invalide';
        }
    }
}

$csrfToken = generateCSRFToken();
$groups    = $sliderModel->getAllGroups();
$retUrl    = $_GET['return_url'] ?? '';
$retParam  = (!empty($retUrl) && is_safe_url($retUrl)) ? '&return_url=' . urlencode($retUrl) : '';

$flash = function_exists('getFlash') ? getFlash() : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sliders - Administrator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }
        .form-card { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,.06); }
        .slider-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 5px 20px rgba(0,0,0,.06); margin-bottom: 20px; }
        .slider-card .slider-title { font-size: 1.1rem; font-weight: 600; color: #1a1a2e; }
        .slider-card .badge-count { background: #e3f2fd; color: #1565C0; border-radius: 20px; padding: 2px 12px; font-size: 0.85rem; font-weight: 500; }
        .label-preview { display: inline-block; background: #f0f4f8; border-radius: 6px; padding: 3px 10px; margin: 2px; font-size: 0.82rem; color: #444; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Gestion des Sliders</h2>
            <p class="text-muted mb-0">Gérez vos carousels de slides.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/sliders/create.php<?php echo $retParam; ?>" class="btn btn-primary">
            + Nouveau Slider
        </a>
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

    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline-secondary mb-3">&larr; Tableau de bord</a>
    <?php if (!empty($_GET['return_url']) && is_safe_url($_GET['return_url'])): ?>
        <a href="<?php echo htmlspecialchars($_GET['return_url']); ?>" class="btn btn-outline-secondary mb-3">&larr; Retour au site</a>
    <?php endif; ?>

    <?php if (empty($groups)): ?>
        <div class="form-card text-center py-5">
            <p class="text-muted mb-3">Aucun slider trouvé.</p>
            <a href="<?php echo BASE_URL; ?>admin/sliders/create.php<?php echo $retParam; ?>" class="btn btn-primary">Créer le premier slider</a>
        </div>
    <?php else: ?>
        <?php foreach ($groups as $group): ?>
            <?php
            $sid    = (int)$group['slider_id'];
            $count  = (int)$group['slide_count'];
            $slides = $sliderModel->getAllBySlider($sid);
            ?>
            <div class="slider-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="slider-title">Slider #<?php echo $sid; ?></span>
                            <span class="badge-count"><?php echo $count; ?> slide<?php echo $count > 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="mb-2">
                            <?php foreach ($slides as $slide): ?>
                                <span class="label-preview"><?php echo htmlspecialchars($slide['label']); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted">Shortcode : <code>[carousel slider_id="<?php echo $sid; ?>"]</code></small>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="<?php echo BASE_URL; ?>admin/sliders/manage.php?slider_id=<?php echo $sid . $retParam; ?>"
                           class="btn btn-outline-primary btn-sm">
                            Gérer les slides
                        </a>
                        <form method="POST" onsubmit="return confirm('Supprimer le slider #<?php echo $sid; ?> et toutes ses slides ?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            <input type="hidden" name="slider_id" value="<?php echo $sid; ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer le slider</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
