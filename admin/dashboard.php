<?php
// admin/dashboard.php
// Dashboard administrateur principal

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/modules.php';
require_once __DIR__ . '/../includes/settings_helpers.php';
require_once __DIR__ . '/../includes/upload.php';

// Vérifier l'authentification
requirePasswordChange();

// Initialiser les modules
ModuleRegistry::init($pdo);

// Charger les modèles
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Brand.php';
require_once __DIR__ . '/../app/models/Partner.php';
require_once __DIR__ . '/../app/models/News.php';
require_once __DIR__ . '/../app/models/Contact.php';
require_once __DIR__ . '/../app/models/Content.php';
require_once __DIR__ . '/../app/models/User.php';



// Initialiser les modèles
$productModel = new Product($pdo);
$categoryModel = new Category($pdo);
$brandModel = new Brand($pdo);
$partnerModel = new Partner($pdo);
$newsModel = new News($pdo);
$contactModel = new Contact($pdo);
$contentModel = new Content($pdo);
$UserModel = new User($pdo);


// Obtenir les statistiques
$stats = [
    'total_products' => $productModel->count(),
    'total_categories' => $categoryModel->count(false),
    'total_brands' => $brandModel->count(),
    'total_partners' => $partnerModel->count(),
    'total_news' => $newsModel->count(),
    'unread_messages' => $contactModel->count(true),
    'total_content_pages' => count($contentModel->listAll(false)),
    'total_users'         => $UserModel->count(), // ← AJOUTER

];

// Déterminer la section active
$section = $_GET['section'] ?? 'overview';

