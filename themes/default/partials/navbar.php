<?php
require_once dirname(__DIR__, 3) . '/app/models/Menu.php';
if (!function_exists('get_setting')) {
    require_once dirname(__DIR__, 3) . '/includes/settings_helpers.php';
}
$menuModel  = new Menu($pdo);
$mainMenu   = $menuModel->getByName('main');
$menuItems  = $mainMenu ? $menuModel->getItemsWithChildren($mainMenu['id']) : [];
$siteName   = get_setting($pdo, 'site_name', 'Noor Guide');
$siteName   = str_replace("\xC2\xA0", ' ', html_entity_decode($siteName, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
$siteLogo   = get_setting($pdo, 'site_logo', '');
$nameColor  = get_setting($pdo, 'site_name_color', '#1A1A2E');
$accentColor = get_setting($pdo, 'site_name_color_accent', '#FF6B00');
$brandFont  = get_setting($pdo, 'site_name_font_family', '');
$brandSize  = get_setting($pdo, 'site_name_font_size', '');
$brandBold  = get_setting($pdo, 'site_name_bold', '1');
$brandItalic = get_setting($pdo, 'site_name_italic', '0');
$brandUnderline = get_setting($pdo, 'site_name_underline', '0');
$siteInitial = function_exists('mb_substr') ? mb_substr(trim($siteName), 0, 1) : substr(trim($siteName), 0, 1);
$nameParts  = explode(' ', trim($siteName), 2);
$brandHtml  = get_setting($pdo, 'site_name_html', '');
if ($brandHtml !== '') {
    $brandInner = sanitize_brand_html($brandHtml);
} else {
    $brandInner = htmlspecialchars($nameParts[0], ENT_QUOTES, 'UTF-8');
    if (isset($nameParts[1]) && $nameParts[1] !== '') {
        $brandInner .= ' <span style="color:' . htmlspecialchars($accentColor, ENT_QUOTES, 'UTF-8') . ';">' . htmlspecialchars($nameParts[1], ENT_QUOTES, 'UTF-8') . '</span>';
    }
}
$brandStyle = 'color:' . $nameColor . ';'
    . ($brandFont !== '' ? 'font-family:' . $brandFont . ';' : '')
    . ($brandSize !== '' ? 'font-size:' . $brandSize . ';' : '')
    . 'font-weight:' . ($brandBold === '1' ? '700' : '400') . ';'
    . 'font-style:' . ($brandItalic === '1' ? 'italic' : 'normal') . ';'
    . 'text-decoration:' . ($brandUnderline === '1' ? 'underline' : 'none') . ';';
$isAdmin    = function_exists('isLoggedIn') && isLoggedIn();
$mainMenuId = $mainMenu ? (int)$mainMenu['id'] : 0;
$navItems   = [];
if ($mainMenu) {
    $navItems = $menuModel->getItemsWithChildren($mainMenu['id'], false);
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>" aria-label="<?php echo htmlspecialchars($siteName); ?> — Retour à l'accueil">
            <?php if (!empty($siteLogo)): ?>
                <img src="<?php echo BASE_URL . ltrim($siteLogo, '/'); ?>" alt="<?php echo htmlspecialchars($siteName); ?>" data-ie-logo style="height:40px;width:auto;">
            <?php else: ?>
                <span data-ie-logo class="d-inline-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;background:#FF6B00;color:#fff;font-weight:900;font-size:1.1rem;border-radius:12px;"><?php echo htmlspecialchars($siteInitial); ?></span>
            <?php endif; ?>
            <span data-ie-setting="site_name" style="<?php echo htmlspecialchars($brandStyle, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $brandInner; ?></span>
            <?php if ($isAdmin): ?>
            <span data-ie-color-edit class="ie-color-btn" title="Changer les couleurs du logo" aria-label="Changer les couleurs du logo" role="button" tabindex="0">
                <i class="fas fa-palette"></i>
            </span>
            <?php endif; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Ouvrir le menu de navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto align-items-center" id="main-menu-nav">
                <?php foreach ($menuItems as $item):
                    $params   = $item['params'] ? json_decode($item['params'], true) : [];
                    $showIcon = !isset($params['icon']) || $params['icon'];
                    $icon     = ($showIcon && !empty($item['icon'])) ? htmlspecialchars($item['icon']) : '';
                ?>
                <li class="nav-item<?php echo !empty($item['children']) ? ' dropdown' : ''; ?>"
                    data-ie-menu-item="<?php echo (int)$item['id']; ?>"
                    data-ie-menu-title="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-ie-menu-url="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-ie-menu-icon="<?php echo htmlspecialchars($item['icon'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    data-ie-menu-position="<?php echo (int)$item['position']; ?>"
                    data-ie-menu-parent="<?php echo (int)($item['parent_id'] ?? 0); ?>"
                    data-ie-menu-active="<?php echo (int)$item['active']; ?>"
                    data-ie-menu-showicon="<?php echo (int)$showIcon; ?>">
                    <a class="nav-link<?php echo !empty($item['children']) ? ' dropdown-toggle' : ''; ?>"
                       href="<?php echo BASE_URL . ltrim($item['url'], '/'); ?>"
                       <?php echo !empty($item['children']) ? 'role="button" data-bs-toggle="dropdown"' : ''; ?>>
                        <?php if ($icon): ?><i class="<?php echo $icon; ?> me-1"></i><?php endif; ?>
                        <?php echo htmlspecialchars($item['title']); ?>
                    </a>
                    <?php if ($isAdmin): ?>
                    <div class="ie-menu-actions">
                        <button type="button" class="ie-menu-btn" data-ie-menu-edit title="Modifier" aria-label="Modifier <?php echo htmlspecialchars($item['title']); ?>">✎</button>
                        <button type="button" class="ie-menu-btn danger" data-ie-menu-delete title="Supprimer" aria-label="Supprimer <?php echo htmlspecialchars($item['title']); ?>">🗑</button>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($item['children'])): ?>
                    <ul class="dropdown-menu">
                        <?php foreach ($item['children'] as $child):
                            $cp   = $child['params'] ? json_decode($child['params'], true) : [];
                            $ci   = (!isset($cp['icon']) || $cp['icon']) && !empty($child['icon']) ? htmlspecialchars($child['icon']) : '';
                        ?>
                        <li data-ie-menu-item="<?php echo (int)$child['id']; ?>"
                            data-ie-menu-title="<?php echo htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-ie-menu-url="<?php echo htmlspecialchars($child['url'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-ie-menu-icon="<?php echo htmlspecialchars($child['icon'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-ie-menu-position="<?php echo (int)$child['position']; ?>"
                            data-ie-menu-parent="<?php echo (int)($child['parent_id'] ?? 0); ?>"
                            data-ie-menu-active="<?php echo (int)$child['active']; ?>"
                            data-ie-menu-showicon="<?php echo (int)($cp['icon'] ?? 1); ?>"
                            style="position:relative;">
                            <a class="dropdown-item" href="<?php echo BASE_URL . ltrim($child['url'], '/'); ?>">
                                <?php if ($ci): ?><i class="<?php echo $ci; ?> me-2"></i><?php endif; ?>
                                <?php echo htmlspecialchars($child['title']); ?>
                            </a>
                            <?php if ($isAdmin): ?>
                            <div class="ie-menu-actions">
                                <button type="button" class="ie-menu-btn" data-ie-menu-edit title="Modifier" aria-label="Modifier <?php echo htmlspecialchars($child['title']); ?>">✎</button>
                                <button type="button" class="ie-menu-btn danger" data-ie-menu-delete title="Supprimer" aria-label="Supprimer <?php echo htmlspecialchars($child['title']); ?>">🗑</button>
                            </div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <button type="button" class="nav-link border-0 bg-transparent" data-ie-menu-add title="Ajouter un élément au menu" aria-label="Ajouter un élément au menu">
                        <i class="fas fa-plus me-1" style="color:#FF6B00;"></i>Ajouter
                    </button>
                </li>
                <?php endif; ?>
            </ul>
            <div class="navbar-right ms-lg-3">
                <?php if (isset($_SESSION['admin_id'])): ?>
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                    <i class="fas fa-cog me-1"></i>Dashboard
                </a>
                <?php else: ?>
                <a class="nav-link" href="<?php echo BASE_URL; ?>login.php">
                    <i class="fas fa-lock me-1"></i>Admin
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if ($isAdmin): ?>
<?php
// Liste plate de tous les éléments du menu (parents + enfants) pour l'éditeur JS
$menuListData = [];
foreach ($navItems as $it) {
    $menuListData[] = [
        'id'       => (int)$it['id'],
        'title'    => $it['title'],
        'url'      => $it['url'],
        'parent'   => (int)($it['parent_id'] ?? 0),
        'position' => (int)$it['position'],
    ];
    foreach (($it['children'] ?? []) as $ch) {
        $menuListData[] = [
            'id'       => (int)$ch['id'],
            'title'    => $ch['title'],
            'url'      => $ch['url'],
            'parent'   => (int)($ch['parent_id'] ?? 0),
            'position' => (int)$ch['position'],
        ];
    }
}
?>
<script>
window.__ieMenu = {
    menuId: <?php echo $mainMenuId; ?>,
    items: <?php echo json_encode($menuListData, JSON_UNESCAPED_UNICODE); ?>,
    baseUrl: <?php echo json_encode(BASE_URL); ?>
};
window.__ieBrand = {
    color: <?php echo json_encode($nameColor); ?>,
    accent: <?php echo json_encode($accentColor); ?>,
    font: <?php echo json_encode($brandFont); ?>,
    size: <?php echo json_encode($brandSize); ?>,
    bold: <?php echo json_encode($brandBold === '1' ? 1 : 0); ?>,
    italic: <?php echo json_encode($brandItalic === '1' ? 1 : 0); ?>,
    underline: <?php echo json_encode($brandUnderline === '1' ? 1 : 0); ?>,
    html: <?php echo json_encode($brandHtml !== '' ? $brandInner : ''); ?>
};
</script>
<?php endif; ?>