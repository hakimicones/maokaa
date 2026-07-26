<?php
// admin/products/edit.php
// Modifier un produit existant

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../app/models/Product.php';
require_once __DIR__ . '/../../app/models/Category.php';
require_once __DIR__ . '/../../app/models/Brand.php';

requirePasswordChange();

$productModel = new Product($pdo);
$categoryModel = new Category($pdo);
$brandModel = new Brand($pdo);

$productId = (int)($_GET['id'] ?? 0);
$product = $productModel->getById($productId, false);

if (!$product) {
    http_response_code(404);
    echo '<h1>Produit introuvable</h1>';
    exit;
}

$categories = $categoryModel->getAll(false);
$brands = $brandModel->getAll(false);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'nom' => sanitize($_POST['nom'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'description_complete' => sanitize($_POST['description_complete'] ?? ''),
            'categorie_id' => (int)($_POST['categorie_id'] ?? 0),
            'marque_id' => !empty($_POST['marque_id']) ? (int)$_POST['marque_id'] : null,
            'caracteristiques_techniques' => sanitize($_POST['caracteristiques_techniques'] ?? ''),
            'active' => isset($_POST['active']) ? 1 : 0,
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'display_order' => (int)($_POST['display_order'] ?? 0)
        ];

        if (empty($data['nom'])) {
            $error = 'Le nom du produit est requis';
        } elseif (empty($data['categorie_id'])) {
            $error = 'La catégorie est requise';
        } else {
            $data['image'] = $product['image'] ?? null;
            $data['brochure_pdf'] = $product['brochure_pdf'] ?? null;
            $imageFromLibrary = trim($_POST['image_from_library'] ?? '');

            if (!empty($_FILES['image']['name'])) {
                $result = upload_image($_FILES['image'], __DIR__ . '/../../assets/images/products/', 'prod');
                if (isset($result['error'])) {
                    $error = $result['error'];
                } else {
                    $data['image'] = 'assets/images/products/' . $result['filename'];
                }
            } elseif (!empty($imageFromLibrary)) {
                $data['image'] = $imageFromLibrary;
            }

            $pdfFromLibrary = trim($_POST['brochure_from_library'] ?? '');
            if (!empty($_FILES['brochure_pdf']['name'])) {
                $result = upload_pdf($_FILES['brochure_pdf'], __DIR__ . '/../../assets/brochures/', 'broch');
                if (isset($result['error'])) {
                    $error = $result['error'];
                } else {
                    $data['brochure_pdf'] = 'assets/brochures/' . $result['filename'];
                }
            } elseif (!empty($pdfFromLibrary)) {
                $data['brochure_pdf'] = $pdfFromLibrary;
            }

            if (empty($error)) {
                if ($productModel->update($productId, $data)) {
                    setFlash('success', 'Produit mis à jour avec succès');
                    logAudit('update_product', $product['nom']);
                    header('Location: ' . BASE_URL . 'admin/dashboard.php?section=products');
                    exit;
                }

                $error = 'Erreur lors de la mise à jour du produit';
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
    <title>Modifier un Produit - Administrator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            margin-top: 30px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #435980;
            color: #435980;
        }
        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .btn-submit {
            background: linear-gradient(135deg, #435980 0%, #345075 100%);
            border: none;
            color: white;
            padding: 12px 40px;
            font-weight: 600;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="d-flex align-items-center mb-4">
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php?section=products" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <h3 class="mb-0">Modifier un Produit</h3>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data" data-validate="true">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                <div class="form-section-title">Informations Générales</div>

                <div class="mb-3">
                    <label for="nom" class="form-label">Nom du Produit *</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($product['nom']); ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="categorie_id" class="form-label">Catégorie *</label>
                        <select class="form-select" id="categorie_id" name="categorie_id" required>
                            <option value="">-- Sélectionner une catégorie --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ((int)$category['id'] === (int)($product['categorie_id'] ?? 0)) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="marque_id" class="form-label">Marque</label>
                        <select class="form-select" id="marque_id" name="marque_id">
                            <option value="">-- Aucune marque --</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo ((int)$brand['id'] === (int)($product['marque_id'] ?? 0)) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="display_order" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo (int)($product['display_order'] ?? 0); ?>">
                    </div>
                    <div class="col-md-4 mb-3 form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="active" name="active" <?php echo !empty($product['active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="active">Produit actif</label>
                    </div>
                    <div class="col-md-4 mb-3 form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="featured" name="featured" <?php echo !empty($product['featured']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="featured">Produit mis en avant</label>
                    </div>
                </div>

                <div class="form-section-title">Description</div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description Courte</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="description_complete" class="form-label">Description Complète</label>
                    <textarea class="form-control" id="description_complete" name="description_complete" rows="5"><?php echo htmlspecialchars($product['description_complete'] ?? ''); ?></textarea>
                </div>

                <div class="form-section-title">Caractéristiques Techniques</div>

                <div class="mb-3">
                    <label for="caracteristiques_techniques" class="form-label">Spécifications</label>
                    <textarea class="form-control" id="caracteristiques_techniques" name="caracteristiques_techniques" rows="5"><?php echo htmlspecialchars($product['caracteristiques_techniques'] ?? ''); ?></textarea>
                </div>

                <div class="form-section-title">Média</div>

                <div class="mb-3">
                    <label for="image" class="form-label">Image Principale</label>
                    <input type="hidden" name="image_from_library" id="image_from_library" value="">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-grow-1">
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, WebP. Taille max: 2 MB</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="btn-pick-image" style="white-space:nowrap;">
                            <i class="fas fa-images"></i> Bibliothèque
                        </button>
                    </div>
                    <div id="image-preview" class="mt-2"<?php if (empty($product['image'])): ?> style="display:none;"<?php endif; ?>>
                        <img src="<?php echo htmlspecialchars($product['image'] ?? ''); ?>" alt="Aperçu" style="max-width: 180px; border-radius: 8px;">
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="btn-remove-image" title="Supprimer l'image">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="brochure_pdf" class="form-label">Brochure PDF</label>
                    <input type="hidden" name="brochure_from_library" id="brochure_from_library" value="">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-grow-1">
                            <input type="file" class="form-control" id="brochure_pdf" name="brochure_pdf" accept=".pdf">
                            <small class="text-muted">Format: PDF. Taille max: 5 MB</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="btn-pick-pdf" style="white-space:nowrap;">
                            <i class="fas fa-file-pdf"></i> Bibliothèque
                        </button>
                    </div>
                    <div id="pdf-preview" class="mt-2"<?php if (empty($product['brochure_pdf'])): ?> style="display:none;"<?php endif; ?>>
                        <span class="badge bg-secondary">
                            <i class="fas fa-file-pdf"></i>
                            <a href="<?php echo htmlspecialchars($product['brochure_pdf'] ?? ''); ?>" target="_blank" id="pdf-name" class="text-white text-decoration-none">
                                <?php echo htmlspecialchars(basename($product['brochure_pdf'] ?? '')); ?>
                            </a>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="btn-remove-pdf" title="Supprimer">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-submit">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal sélecteur d'images -->
    <div class="modal fade" id="productImagePicker" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-images"></i> Choisir une image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-2" id="img-search" placeholder="Rechercher une image..." style="display:none;">
                    <div class="row g-3" id="product-image-grid">
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Chargement des images...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal sélecteur de brochures PDF -->
    <div class="modal fade" id="productPdfPicker" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-pdf"></i> Choisir une brochure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-2" id="pdf-search" placeholder="Rechercher une brochure..." style="display:none;">
                    <div class="list-group" id="product-pdf-list">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Chargement des brochures...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .product-img-item {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            padding: 0;
            background: #f8fafc;
            transition: border-color 0.15s, transform 0.1s, box-shadow 0.15s;
        }
        .product-img-item:hover {
            border-color: #6366f1;
            transform: scale(1.03);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }
        .product-img-item.selected {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.3);
        }
        .product-img-item .thumb-wrap {
            aspect-ratio: 1;
            width: 100%;
        }
        .product-img-item .thumb-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            pointer-events: none;
        }
        .product-img-item .thumb-label {
            font-size: 11px;
            text-align: center;
            padding: 3px 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            background: #fff;
            border-top: 1px solid #e2e8f0;
        }
        .product-img-item.selected .thumb-label {
            background: #f0fdf4;
        }
        .product-img-item-empty {
            grid-column: 1 / -1;
            text-align: center;
            color: #94a3b8;
            padding: 32px;
        }
        .pdf-item-hidden { display: none !important; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var pickerBtn   = document.getElementById('btn-pick-image');
        var hiddenInput = document.getElementById('image_from_library');
        var preview     = document.getElementById('image-preview');
        var previewImg  = preview.querySelector('img');
        var removeBtn   = document.getElementById('btn-remove-image');
        var fileInput   = document.getElementById('image');
        var modalEl     = document.getElementById('productImagePicker');
        var imageGrid   = document.getElementById('product-image-grid');
        var imgSearch   = document.getElementById('img-search');
        if (!modalEl || !pickerBtn) return;
        var modal       = new bootstrap.Modal(modalEl);
        var currentImg  = '<?php echo htmlspecialchars($product['image'] ?? '', ENT_QUOTES); ?>';

        var selectedUrl = currentImg || '';
        if (selectedUrl) {
            hiddenInput.value = selectedUrl;
        }

        function filterImages() {
            var q = imgSearch.value.toLowerCase();
            imageGrid.querySelectorAll('.col-4').forEach(function (col) {
                var fn = col.getAttribute('data-filename') || '';
                col.style.display = q === '' || fn.indexOf(q) !== -1 ? '' : 'none';
            });
        }

        pickerBtn.addEventListener('click', function () {
            imageGrid.innerHTML =
                '<div class="col-12 text-center text-muted py-4">' +
                    '<i class="fas fa-spinner fa-spin fa-2x"></i>' +
                    '<p class="mt-2">Chargement des images...</p>' +
                '</div>';
            imgSearch.value = '';
            imgSearch.style.display = 'none';
            modal.show();

            fetch('<?php echo BASE_URL; ?>includes/list_product_images.php')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var images = data.images || [];
                    if (images.length === 0) {
                        imageGrid.innerHTML = '<div class="product-img-item-empty">Aucune image dans assets/images/products/</div>';
                        return;
                    }
                    imgSearch.style.display = '';
                    imageGrid.innerHTML = '';
                    images.forEach(function (src) {
                        var col = document.createElement('div');
                        col.className = 'col-4 col-md-3';
                        var fn = src.replace(/^.*\//, '').toLowerCase();
                        col.setAttribute('data-filename', fn);
                        var btn = document.createElement('button');
                        btn.className = 'product-img-item' + (src === selectedUrl ? ' selected' : '');
                        btn.type = 'button';
                        btn.setAttribute('data-src', src);
                        btn.innerHTML =
                            '<div class="thumb-wrap"><img src="' + src.replace(/&/g,'&amp;').replace(/"/g,'&quot;') + '" alt="" loading="lazy"></div>' +
                            '<div class="thumb-label">' + fn.replace(/&/g,'&amp;') + '</div>';
                        btn.addEventListener('click', function () {
                            selectedUrl = this.getAttribute('data-src');
                            hiddenInput.value = selectedUrl;
                            previewImg.src = selectedUrl;
                            preview.style.display = 'block';
                            fileInput.value = '';
                            modalEl.querySelectorAll('.product-img-item').forEach(function (e) { e.classList.remove('selected'); });
                            this.classList.add('selected');
                            setTimeout(function () { modal.hide(); }, 200);
                        });
                        col.appendChild(btn);
                        imageGrid.appendChild(col);
                    });
                })
                .catch(function () {
                    imageGrid.innerHTML = '<div class="product-img-item-empty">Erreur de chargement des images</div>';
                });
        });

        imgSearch.addEventListener('input', filterImages);

        removeBtn.addEventListener('click', function () {
            selectedUrl = '';
            hiddenInput.value = '';
            preview.style.display = 'none';
            previewImg.src = '';
        });

        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                selectedUrl = '';
                hiddenInput.value = '';
                preview.style.display = 'none';
                previewImg.src = '';
            }
        });

        // ── Sélecteur de brochure PDF ──
        var pdfPickerBtn   = document.getElementById('btn-pick-pdf');
        var pdfHiddenInput = document.getElementById('brochure_from_library');
        var pdfPreview     = document.getElementById('pdf-preview');
        var pdfNameSpan    = document.getElementById('pdf-name');
        var pdfRemoveBtn   = document.getElementById('btn-remove-pdf');
        var pdfFileInput   = document.getElementById('brochure_pdf');
        var pdfModalEl     = document.getElementById('productPdfPicker');
        var pdfListEl      = document.getElementById('product-pdf-list');
        var pdfSearch      = document.getElementById('pdf-search');
        if (!pdfModalEl || !pdfPickerBtn || !pdfSearch) return;
        var pdfModal       = new bootstrap.Modal(pdfModalEl);
        var pdfSelectedUrl = '<?php echo isset($product['brochure_pdf']) ? addslashes($product['brochure_pdf']) : ''; ?>';
        if (pdfSelectedUrl) { pdfHiddenInput.value = pdfSelectedUrl; }

        function filterPdfs() {
            var q = pdfSearch.value.toLowerCase().trim();
            pdfListEl.querySelectorAll('.list-group-item').forEach(function (item) {
                var fn = item.getAttribute('data-filename') || '';
                item.classList.toggle('pdf-item-hidden', q.length > 0 && fn.indexOf(q) === -1);
            });
        }

        pdfPickerBtn.addEventListener('click', function () {
            pdfListEl.innerHTML =
                '<div class="text-center text-muted py-4">' +
                    '<i class="fas fa-spinner fa-spin fa-2x"></i>' +
                    '<p class="mt-2">Chargement des brochures...</p>' +
                '</div>';
            pdfSearch.value = '';
            pdfSearch.style.display = 'none';
            pdfModal.show();

            fetch('<?php echo BASE_URL; ?>includes/list_brochures.php')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var files = data.files || [];
                    if (files.length === 0) {
                        pdfListEl.innerHTML = '<div class="text-center text-muted py-4">Aucune brochure dans assets/brochures/</div>';
                        return;
                    }
                    pdfSearch.style.display = '';
                    pdfListEl.innerHTML = '';
                    files.forEach(function (f) {
                        var item = document.createElement('button');
                        item.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2' +
                            (f.url === pdfSelectedUrl ? ' active' : '');
                        item.type = 'button';
                        item.setAttribute('data-url', f.url);
                        item.setAttribute('data-filename', f.name.toLowerCase());
                        item.innerHTML = '<i class="fas fa-file-pdf text-danger"></i> ' + f.name.replace(/&/g,'&amp;');
                        item.addEventListener('click', function () {
                            pdfSelectedUrl = this.getAttribute('data-url');
                            pdfHiddenInput.value = pdfSelectedUrl;
                            pdfNameSpan.textContent = f.name;
                            pdfNameSpan.href = pdfSelectedUrl;
                            pdfPreview.style.display = 'block';
                            pdfFileInput.value = '';
                            pdfListEl.querySelectorAll('.list-group-item').forEach(function (e) { e.classList.remove('active'); });
                            this.classList.add('active');
                            setTimeout(function () { pdfModal.hide(); }, 200);
                        });
                        pdfListEl.appendChild(item);
                    });
                })
                .catch(function () {
                    pdfListEl.innerHTML = '<div class="text-center text-muted py-4">Erreur de chargement des brochures</div>';
                });
        });

        pdfSearch.addEventListener('input', filterPdfs);

        pdfRemoveBtn.addEventListener('click', function () {
            pdfSelectedUrl = '';
            pdfHiddenInput.value = '';
            pdfPreview.style.display = 'none';
            pdfNameSpan.textContent = '';
            pdfNameSpan.href = '';
        });

        pdfFileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                pdfSelectedUrl = '';
                pdfHiddenInput.value = '';
                pdfPreview.style.display = 'none';
                pdfNameSpan.textContent = '';
                pdfNameSpan.href = '';
            }
        });
    });
    </script>

</body>
</html>