// Traiter les actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $section = $_POST['section'] ?? $section;
    
    // Vérifier le CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de sécurité invalide');
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
        exit;
    }
    
    switch ($action) {
        case 'delete_product':
            if (!is_module_enabled('products')) break;
            if ($productModel->delete($_POST['id'])) {
                setFlash('success', 'Produit supprimé avec succès');
                logAudit('delete_product', 'ID: ' . $_POST['id']);
            }
            break;
        
        case 'delete_brand':
            if (!is_module_enabled('brands')) break;
            if ($brandModel->delete($_POST['id'])) {
                setFlash('success', 'Marque supprimée avec succès');
                logAudit('delete_brand', 'ID: ' . $_POST['id']);
            }
            break;
        
        case 'delete_partner':
            if (!is_module_enabled('partners')) break;
            if ($partnerModel->delete($_POST['id'])) {
                setFlash('success', 'Partenaire supprimé avec succès');
                logAudit('delete_partner', 'ID: ' . $_POST['id']);
            }
            break;
        
        case 'delete_news':
            if (!is_module_enabled('news')) break;
            if ($newsModel->delete($_POST['id'])) {
                setFlash('success', 'Actualité supprimée avec succès');
                logAudit('delete_news', 'ID: ' . $_POST['id']);
            }
            break;
        
        case 'delete_message':
            if (!is_module_enabled('messages')) break;
            if ($contactModel->delete($_POST['id'])) {
                setFlash('success', 'Message supprimé avec succès');
                logAudit('delete_message', 'ID: ' . $_POST['id']);
            }
            break;

      case 'delete_content':
    if ($contentModel->delete((int)$_POST['id'])) {
        setFlash('success', 'Page supprimée avec succès');
        logAudit('delete_content', 'ID: ' . $_POST['id']);
    } else {
        setFlash('error', 'Erreur lors de la suppression de la page');
    }
    break;
 
    case 'delete_user':
    if (!hasRole('admin')) {
        setFlash('error', 'Accès interdit. Droits admin requis.');
        break;
    }
    if ($UserModel->delete((int)$_POST['id'])) {
        setFlash('success', 'Utilisateur supprimé avec succès');
        logAudit('delete_user', 'ID: ' . $_POST['id']);
    } else {
        setFlash('error', 'Erreur lors de la suppression de l\'utilisateur');
    }
    break;

    case 'toggle_module':
        if (!hasRole('admin')) {
            setFlash('error', 'Accès interdit. Droits admin requis.');
            break;
        }
        $moduleSlug = $_POST['module_slug'] ?? '';
        if (!empty($moduleSlug)) {
            $isCurrentlyEnabled = ModuleRegistry::isEnabled($moduleSlug);
            if ($isCurrentlyEnabled) {
                if (ModuleRegistry::disable($moduleSlug)) {
                    setFlash('success', 'Module "' . htmlspecialchars($moduleSlug) . '" désactivé');
                } else {
                    setFlash('error', 'Impossible de désactiver (module core ou dépendances actives)');
                }
            } else {
                if (ModuleRegistry::enable($moduleSlug)) {
                    setFlash('success', 'Module "' . htmlspecialchars($moduleSlug) . '" activé');
                } else {
                    setFlash('error', 'Impossible d\'activer le module (dépendances manquantes)');
                }
            }
        }
        break;

    case 'save_settings':
        if (!hasRole('admin')) {
            setFlash('error', 'Accès interdit. Droits admin requis.');
            break;
        }
        $newLogo = get_setting($pdo, 'site_logo', '');
        if (!empty($_FILES['site_logo']['name'])) {
            $uploadDir = __DIR__ . '/../assets/images/settings/';
            $res = upload_image($_FILES['site_logo'], $uploadDir, 'logo');
            if (!empty($res['error'])) {
                setFlash('error', $res['error']);
                break;
            }
            $newLogo = 'assets/images/settings/' . $res['filename'];
        }
        if (!empty($_POST['remove_logo'])) {
            $newLogo = '';
        }
        $siteNameVal = str_replace("\xC2\xA0", ' ', html_entity_decode(trim($_POST['site_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        set_setting($pdo, 'site_name', $siteNameVal);
        set_setting($pdo, 'site_logo', $newLogo);
        set_setting($pdo, 'footer_description', trim($_POST['footer_description'] ?? ''));
        set_setting($pdo, 'footer_email', trim($_POST['footer_email'] ?? ''));
        set_setting($pdo, 'footer_phone', trim($_POST['footer_phone'] ?? ''));
        set_setting($pdo, 'footer_copyright', trim($_POST['footer_copyright'] ?? ''));

        // Colonnes du footer (JSON)
        $cols = [];
        for ($i = 1; $i <= 3; $i++) {
            $colTitle = trim($_POST["footer_col_{$i}_title"] ?? '');
            $links    = [];
            $lines    = preg_split('/\r\n|\r|\n/', trim($_POST["footer_col_{$i}_links"] ?? ''));
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $parts = array_map('trim', explode('|', $line, 2));
                $links[] = ['label' => $parts[0], 'url' => $parts[1] ?? ''];
            }
            if ($colTitle !== '' || count($links) > 0) {
                $cols[] = ['title' => $colTitle, 'links' => $links];
            }
        }
        if (count($cols) > 0) {
            set_setting($pdo, 'footer_columns', json_encode($cols, JSON_UNESCAPED_UNICODE));
        }
        setFlash('success', 'Réglages enregistrés avec succès');
        logAudit('save_settings', 'Réglages du site modifiés');
        break;

    }
    
    header('Location: ' . BASE_URL . 'admin/dashboard.php?section=' . $section);
    exit;
}

// Récupérer les données
$products = $productModel->getAll(false );
$categories = $categoryModel->getAll(false);
$brands = $brandModel->getAll(false);
$partners = $partnerModel->getAll(false);
$news = $newsModel->getAllAdmin();
$messages = $contactModel->getAll(10);
$unreadMessages = $contactModel->getUnread();
$contentPages = $contentModel->listAll(false);

$users = $UserModel->getAll(); //

$admin = getCurrentAdmin();
$flash = getFlash();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - VEP</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@10.2.0/dist/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #435980;
            --primary-dark: #345075;
            --secondary: #87A952;
        }
        
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            min-height: 100vh;
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: bold;
        }
        
        .sidebar .nav-menu {
            list-style: none;
        }
        
        .sidebar .nav-item {
            margin: 5px 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            display: block;
            border-left: 3px solid transparent;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .topbar {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
            margin-bottom: 20px;
            border-top: 4px solid var(--primary);
        }
        
        .stat-card h3 {
            font-size: 28px;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            margin: 0;
        }
        
        .data-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .data-table thead {
            background: #f5f5f5;
        }
        
        .data-table thead th {
            border: none;
            padding: 15px;
            font-weight: 600;
            color: var(--text);
        }
        
        .data-table tbody td {
            padding: 12px 15px;
            border-top: 1px solid #eee;
        }
        
        .btn-action {
            padding: 6px 12px;
            font-size: 12px;
            margin-right: 5px;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
        }

      /* ===================================================
   card page cms
   =================================================== */

.page-card {
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.06) !important;
    border-radius: 16px !important;
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
                box-shadow 0.3s ease,
                border-color 0.3s ease;
    position: relative;
    overflow: hidden;
}

