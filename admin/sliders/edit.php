<?php
// admin/sliders/edit.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../app/models/Slider.php';

requirePasswordChange();

$sliderModel = new Slider($pdo);
$error       = '';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('error', 'Identifiant de slide invalide.');
    header('Location: ' . BASE_URL . 'admin/sliders/index.php');
    exit;
}

$slide = $sliderModel->getById($id);
if (!$slide) {
    setFlash('error', 'Slide introuvable.');
    header('Location: ' . BASE_URL . 'admin/sliders/index.php');
    exit;
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
                $image = $slide['image'];
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

                    if ($sliderModel->update($id, $data)) {
                        setFlash('success', 'Slide mis à jour avec succès');
                        header('Location: ' . BASE_URL . 'admin/sliders/manage.php?slider_id=' . $slider_id . $retParam);
                        exit;
                    }

                    $error = 'Erreur lors de la mise à jour du slide';
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
    <title>Modifier le slide #<?php echo $id; ?> - VEP Admin</title>
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
            <h2 class="mb-1">Modifier le slide #<?php echo $id; ?></h2>
            <p class="text-muted mb-0">Modifier les informations de ce slide.</p>
        </div>
        <a href="<?php echo return_url(BASE_URL . 'admin/sliders/manage.php?slider_id=' . (int)$slide['slider_id']); ?>"
           class="btn btn-outline-secondary">Retour</a>
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
                       value="<?php echo (int)$slide['slider_id']; ?>" required>
                <div class="form-text">Modifier pour déplacer ce slide vers un autre carousel.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Label *</label>
                <input type="text" name="label" class="form-control"
                       value="<?php echo htmlspecialchars($slide['label']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Sous-titre (optionnel)</label>
                <textarea name="subtitle" class="form-control" rows="2"
                          placeholder="Texte descriptif affiché sous le titre"><?php echo htmlspecialchars($slide['subtitle'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Position du texte</label>
                <select name="text_position" class="form-select">
                    <?php $pos = $slide['text_position'] ?? 'center'; ?>
                    <option value="top-left" <?php echo $pos === 'top-left' ? 'selected' : ''; ?>>En haut à gauche</option>
                    <option value="top-center" <?php echo $pos === 'top-center' ? 'selected' : ''; ?>>En haut centré</option>
                    <option value="top-right" <?php echo $pos === 'top-right' ? 'selected' : ''; ?>>En haut à droite</option>
                    <option value="center-left" <?php echo $pos === 'center-left' ? 'selected' : ''; ?>>Milieu gauche</option>
                    <option value="center" <?php echo $pos === 'center' ? 'selected' : ''; ?>>Centré</option>
                    <option value="center-right" <?php echo $pos === 'center-right' ? 'selected' : ''; ?>>Milieu droite</option>
                    <option value="bottom-left" <?php echo $pos === 'bottom-left' ? 'selected' : ''; ?>>En bas à gauche</option>
                    <option value="bottom-center" <?php echo $pos === 'bottom-center' ? 'selected' : ''; ?>>En bas centré</option>
                    <option value="bottom-right" <?php echo $pos === 'bottom-right' ? 'selected' : ''; ?>>En bas à droite</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Image de fond</label>
                <?php if (!empty($slide['image'])): ?>
                    <div class="mb-2">
                        <img src="<?php echo BASE_URL . htmlspecialchars($slide['image']); ?>" alt=""
                             style="max-height:120px;border-radius:6px;border:1px solid #dee2e6;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">Laissez vide pour conserver l'image actuelle. Formats : JPEG, PNG, WebP (max 2 Mo).</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Couleur de fond (fallback si pas d'image)</label>
                <div class="d-flex align-items-center gap-3">
                    <input type="color" name="bg" class="form-control form-control-color" id="bgColor"
                           value="<?php echo htmlspecialchars($slide['bg']); ?>" title="Choisir une couleur">
                    <span class="text-muted" id="bgColorHex"><?php echo htmlspecialchars($slide['bg']); ?></span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Ordre d'affichage</label>
                <input type="number" name="sort_order" class="form-control"
                       value="<?php echo (int)$slide['sort_order']; ?>" min="0">
                <div class="form-text">Les slides sont affichées du plus petit au plus grand sort_order.</div>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" name="active" class="form-check-input" id="activeCheck"
                           <?php echo $slide['active'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="activeCheck">Actif</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="<?php echo return_url(BASE_URL . 'admin/sliders/manage.php?slider_id=' . (int)$slide['slider_id']); ?>"
                   class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const bgColor   = document.getElementById('bgColor');
    const bgHexSpan = document.getElementById('bgColorHex');
    bgColor.addEventListener('input', function () {
        bgHexSpan.textContent = this.value;
    });
</script>
</body>
</html>
