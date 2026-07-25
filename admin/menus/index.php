<?php
// admin/menus/index.php — Gestion des menus

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/models/Menu.php';

requirePasswordChange();
requireRole('admin');

$menuModel = new Menu($pdo);
$menus = $menuModel->getAllMenus();
$selectedMenu = null;
$menuItems = [];

// Sélectionner le menu à éditer
$menuId = (int)($_GET['menu_id'] ?? 0);
if ($menuId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
    $stmt->execute([$menuId]);
    $selectedMenu = $stmt->fetch() ?: ($menus[0] ?? null);
} elseif (!empty($menus)) {
    $selectedMenu = $menus[0];
}

if ($selectedMenu) {
    $menuItems = $menuModel->getItemsWithChildren($selectedMenu['id'], false);
}

// Traiter les actions POST
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add_item':
                $addParams = json_encode(['icon' => isset($_POST['param_icon']) ? 1 : 0]);
                if ($menuModel->addItem(
                    $selectedMenu['id'],
                    trim($_POST['title'] ?? ''),
                    trim($_POST['url'] ?? ''),
                    (int)($_POST['position'] ?? 0),
                    trim($_POST['icon'] ?? '') ?: null,
                    !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                    $addParams
                )) {
                    $message = 'Élément ajouté avec succès';
                    $messageType = 'success';
                    $menuItems = $menuModel->getItemsWithChildren($selectedMenu['id'], false);
                } else {
                    $message = 'Erreur lors de l\'ajout de l\'élément';
                    $messageType = 'danger';
                }
                break;

            case 'update_item':
                $updParams = json_encode(['icon' => isset($_POST['param_icon']) ? 1 : 0]);
                if ($menuModel->updateItem((int)$_POST['id'], [
                    'title' => trim($_POST['title'] ?? ''),
                    'url' => trim($_POST['url'] ?? ''),
                    'icon' => trim($_POST['icon'] ?? '') ?: null,
                    'position' => (int)($_POST['position'] ?? 0),
                    'active' => isset($_POST['active']) ? 1 : 0,
                    'params' => $updParams
                ])) {
                    $message = 'Élément mis à jour avec succès';
                    $messageType = 'success';
                    $menuItems = $menuModel->getItemsWithChildren($selectedMenu['id'], false);
                } else {
                    $message = 'Erreur lors de la mise à jour';
                    $messageType = 'danger';
                }
                break;

            case 'delete_item':
                if ($menuModel->deleteItem((int)$_POST['id'])) {
                    $message = 'Élément supprimé avec succès';
                    $messageType = 'success';
                    $menuItems = $menuModel->getItemsWithChildren($selectedMenu['id'], false);
                } else {
                    $message = 'Erreur lors de la suppression';
                    $messageType = 'danger';
                }
                break;
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
    <title>Gestion des menus - Admin VEP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }
        .card { border: 0; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .menu-item { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; }
        .menu-item.child { margin-left: 30px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Gestion des menus</h2>
            <p class="text-muted mb-0">Organisez les éléments de navigation du site</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline-secondary">Retour</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Liste des menus -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-light fw-bold">Menus</div>
                <div class="list-group list-group-flush">
                    <?php foreach ($menus as $menu): ?>
                        <a href="?menu_id=<?php echo $menu['id']; ?>"
                           class="list-group-item list-group-item-action <?php echo $selectedMenu && $selectedMenu['id'] === $menu['id'] ? 'active' : ''; ?>">
                            <strong><?php echo htmlspecialchars($menu['label']); ?></strong>
                            <small class="d-block text-muted"><?php echo htmlspecialchars($menu['name']); ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Édition du menu sélectionné -->
        <div class="col-md-9">
            <?php if ($selectedMenu): ?>
                <div class="card mb-4">
                    <div class="card-header bg-light fw-bold"><?php echo htmlspecialchars($selectedMenu['label']); ?></div>
                    <div class="card-body">
                        <h5 class="mb-3">Ajouter un nouvel élément</h5>
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="action" value="add_item">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                            <div class="col-md-4">
                                <label class="form-label">Titre *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">URL *</label>
                                <input type="text" name="url" class="form-control" placeholder="/about" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Icône <small class="text-muted">(classe FA)</small></label>
                                <input type="text" name="icon" class="form-control" placeholder="fas fa-home">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Position</label>
                                <input type="number" name="position" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sous-élément de</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">— Aucun (niveau racine) —</option>
                                    <?php foreach ($menuItems as $topItem): ?>
                                        <option value="<?php echo $topItem['id']; ?>">
                                            <?php echo htmlspecialchars($topItem['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" name="param_icon" class="form-check-input" id="add_param_icon" checked>
                                    <label class="form-check-label" for="add_param_icon">Afficher l'icône</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-light fw-bold">Éléments du menu</div>
                    <div class="card-body">
                        <?php if (!empty($menuItems)): ?>
                            <?php foreach ($menuItems as $item):
                                $itemParams = $item['params'] ? json_decode($item['params'], true) : [];
                                $itemShowIcon = !isset($itemParams['icon']) || $itemParams['icon'];
                            ?>
                                <div class="menu-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <?php if (!empty($item['icon'])): ?>
                                                <i class="<?php echo htmlspecialchars($item['icon']); ?> me-2 <?php echo $itemShowIcon ? 'text-primary' : 'text-muted'; ?>"></i>
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($item['url']); ?></small>
                                            <?php if ($item['active']): ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactif</span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['icon'])): ?>
                                                <span class="badge <?php echo $itemShowIcon ? 'bg-info' : 'bg-light text-dark border'; ?>">
                                                    <i class="fas fa-eye<?php echo $itemShowIcon ? '' : '-slash'; ?> me-1"></i>Icône <?php echo $itemShowIcon ? 'visible' : 'masquée'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                                                    data-bs-target="#edit-item-<?php echo $item['id']; ?>">Éditer</button>
                                            <form method="POST" class="m-0">
                                                <input type="hidden" name="action" value="delete_item">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Supprimer cet élément et ses sous-éléments ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Formulaire d'édition (masqué) -->
                                    <div class="collapse mt-3" id="edit-item-<?php echo $item['id']; ?>">
                                        <form method="POST" class="row g-2 align-items-end">
                                            <input type="hidden" name="action" value="update_item">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                                            <div class="col-md-3">
                                                <label class="form-label small mb-1">Titre</label>
                                                <input type="text" name="title" class="form-control form-control-sm"
                                                       value="<?php echo htmlspecialchars($item['title']); ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small mb-1">URL</label>
                                                <input type="text" name="url" class="form-control form-control-sm"
                                                       value="<?php echo htmlspecialchars($item['url']); ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small mb-1">Icône <small class="text-muted">(FA)</small></label>
                                                <input type="text" name="icon" class="form-control form-control-sm"
                                                       value="<?php echo htmlspecialchars($item['icon'] ?? ''); ?>" placeholder="fas fa-home">
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label small mb-1">Pos.</label>
                                                <input type="number" name="position" class="form-control form-control-sm"
                                                       value="<?php echo $item['position']; ?>" min="0">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-flex flex-column gap-1">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" name="active" class="form-check-input"
                                                               id="active-<?php echo $item['id']; ?>" <?php echo $item['active'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label small" for="active-<?php echo $item['id']; ?>">Actif</label>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" name="param_icon" class="form-check-input"
                                                               id="param_icon-<?php echo $item['id']; ?>" <?php echo $itemShowIcon ? 'checked' : ''; ?>>
                                                        <label class="form-check-label small" for="param_icon-<?php echo $item['id']; ?>">Afficher icône</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-sm btn-primary w-100">Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Sous-éléments -->
                                    <?php if (!empty($item['children'])): ?>
                                        <?php foreach ($item['children'] as $child):
                                            $childParams = $child['params'] ? json_decode($child['params'], true) : [];
                                            $childShowIcon = !isset($childParams['icon']) || $childParams['icon'];
                                        ?>
                                            <div class="menu-item child mt-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <i class="fas fa-arrow-right me-2 text-muted"></i>
                                                        <?php if (!empty($child['icon'])): ?>
                                                            <i class="<?php echo htmlspecialchars($child['icon']); ?> me-2 <?php echo $childShowIcon ? 'text-primary' : 'text-muted'; ?>"></i>
                                                        <?php endif; ?>
                                                        <strong><?php echo htmlspecialchars($child['title']); ?></strong>
                                                        <small class="d-block text-muted"><?php echo htmlspecialchars($child['url']); ?></small>
                                                        <?php if (!empty($child['icon'])): ?>
                                                            <span class="badge <?php echo $childShowIcon ? 'bg-info' : 'bg-light text-dark border'; ?> ms-1" style="font-size:.65rem;">
                                                                <i class="fas fa-eye<?php echo $childShowIcon ? '' : '-slash'; ?>"></i>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                                                                data-bs-target="#edit-item-<?php echo $child['id']; ?>">Éditer</button>
                                                        <form method="POST" class="m-0">
                                                            <input type="hidden" name="action" value="delete_item">
                                                            <input type="hidden" name="id" value="<?php echo $child['id']; ?>">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Supprimer cet élément ?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <!-- Formulaire d'édition enfant -->
                                                <div class="collapse mt-2" id="edit-item-<?php echo $child['id']; ?>">
                                                    <form method="POST" class="row g-2 align-items-end">
                                                        <input type="hidden" name="action" value="update_item">
                                                        <input type="hidden" name="id" value="<?php echo $child['id']; ?>">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                        <div class="col-md-3">
                                                            <input type="text" name="title" class="form-control form-control-sm"
                                                                   value="<?php echo htmlspecialchars($child['title']); ?>" placeholder="Titre">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" name="url" class="form-control form-control-sm"
                                                                   value="<?php echo htmlspecialchars($child['url']); ?>" placeholder="URL">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" name="icon" class="form-control form-control-sm"
                                                                   value="<?php echo htmlspecialchars($child['icon'] ?? ''); ?>" placeholder="fas fa-home">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <input type="number" name="position" class="form-control form-control-sm"
                                                                   value="<?php echo $child['position']; ?>" min="0">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="d-flex flex-column gap-1">
                                                                <div class="form-check form-switch">
                                                                    <input type="checkbox" name="active" class="form-check-input"
                                                                           id="active-<?php echo $child['id']; ?>" <?php echo $child['active'] ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label small" for="active-<?php echo $child['id']; ?>">Actif</label>
                                                                </div>
                                                                <div class="form-check form-switch">
                                                                    <input type="checkbox" name="param_icon" class="form-check-input"
                                                                           id="param_icon-<?php echo $child['id']; ?>" <?php echo $childShowIcon ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label small" for="param_icon-<?php echo $child['id']; ?>">Afficher icône</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="submit" class="btn btn-sm btn-primary w-100">Enregistrer</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Aucun élément dans ce menu</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Aucun menu disponible</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
