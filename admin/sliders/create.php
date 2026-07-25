<?php
// admin/sliders/create.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../app/models/Slider.php';

requirePasswordChange();

$sliderModel = new Slider($pdo);
$error       = '';

// slider_id passé en GET (ajouter un slide à un carousel existant)
// ou nouveau slider (slider_id = getNextSliderId())
$prefill_slider_id = (int)($_GET['slider_id'] ?? 0);
if ($prefill_slider_id <= 0) {
    $prefill_slider_id = $sliderModel->getNextSliderId();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $slider_id     = (int)($_POST['slider_id'] ?? 1);
        $label         = trim($_POST['label'] ?? '');
        $subtitle      = trim($_POST['subtitle'] ?? '');
        $text_position = trim($_POST['text_position'] ?? 'center');
        $bg            = trim($_POST['bg'] ?? '#dde4ee');
        $sort_order    = (int)($_POST['sort_order'] ?? 0);
        $active        = isset($_POST['active']) ? 1 : 0;

        if (empty($label)) {
            $error = 'Le label du slide est requis';
        } elseif ($slider_id <= 0) {
            $error = 'Identifiant de slider invalide';
        } elseif (!in_array($text_position, ['top-left','top-center','top-right','center-left','center','center-right','bottom-left','bottom-center','bottom-right'])) {
            $error = 'Position de texte invalide';
        } else {
            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $upload = upload_image($_FILES['image'], UPLOAD_DIR, 'slide');
                if (isset($upload['error'])) {
                    $error = $upload['error'];
                } else {
                    $image = 'assets/images/' . $upload['filename'];
                }
            }

            if (empty($error)) {
                $data = [
                    'slider_id'     => $slider_id,
                    'label'         => $label,
                    'subtitle'      => $subtitle,
                    'bg'            => $bg,
                    'image'         => $image,
                    'text_position' => $text_position,
                    'sort_order'    => $sort_order,
                    'active'        => $active,
                ];

                if ($sliderModel->create($data)) {
                    setFlash('success', 'Slide créé avec succès');
                    header('Location: ' . BASE_URL . 'admin/sliders/manage.php?slider_id=' . $slider_id . $retParam);
                    exit;
                }

                $error = 'Erreur lors de la création du slide';
            }
        }
    }
}

$retUrl    = $_GET['return_url'] ?? '';
$retParam  = (!empty($retUrl) && is_safe_url($retUrl)) ? '&return_url=' . urlencode($retUrl) : '';
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un slide - VEP Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }
        .form-card { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,.06); }
        .color-preview { display: inline-block; width: 32px; height: 32px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15); vertical-align: middle; margin-left: 8px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Ajouter un slide</h2>
            <p class="text-muted mb-0">Créez un nouveau slide dans un carousel.</p>
        </div>
        <a href="<?php echo return_url(BASE_URL . 'admin/sliders/index.php'); ?>" class="btn btn-outline-secondary">Retour</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Slider ID (numéro du carousel) *</label>
                <input type="number" name="slider_id" class="form-control" min="1"
                       value="<?php echo $prefill_slider_id; ?>" required>
                <div class="form-text">Utilisez le même slider_id pour regrouper plusieurs slides dans un même carousel.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Label *</label>
                <input type="text" name="label" class="form-control"
                       placeholder="Ex: Bienvenue sur notre site" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Sous-titre (optionnel)</label>
                <textarea name="subtitle" class="form-control" rows="2"
                          placeholder="Texte descriptif affiché sous le titre"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Position du texte</label>
                <select name="text_position" class="form-select">
                    <option value="top-left">En haut à gauche</option>
                    <option value="top-center">En haut centré</option>
                    <option value="top-right">En haut à droite</option>
                    <option value="center-left">Milieu gauche</option>
                    <option value="center" selected>Centré</option>
                    <option value="center-right">Milieu droite</option>
                    <option value="bottom-left">En bas à gauche</option>
                    <option value="bottom-center">En bas centré</option>
                    <option value="bottom-right">En bas à droite</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Image de fond</label>
                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">Laissez vide pour utiliser la couleur de fond. Formats acceptés : JPEG, PNG, WebP (max 2 Mo).</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Couleur de fond (fallback si pas d'image)</label>
                <div class="d-flex align-items-center gap-3">
                    <input type="color" name="bg" class="form-control form-control-color" id="bgColor"
                           value="#dde4ee" title="Choisir une couleur">
                    <span class="text-muted" id="bgColorHex">#dde4ee</span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Ordre d'affichage</label>
                <input type="number" name="sort_order" class="form-control" value="0" min="0">
                <div class="form-text">Les slides sont affichées du plus petit au plus grand sort_order.</div>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" name="active" class="form-check-input" id="activeCheck" checked>
                    <label class="form-check-label" for="activeCheck">Actif</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Créer le slide</button>
                <a href="<?php echo return_url(BASE_URL . 'admin/sliders/index.php'); ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const bgColor  = document.getElementById('bgColor');
    const bgHexSpan = document.getElementById('bgColorHex');
    bgColor.addEventListener('input', function () {
        bgHexSpan.textContent = this.value;
    });
</script>
</body>
</html>