/* Barre colorée animée en haut de la carte */
.page-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #4f46e5, #06b6d4);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 16px 16px 0 0;
}

.page-card:hover::before {
    transform: scaleX(1);
}

.page-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(79, 70, 229, 0.08),
                0 8px 16px rgba(0, 0, 0, 0.06) !important;
    border-color: rgba(79, 70, 229, 0.15) !important;
}

/* Card body */
.page-card .card-body {
    padding: 1.4rem 1.4rem 1rem;
}

/* ID et badge */
.page-card .card-body .text-muted.small {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #a0aec0 !important;
}

/* Badge statut */
.page-card .badge {
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    padding: 0.35em 0.75em;
    border-radius: 999px;
}

.page-card .badge.bg-success {
    background: #dcfce7 !important;
    color: #16a34a !important;
}

.page-card .badge.bg-secondary {
    background: #f1f5f9 !important;
    color: #64748b !important;
}

/* Template et slug */
.page-card .card-body p.text-muted {
    font-size: 0.78rem;
    color: #94a3b8 !important;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.page-card .card-body p.text-muted i {
    font-size: 0.7rem;
    color: #cbd5e1;
}

/* Titre */
.page-card .card-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.3;
    letter-spacing: -0.01em;
}

/* Footer */
.page-card .card-footer {
    padding: 0.9rem 1.4rem;
    background: #f8fafc !important;
    border-top: 1px solid rgba(0, 0, 0, 0.05) !important;
}

.page-card .card-footer .btn {
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: 8px;
    padding: 0.45rem 0.75rem;
    transition: all 0.2s ease;
    letter-spacing: 0.01em;
}

.page-card .card-footer .btn-primary {
    background: #4f46e5;
    border-color: #4f46e5;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
}

.page-card .card-footer .btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
    transform: translateY(-1px);
}

.page-card .card-footer .btn-outline-secondary {
    color: #64748b;
    border-color: #e2e8f0;
    background: #ffffff;
}

.page-card .card-footer .btn-outline-secondary:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #475569;
    transform: translateY(-1px);
}

/* Petits boutons icônes */
.page-card .card-footer .btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.75rem;
}

.page-card .card-footer .btn-editor {
    font-size: 0.72rem;
    padding: 0.4rem 0.65rem;
    white-space: nowrap;
}
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-cog"></i> Administrator
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="?section=overview" class="nav-link <?php echo $section === 'overview' ? 'active' : ''; ?>">
                    <i class="fas fa-dashboard"></i> Tableau de bord
                </a>
            </li>
            <?php if (is_module_enabled('products')): ?>
            <li class="nav-item">
                <a href="?section=products" class="nav-link <?php echo $section === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Produits
                </a>
            </li>
            <li class="nav-item">
                <a href="?section=categories" class="nav-link <?php echo $section === 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Catégories
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="?section=content" class="nav-link <?php echo $section === 'content' ? 'active' : ''; ?>">
                    <i class="fas fa-file-lines"></i> Pages CMS
                </a>
            </li>
            <?php if (is_module_enabled('brands')): ?>
            <li class="nav-item">
                <a href="?section=brands" class="nav-link <?php echo $section === 'brands' ? 'active' : ''; ?>">
                    <i class="fas fa-tag"></i> Marques
                </a>
            </li>
            <?php endif; ?>
            <?php if (is_module_enabled('partners')): ?>
            <li class="nav-item">
                <a href="?section=partners" class="nav-link <?php echo $section === 'partners' ? 'active' : ''; ?>">
                    <i class="fas fa-handshake"></i> Partenaires
                </a>
            </li>
            <?php endif; ?>
            <?php if (is_module_enabled('news')): ?>
            <li class="nav-item">
                <a href="?section=news" class="nav-link <?php echo $section === 'news' ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper"></i> Actualités
                </a>
            </li>
            <?php endif; ?>
            <?php if (is_module_enabled('messages')): ?>
            <li class="nav-item">
                <a href="?section=messages" class="nav-link <?php echo $section === 'messages' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> Messages
                    <?php if ($stats['unread_messages'] > 0): ?>
                        <span class="badge bg-danger"><?php echo $stats['unread_messages']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if (is_module_enabled('media')): ?>
            <li class="nav-item">
                <a href="media/index.php" class="nav-link">
                    <i class="fas fa-photo-video"></i> Médias
                </a>
            </li>
            <?php endif; ?>
            <?php if (is_module_enabled('menus')): ?>
            <li class="nav-item">
                <a href="menus/index.php" class="nav-link">
                    <i class="fas fa-bars"></i> Menus
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasRole('admin')): ?>
            <li class="nav-item">
                <a href="?section=settings" class="nav-link <?php echo $section === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-sliders-h"></i> Réglages du site
                </a>
            </li>
            <li class="nav-item">
                <a href="?section=modules" class="nav-link <?php echo $section === 'modules' ? 'active' : ''; ?>">
                    <i class="fas fa-puzzle-piece"></i> Modules
                </a>
            </li>
            <li class="nav-item">
    <a href="?section=users" class="nav-link <?php echo $section === 'users' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> Utilisateurs
    </a>
