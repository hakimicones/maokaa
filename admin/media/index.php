<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';

requirePasswordChange();

$csrfToken = generateCSRFToken();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'upload':
                $result = upload_image($_FILES['file'] ?? [], __DIR__ . '/../../assets/images/', 'img', [
                    'image/jpeg'    => 'jpg',
                    'image/png'     => 'png',
                    'image/gif'     => 'gif',
                    'image/webp'    => 'webp',
                ]);
                if (isset($result['error'])) {
                    $error = $result['error'];
                } else {
                    setFlash('success', 'Image téléchargée : ' . $result['filename']);
                    logAudit('upload_media', 'Fichier: ' . $result['filename']);
                    header('Location: ' . BASE_URL . 'admin/media/index.php');
                    exit;
                }
                break;

            case 'delete':
                $file = $_POST['file'] ?? '';
                $fullPath = realpath(__DIR__ . '/../../' . $file);
                $baseDir  = realpath(__DIR__ . '/../../assets/images/');

                if ($fullPath && strpos($fullPath, $baseDir) === 0 && is_file($fullPath)) {
                    if (unlink($fullPath)) {
                        setFlash('success', 'Image supprimée : ' . htmlspecialchars(basename($file)));
                        logAudit('delete_media', 'Fichier: ' . $file);
                        header('Location: ' . BASE_URL . 'admin/media/index.php');
                        exit;
                    }
                    $error = 'Impossible de supprimer le fichier';
                } else {
                    $error = 'Fichier invalide';
                }
                break;

            case 'rename':
                $oldFile = $_POST['old_file'] ?? '';
                $newName = $_POST['new_name'] ?? '';

                $newName = trim(preg_replace('/[^\w.\-]/', '_', $newName));
                if ($newName === '' || $newName === '.') {
                    $error = 'Nom de fichier invalide';
                    break;
                }
                $oldPath = realpath(__DIR__ . '/../../' . $oldFile);
                $baseDir = realpath(__DIR__ . '/../../assets/images/');

                if ($oldPath && strpos($oldPath, $baseDir) === 0 && is_file($oldPath)) {
                    $dir     = dirname($oldPath);
                    $newPath = $dir . '/' . $newName;

                    if (file_exists($newPath)) {
                        $error = 'Un fichier avec ce nom existe déjà';
                    } elseif (rename($oldPath, $newPath)) {
                        setFlash('success', 'Image renommée : ' . htmlspecialchars(basename($oldFile)) . ' → ' . htmlspecialchars($newName));
                        logAudit('rename_media', 'De: ' . $oldFile . ' → ' . $newName);
                        header('Location: ' . BASE_URL . 'admin/media/index.php');
                        exit;
                    } else {
                        $error = 'Impossible de renommer le fichier';
                    }
                } else {
                    $error = 'Fichier invalide';
                }
                break;
        }
    }
}

$projectRoot = realpath(__DIR__ . '/../../');
$imagesDir   = $projectRoot . '/assets/images';
$images      = [];

if (is_dir($imagesDir)) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($imagesDir, FilesystemIterator::SKIP_DOTS));
    foreach ($rii as $file) {
        if ($file->isFile() && preg_match('/\.(jpg|jpeg|png|gif|svg|webp)$/i', $file->getFilename())) {
            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($projectRoot) + 1));
            $subDir       = str_replace('\\', '/', substr(dirname($file->getPathname()), strlen($imagesDir) + 1)) ?: '/';
            $images[] = [
                'path'     => $relativePath,
                'url'      => BASE_URL . $relativePath,
                'name'     => $file->getFilename(),
                'size'     => $file->getSize(),
                'modified' => $file->getMTime(),
                'dir'      => $subDir,
            ];
        }
    }
    usort($images, fn($a, $b) => $b['modified'] - $a['modified']);
}