</li>
<?php endif; ?>

            <?php if (is_module_enabled('sliders')): ?>
            <li class="nav-item">
                <a href="sliders/index.php" class="nav-link">
                    <i class="fas fa-images"></i> Sliders
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 10px 0;">
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <h4 style="margin: 0;">Bienvenue, <?php echo htmlspecialchars($admin['fullname'] ?? 'Admin'); ?></h4>
            </div>
            <div>
                <span style="margin-right: 20px;"><?php echo date('d/m/Y H:i'); ?></span>
                <a href="../index.php" class="btn btn-sm btn-outline-success">Aller au site Web</a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">Déconnexion</a>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Overview Section -->
        <div class="content-section <?php echo $section === 'overview' ? 'active' : ''; ?>" id="overview">
            <h3 class="mb-4">Tableau de bord</h3>
            
            <div class="row">
                <?php if (is_module_enabled('products')): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <h3><?php echo $stats['total_products']; ?></h3>
                        <p>Produits</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <h3><?php echo $stats['total_categories']; ?></h3>
                        <p>Catégories</p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (is_module_enabled('brands')): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <h3><?php echo $stats['total_brands']; ?></h3>
                        <p>Marques</p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (is_module_enabled('partners')): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <h3><?php echo $stats['total_partners']; ?></h3>
                        <p>Partenaires</p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (is_module_enabled('news')): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <h3><?php echo $stats['total_news']; ?></h3>
                        <p>Actualités</p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (is_module_enabled('messages')): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card" style="border-top-color: #e74c3c;">
                        <h3 style="color: #e74c3c;"><?php echo $stats['unread_messages']; ?></h3>
                        <p>Messages non lus</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="row mt-4">
                <?php if (is_module_enabled('products')): ?>
                <div class="col-md-6">
                    <h4>Derniers produits</h4>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($products, 0, 5) as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(substr($product['nom'], 0, 20)); ?></td>
                                <td><small><?php echo htmlspecialchars($product['categorie_name']); ?></small></td>
                                <td>
                                    <a href="?section=products" class="btn btn-sm btn-primary btn-action">Éditer</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <?php if (is_module_enabled('messages')): ?>
                <div class="col-md-6">
                    <h4>Derniers messages</h4>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($messages, 0, 5) as $msg): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(substr($msg['nom'], 0, 15)); ?></td>
                                <td><small><?php echo htmlspecialchars(substr($msg['email'], 0, 20)); ?></small></td>
                                <td>
                                    <a href="?section=messages" class="btn btn-sm btn-info btn-action">Voir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Products Section -->
        <?php if (is_module_enabled('products')): ?>
        <div class="content-section <?php echo $section === 'products' ? 'active' : ''; ?>" id="products">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Gestion des Produits</h3>
                <a href="products/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </a>
            </div>
            
            <div class="data-table">
                <table class="table table-hover mb-0" id="dt-products" data-datatable data-dt-columns='[{"select":5,"sortable":false}]'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Marque</th>
                            <th>Actif</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['nom']); ?></td>
                            <td><?php echo htmlspecialchars($product['categorie_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['marque_name'] ?? '-'); ?></td>
                            <td>
                                <span class="badge <?php echo $product['active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $product['active'] ? 'Oui' : 'Non'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="products/edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary btn-action">Éditer</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Confirmer la suppression?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
<!-- Categories Section -->
<?php if (is_module_enabled('products')): ?>
<div class="content-section <?php echo $section === 'categories' ? 'active' : ''; ?>" id="categories">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Gestion des Catégories</h3>
        <a href="categories/create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter une catégorie
        </a>
    </div>

    <div class="data-table">
        <table class="table table-hover mb-0" id="dt-categories" data-datatable data-dt-columns='[{"select":5,"sortable":false}]'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Ordre</th>
                    <th>Actif</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?php echo (int)$category['id']; ?></td>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                    <td><?php echo (int)($category['display_order'] ?? 0); ?></td>
                    <td>
                        <span class="badge <?php echo !empty($category['active']) ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo !empty($category['active']) ? 'Oui' : 'Non'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="categories/edit.php?id=<?php echo (int)$category['id']; ?>" class="btn btn-sm btn-primary btn-action">Éditer</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" name="id" value="<?php echo (int)$category['id']; ?>">
                            <button type="submit" 
                                    class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer la catégorie \"<?php echo htmlspecialchars($category['name']); ?>\" ? Cette action est irréversible.')">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>


        <!-- Content Pages Section -->
<div class="content-section <?php echo $section === 'content' ? 'active' : ''; ?>" id="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Gestion des pages CMS</h3>
        <a href="content/create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter une page
        </a>
    </div>

    <div class="row g-3">
        <?php foreach ($contentPages as $page): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card shadow-sm border-0 page-card" style="min-height: 320px;">

                <!-- APERÇU PLEIN FORMAT -->
                <div style="position: relative; overflow: hidden; background: #f8f9fa; height: 280px;">
                    <iframe
                        src="<?php echo BASE_URL . htmlspecialchars($page['slug']); ?>"
                        scrolling="no"
                        style="
                            width: 500%;
                            height: 500%;
                            border: none;
                            transform: scale(0.20);
                            transform-origin: top left;
                            pointer-events: none;
                            max-width: none;
                        "
                        loading="lazy"
                        title="Aperçu <?php echo htmlspecialchars($page['title']); ?>"
                    ></iframe>

                    <!-- Dégradé + infos overlay -->
                    <div style="
                        position: absolute;
                        inset: 0;
                        background: linear-gradient(to bottom, rgba(0,0,0,0.15) 0%, transparent 40%, rgba(0,0,0,0.75) 100%);
                    ">
                        <!-- Badge ID + statut en haut -->
                        <div class="p-2 d-flex justify-content-between align-items-start">
                            <span class="badge bg-dark bg-opacity-50">#<?php echo (int)$page['id']; ?></span>
                            <span class="badge <?php echo $page['status'] === 'published' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo htmlspecialchars($page['status']); ?>
                            </span>
                        </div>

                        <!-- Titre + template + slug en bas -->
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 14px;">
                            <h6 class="text-white fw-bold mb-1"><?php echo htmlspecialchars($page['title']); ?></h6>
                            <small class="text-white opacity-75 d-block">
                                <i class="fas fa-file-alt me-1"></i><?php echo htmlspecialchars($page['template'] ?? 'default'); ?>
                            </small>
                            <small class="text-white opacity-75 d-block">
                                <i class="fas fa-link me-1"></i><?php echo htmlspecialchars($page['slug']); ?>
                            </small>
    </div>
</div>
                </div>

<!-- FOOTER ACTIONS -->
<div class="card-footer bg-transparent border-top d-flex gap-2 align-items-center">
    <a href="content/body-editor.php?id=<?php echo (int)$page['id']; ?>" class="btn btn-sm btn-primary btn-editor" title="Éditeur visuel">
        <i class="fas fa-paint-brush me-1"></i>Éditeur visuel
    </a>
    <a href="content/edit.php?id=<?php echo (int)$page['id']; ?>" class="btn btn-sm btn-outline-secondary btn-icon" title="Métadonnées">
        <i class="fas fa-cog"></i>
    </a>
    <a href="<?php echo BASE_URL . htmlspecialchars($page['slug']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary btn-icon" title="Voir la page">
        <i class="fas fa-eye"></i>
    </a>
    <!-- SUPPRESSION -->
    <form method="POST" style="display: inline; margin-left: auto;">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="action" value="delete_content">
        <input type="hidden" name="id" value="<?php echo (int)$page['id']; ?>">
        <button type="submit" 
                class="btn btn-sm btn-outline-danger btn-icon" 
                title="Supprimer"
                onclick="return confirm('Supprimer la page \"<?php echo htmlspecialchars($page['title']); ?>\" ? Cette action est irréversible.')">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>



        <!-- Brands Section -->
        <?php if (is_module_enabled('brands')): ?>
        <div class="content-section <?php echo $section === 'brands' ? 'active' : ''; ?>" id="brands">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Gestion des Marques</h3>
                <a href="brands/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter une marque
                </a>
            </div>
            
            <div class="data-table">
                <table class="table table-hover mb-0" id="dt-brands" data-datatable data-dt-columns='[{"select":3,"sortable":false}]'>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Logo</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($brands as $brand): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($brand['name']); ?></td>
                            <td>
                                <?php if (!empty($brand['logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($brand['logo']); ?>" alt="" style="max-height: 40px;">
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(substr($brand['description'], 0, 50)); ?></td>
                            <td>
                                <a href="brands/edit.php?id=<?php echo $brand['id']; ?>" class="btn btn-sm btn-primary btn-action">Éditer</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="action" value="delete_brand">
                                    <input type="hidden" name="id" value="<?php echo $brand['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Confirmer la suppression?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Partners Section -->
        <?php if (is_module_enabled('partners')): ?>
        <div class="content-section <?php echo $section === 'partners' ? 'active' : ''; ?>" id="partners">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Gestion des Partenaires</h3>
                <a href="partners/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un partenaire
                </a>
            </div>
            
            <div class="data-table">
                <table class="table table-hover mb-0" id="dt-partners" data-datatable data-dt-columns='[{"select":3,"sortable":false}]'>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Logo</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($partners as $partner): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($partner['name']); ?></td>
                            <td>
                                <?php if (!empty($partner['logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($partner['logo']); ?>" alt="" style="max-height: 40px;">
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(substr($partner['description'], 0, 50)); ?></td>
                            <td>
                                <a href="partners/edit.php?id=<?php echo $partner['id']; ?>" class="btn btn-sm btn-primary btn-action">Éditer</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="action" value="delete_partner">
                                    <input type="hidden" name="id" value="<?php echo $partner['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Confirmer la suppression?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- News Section -->
        <?php if (is_module_enabled('news')): ?>
        <div class="content-section <?php echo $section === 'news' ? 'active' : ''; ?>" id="news">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Gestion des Actualités</h3>
                <a href="news/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter une actualité
                </a>
            </div>
            
            <div class="data-table">
                <table class="table table-hover mb-0" id="dt-news" data-datatable data-dt-columns='[{"select":3,"sortable":false}]'>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($item['published_at'])); ?></td>
                            <td>
                                <span class="badge <?php echo $item['status'] === 'published' ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="news/edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary btn-action">Éditer</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="action" value="delete_news">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Confirmer la suppression?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Messages Section -->
        <?php if (is_module_enabled('messages')): ?>
        <div class="content-section <?php echo $section === 'messages' ? 'active' : ''; ?>" id="messages">
            <h3 class="mb-4">Messages de contact</h3>
            
            <div class="data-table">
                <table class="table table-hover mb-0" id="dt-messages" data-datatable data-dt-columns='[{"select":5,"sortable":false}]'>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Sujet</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($msg['nom']); ?></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>"><?php echo htmlspecialchars($msg['email']); ?></a></td>
                            <td><?php echo htmlspecialchars($msg['telephone']); ?></td>
                            <td><?php echo htmlspecialchars($msg['sujet']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></td>
                            <td>
                                <a href="messages/view.php?id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-info btn-action">Voir</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="action" value="delete_message">
                                    <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Confirmer la suppression?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>


    <!-- Users Section -->
<?php if (hasRole('admin')): ?>
<!-- Users Section -->
<div class="content-section <?php echo $section === 'users' ? 'active' : ''; ?>" id="users">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Gestion des Utilisateurs</h3>
        <a href="users/create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter un utilisateur
        </a>
    </div>

    <div class="data-table">
        <table class="table table-hover mb-0" id="dt-users" data-datatable data-dt-columns='[{"select":6,"sortable":false}]'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Créé le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo (int)$user['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($user['fullname'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($user['email'] ?? '—'); ?></td>
                    <td>
                        <span class="badge <?php echo !empty($user['active']) ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo !empty($user['active']) ? 'Actif' : 'Inactif'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="users/edit.php?id=<?php echo (int)$user['id']; ?>" class="btn btn-sm btn-primary btn-action">
                            <i class="fas fa-edit"></i> Éditer
                        </a>

                            <!-- Bouton Supprimer -->
    <form method="POST" style="display:inline;" 
          onsubmit="return confirm('Supprimer cet utilisateur ?');">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
        <button type="submit" class="btn btn-sm btn-danger btn-action">
            <i class="fas fa-trash"></i> Supprimer
        </button>
    </form>
</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

    <!-- Modules Section -->
    <?php if (hasRole('admin')): ?>
    <div class="content-section <?php echo $section === 'modules' ? 'active' : ''; ?>" id="modules">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-puzzle-piece me-2"></i>Gestion des Modules</h2>
        </div>

        <div class="row">
            <?php
            $allModules = $pdo->query("SELECT * FROM modules ORDER BY type, name")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allModules as $mod):
                $isEnabled = (bool)$mod['enabled'];
                $typeBadge = match($mod['type']) {
                    'core'  => 'bg-danger',
                    'annex' => 'bg-warning text-dark',
                    'addon' => 'bg-success',
                    default => 'bg-secondary'
                };
            ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($mod['name']); ?></h5>
                            <span class="badge <?php echo $typeBadge; ?>"><?php echo $mod['type']; ?></span>
                        </div>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($mod['description'] ?? ''); ?></p>
                        <?php if (!empty($mod['depends_on'])): ?>
                        <p class="card-text"><small class="text-muted">Dépend de : <?php echo htmlspecialchars($mod['depends_on']); ?></small></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <?php if ($mod['type'] === 'core'): ?>
                            <span class="badge bg-danger"><i class="fas fa-lock me-1"></i>Core (toujours actif)</span>
                        <?php else: ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="action" value="toggle_module">
                            <input type="hidden" name="module_slug" value="<?php echo htmlspecialchars($mod['slug']); ?>">
                            <input type="hidden" name="section" value="modules">
                            <button type="submit" class="btn btn-sm <?php echo $isEnabled ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                <i class="fas fa-<?php echo $isEnabled ? 'power-off' : 'power-off'; ?>"></i>
                                <?php echo $isEnabled ? 'Désactiver' : 'Activer'; ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (hasRole('admin')): ?>
    <div class="content-section <?php echo $section === 'settings' ? 'active' : ''; ?>" id="settings">
        <h3 class="mb-4"><i class="fas fa-sliders-h me-2"></i>Réglages du site</h3>
        <div class="row">
            <div class="col-lg-8">
                <form method="POST" enctype="multipart/form-data" class="card shadow-sm border-0">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="save_settings">
                    <input type="hidden" name="section" value="settings">

                    <div class="card-body">
                        <h5 class="mb-3"><i class="fas fa-id-badge me-2"></i>Identité du site</h5>
                        <div class="mb-3">
                            <label class="form-label">Nom du site</label>
                            <?php $siteNameVal = str_replace("\xC2\xA0", ' ', html_entity_decode(get_setting($pdo, 'site_name', 'Noor Guide'), ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>
                            <input type="text" name="site_name" class="form-control"
                                   value="<?php echo htmlspecialchars($siteNameVal); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" name="site_logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <?php $currentLogo = get_setting($pdo, 'site_logo', ''); ?>
                            <?php if (!empty($currentLogo)): ?>
                                <div class="mt-2 d-flex align-items-center gap-3">
                                    <img src="<?php echo BASE_URL . ltrim($currentLogo, '/'); ?>" alt="Logo actuel" style="max-height:48px;border-radius:8px;">
                                    <div class="form-check">
                                        <input type="checkbox" name="remove_logo" value="1" class="form-check-input" id="remove_logo">
                                        <label class="form-check-label" for="remove_logo">Supprimer le logo actuel</label>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="form-text">Aucun logo défini. Si aucun logo n'est fourni, l'initiale du nom est affichée.</div>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <h5 class="mb-3"><i class="fas fa-shoe-prints me-2"></i>Pied de page (footer)</h5>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="footer_description" class="form-control" rows="3"><?php echo htmlspecialchars(get_setting($pdo, 'footer_description', 'Application mobile de guidage pour personnes aveugles et malvoyantes.')); ?></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Email de contact</label>
                                <input type="email" name="footer_email" class="form-control"
                                       value="<?php echo htmlspecialchars(get_setting($pdo, 'footer_email', 'contact@noorguide.com')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="footer_phone" class="form-control"
                                       value="<?php echo htmlspecialchars(get_setting($pdo, 'footer_phone', '+33 (0)1 23 45 67 89')); ?>">
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Texte de copyright</label>
                            <input type="text" name="footer_copyright" class="form-control"
                                   value="<?php echo htmlspecialchars(get_setting($pdo, 'footer_copyright', 'Noor Guide — Tous droits réservés.')); ?>">
                        </div>

                        <hr>

                        <h5 class="mb-3"><i class="fas fa-link me-2"></i>Colonnes de liens du footer</h5>
                        <?php
                        $footerColumns = json_decode(get_setting($pdo, 'footer_columns', ''), true);
                        if (!is_array($footerColumns) || empty($footerColumns)) {
                            $footerColumns = [
                                ['title' => 'Application', 'links' => [
                                    ['label' => 'Fonctionnalités', 'url' => '#features'],
                                    ['label' => 'Comment ça marche', 'url' => '#how-it-works'],
                                    ['label' => 'Accessibilité', 'url' => '#accessibility'],
                                    ['label' => 'Télécharger', 'url' => '#contact'],
                                ]],
                                ['title' => 'Ressources', 'links' => [
                                    ['label' => 'Documentation', 'url' => 'documentation'],
                                    ['label' => 'FAQ', 'url' => 'faq'],
                                    ['label' => 'Blog', 'url' => 'blog'],
                                    ['label' => 'Support', 'url' => 'support'],
                                ]],
                            ];
                        }
                        $footerColumns = array_pad(array_slice($footerColumns, 0, 3), 3, ['title' => '', 'links' => []]);
                        foreach ($footerColumns as $colIdx => $col):
                            $colTitle = $col['title'] ?? '';
                            $colLinks = isset($col['links']) && is_array($col['links']) ? $col['links'] : [];
                            $colLinksText = '';
                            foreach ($colLinks as $l) {
                                $colLinksText .= ($l['label'] ?? '') . '|' . ($l['url'] ?? '') . "\n";
                            }
                        ?>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Titre colonne <?php echo $colIdx + 1; ?></label>
                                <input type="text" name="footer_col_<?php echo $colIdx + 1; ?>_title" class="form-control"
                                       value="<?php echo htmlspecialchars($colTitle); ?>">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Liens (un par ligne : <code>Libellé|URL</code>)</label>
                                <textarea name="footer_col_<?php echo $colIdx + 1; ?>_links" class="form-control" rows="5"
                                          placeholder="Fonctionnalités|#features"><?php echo htmlspecialchars(rtrim($colLinksText)); ?></textarea>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="form-text">Astuce : le lien peut être relatif (<code>documentation</code>), une ancre (<code>#features</code>) ou une URL complète (<code>https://...</code>).</div>
                    </div>

                    <div class="card-footer bg-transparent d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Enregistrer les réglages
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@10.2.0/dist/umd/simple-datatables.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/admin-tables.js"></script>
</body>
</html>