function formatSize(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

$flash = function_exists('getFlash') ? getFlash() : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médias - Administrator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }
        .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.25rem; }
        .media-card {
            background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.07);
            overflow: hidden; transition: box-shadow .2s;
        }
        .media-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); }
        .media-card .thumb {
            width: 100%; aspect-ratio: 1; object-fit: cover;
            background: #f0f0f0; display: block;
        }
        .media-card .thumb.svg { padding: 1.5rem; object-fit: contain; }
        .media-card .body { padding: .75rem; }
        .media-card .filename {
            font-size: .85rem; font-weight: 500; color: #1a1a2e;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .media-card .meta { font-size: .75rem; color: #888; margin-top: .15rem; }
        .media-card .actions { display: flex; gap: .35rem; margin-top: .5rem; flex-wrap: wrap; }
        .media-card .actions .btn { font-size: .75rem; padding: .15rem .5rem; }
        .dir-badge {
            display: inline-block; background: #e8f4fd; color: #1565C0;
            border-radius: 4px; padding: 0 .35rem; font-size: .7rem; max-width: 100%;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .upload-card {
            background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.07);
            padding: 1.5rem; margin-bottom: 1.5rem;
        }
        .modal-rename-input { font-size: .9rem; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="mb-1"><i class="fas fa-photo-video me-2 text-primary"></i>Médias</h2>
                <p class="text-muted mb-0"><?php echo count($images); ?> fichier<?php echo count($images) > 1 ? 's' : ''; ?> dans <code>assets/images/</code></p>
            </div>
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Dashboard
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

        <div class="upload-card">
            <form method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="upload">
                <div class="col-md-8">
                    <label for="media-file" class="form-label small mb-1">Ajouter une image</label>
                    <input type="file" name="file" id="media-file" class="form-control form-control-sm"
                           accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" required>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-upload"></i> Télécharger</button>
                </div>
            </form>
        </div>

        <?php if (empty($images)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-images fa-3x mb-3"></i>
                <p>Aucune image trouvée. Téléchargez vos premières images ci-dessus.</p>
            </div>
        <?php else: ?>
            <div class="media-grid">
                <?php foreach ($images as $img): ?>
                <div class="media-card">
                    <img src="<?php echo htmlspecialchars($img['url']); ?>"
                         alt="<?php echo htmlspecialchars($img['name']); ?>"
                         class="thumb<?php echo preg_match('/\.svg$/i', $img['name']) ? ' svg' : ''; ?>"
                         loading="lazy"
                         onerror="this.style.display='none'">
                    <div class="body">
                        <div class="filename" title="<?php echo htmlspecialchars($img['name']); ?>">
                            <?php echo htmlspecialchars($img['name']); ?>
                        </div>
                        <div class="meta d-flex justify-content-between">
                            <span><?php echo formatSize($img['size']); ?></span>
                            <span class="dir-badge"><?php echo htmlspecialchars($img['dir']); ?></span>
                        </div>
                        <div class="actions">
                            <button type="button" class="btn btn-outline-primary btn-action"
                                    data-bs-toggle="modal" data-bs-target="#renameModal"
                                    data-path="<?php echo htmlspecialchars($img['path']); ?>"
                                    data-name="<?php echo htmlspecialchars($img['name']); ?>"
                                    title="Renommer">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form method="POST" onsubmit="return confirm('Supprimer <?php echo htmlspecialchars(addslashes($img['name'])); ?> ?');">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="file" value="<?php echo htmlspecialchars($img['path']); ?>">
                                <button type="submit" class="btn btn-outline-danger btn-action" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <a href="<?php echo htmlspecialchars($img['url']); ?>" target="_blank"
                               class="btn btn-outline-secondary btn-action" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="renameModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <form method="POST" class="modal-content">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="rename">
                <input type="hidden" name="old_file" id="rename-old-path" value="">
                <div class="modal-header">
                    <h6 class="modal-title">Renommer le fichier</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="rename-new-name" class="form-label small">Nouveau nom</label>
                    <input type="text" name="new_name" id="rename-new-name" class="form-control form-control-sm modal-rename-input" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Renommer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('renameModal').addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        document.getElementById('rename-old-path').value = btn.getAttribute('data-path');
        document.getElementById('rename-new-name').value = btn.getAttribute('data-name');
    });
    </script>
</body>
</html>
