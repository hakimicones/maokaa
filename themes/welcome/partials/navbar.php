<?php
require_once dirname(__DIR__) . '/includes/content.php';
require_once dirname(__DIR__, 3) . '/app/models/Menu.php';
if (!function_exists('get_setting')) {
    require_once dirname(__DIR__, 3) . '/includes/settings_helpers.php';
}
$menuModel  = new Menu($pdo);
$mainMenu   = $menuModel->getByName('main');
$menuItems  = $mainMenu ? $menuModel->getItemsWithChildren($mainMenu['id']) : [];
$siteName   = get_setting($pdo, 'site_name', 'Votre site');
$siteName   = welcome_is_legacy_content($siteName) ? 'Votre site' : $siteName;
$siteName   = str_replace("\xC2\xA0", ' ', html_entity_decode($siteName, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
$siteLogo   = get_setting($pdo, 'site_logo', '');
$siteLogo   = welcome_is_legacy_content($siteLogo) ? '' : $siteLogo;
$nameColor  = get_setting($pdo, 'site_name_color', '#202533');
$accentColor = get_setting($pdo, 'site_name_color_accent', '#315efb');
$brandFont  = get_setting($pdo, 'site_name_font_family', '');
$brandSize  = get_setting($pdo, 'site_name_font_size', '');
$brandBold  = get_setting($pdo, 'site_name_bold', '1');
$brandItalic = get_setting($pdo, 'site_name_italic', '0');
$brandUnderline = get_setting($pdo, 'site_name_underline', '0');
$siteInitial = function_exists('mb_substr') ? mb_substr(trim($siteName), 0, 1) : substr(trim($siteName), 0, 1);
$nameParts  = explode(' ', trim($siteName), 2);
$brandHtml  = get_setting($pdo, 'site_name_html', '');
if ($brandHtml !== '' && !welcome_is_legacy_content($brandHtml)) {
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
$isHostingTheme = class_exists('ThemeManager') && ThemeManager::getActive() === 'hebergement';
$mainMenuId = $mainMenu ? (int)$mainMenu['id'] : 0;
$navItems   = [];
if ($mainMenu) {
    $navItems = $menuModel->getItemsWithChildren($mainMenu['id'], false);
}
$hostingNavItems = [
    [
        'title' => 'Accueil',
        'url' => BASE_URL,
        'icon' => 'fa-solid fa-house',
        'children' => [],
    ],
    [
        'title' => 'Nom de domaine',
        'url' => BASE_URL . 'domaine#domain-search',
        'icon' => 'fa-solid fa-globe',
        'children' => [
            [
                'title' => 'Recherche de domaine',
                'description' => 'Vérifiez la disponibilité de votre nom de domaine.',
                'url' => BASE_URL . 'domaine#domain-search',
            ],
            [
                'title' => 'Recherche de domaines par lot',
                'description' => 'Vérifiez plusieurs noms de domaine en une seule demande.',
                'url' => BASE_URL . 'domaine#domain-bulk-search',
            ],
            [
                'title' => 'Transfert de domaines',
                'description' => 'Transférez votre domaine et demandez son renouvellement.',
                'url' => BASE_URL . 'domaine#domain-transfer',
            ],
            [
                'title' => 'Tarifs et liste des noms de domaine',
                'description' => 'Consultez les extensions disponibles et demandez leurs tarifs.',
                'url' => BASE_URL . 'domaine#domain-pricing',
            ],
            [
                'title' => 'Whois check',
                'description' => 'Consultez les informations publiques d’un nom de domaine.',
                'url' => 'https://whois.octenium.com/',
                'external' => true,
            ],
            [
                'title' => 'Gestion de domaine',
                'description' => 'Gestion DNS, serveurs DNS et enregistrements A, CNAME, SPF, DKIM, AAAA.',
                'url' => BASE_URL . 'domaine#domain-management',
            ],
        ],
    ],
    [
        'title' => 'Hébergement Web',
        'url' => BASE_URL . 'services#hebergement-web',
        'icon' => 'fa-solid fa-server',
        'children' => [
            [
                'title' => 'Hébergement Web Mutualisé',
                'description' => 'Hébergez votre site avec e-mail, contacts et agendas.',
                'url' => BASE_URL . 'services#hebergement-mutualise',
            ],
            [
                'title' => 'Hébergement localisé en Algérie',
                'description' => 'Hébergez votre site sur des serveurs installés physiquement en Algérie.',
                'url' => BASE_URL . 'services#hebergement-algerie',
            ],
            [
                'title' => 'Serveur dédié VPS',
                'description' => 'Serveur Virtuel Privé.',
                'url' => BASE_URL . 'services#serveur-vps',
            ],
            [
                'title' => 'Serveur dédié Virtuel PRO',
                'description' => 'Puissance, flexibilité, licence WHM/cPanel incluse.',
                'url' => BASE_URL . 'services#serveur-dedie',
            ],
            [
                'title' => 'Serveurs VPS localisés en Algérie',
                'description' => 'Des serveurs VPS installés en Algérie.',
                'url' => BASE_URL . 'services#serveur-vps-algerie',
            ],
        ],
    ],
    ['title' => 'Mail', 'url' => BASE_URL . 'services#email', 'icon' => 'fa-solid fa-envelope', 'children' => []],
    ['title' => 'Revendeur', 'url' => BASE_URL . 'services#revendeur', 'icon' => 'fa-solid fa-people-arrows-left-right', 'children' => []],
];
$displayMenuItems = $isHostingTheme ? $hostingNavItems : $menuItems;
?>
<nav class="welcome-header navbar navbar-expand-<?php echo $isHostingTheme ? 'xxl' : 'lg'; ?> navbar-light bg-white shadow-sm sticky-top">
    <?php if ($isHostingTheme): ?>
    <div class="hosting-nav-topbar">
        <div class="container hosting-nav-topbar__inner">
            <span>Hébergement web, domaines & solutions numériques</span>
            <a href="<?php echo BASE_URL; ?>contact">Besoin d’aide ? Contactez-nous</a>
        </div>
    </div>
    <?php endif; ?>
    <div class="container<?php echo $isHostingTheme ? ' hosting-nav-main' : ''; ?>">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Ouvrir le menu de navigation" aria-controls="navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>" aria-label="<?php echo htmlspecialchars($siteName); ?> — Retour à l'accueil">
            <?php if (!empty($siteLogo)): ?>
                <img src="<?php echo BASE_URL . ltrim($siteLogo, '/'); ?>" alt="<?php echo htmlspecialchars($siteName); ?>" data-ie-logo style="height:40px;width:auto;">
            <?php else: ?>
                <span data-ie-logo class="d-inline-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;background:#315efb;color:#fff;font-weight:900;font-size:1.1rem;border-radius:12px;"><?php echo htmlspecialchars($siteInitial); ?></span>
            <?php endif; ?>
            <span data-ie-setting="site_name" style="<?php echo htmlspecialchars($brandStyle, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $brandInner; ?></span>
            <?php if ($isAdmin): ?>
            <span data-ie-color-edit class="ie-color-btn" title="Changer les couleurs du logo" aria-label="Changer les couleurs du logo" role="button" tabindex="0">
                <i class="fas fa-palette"></i>
            </span>
            <?php endif; ?>
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto align-items-center" id="main-menu-nav">
                <?php foreach ($displayMenuItems as $item):
                    $hasChildren = !empty($item['children']);
                    $isDomainNavItem = $isHostingTheme && ($item['title'] ?? '') === 'Nom de domaine';
                    $params = !empty($item['params']) ? json_decode($item['params'], true) : [];
                    $params = is_array($params) ? $params : [];
                    $showIcon = !isset($params['icon']) || (bool)$params['icon'];
                    $icon = ($isHostingTheme || $showIcon) && !empty($item['icon'])
                        ? htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8')
                        : '';
                ?>
                <li class="nav-item<?php echo $hasChildren ? ' dropdown' : ''; ?><?php echo $isDomainNavItem ? ' hosting-nav-item--domain' : ''; ?><?php echo $isAdmin && !$isHostingTheme ? ' ie-menu-editable' : ''; ?>"
                    <?php if (!$isHostingTheme): ?>
                    data-ie-menu-item="<?php echo (int)$item['id']; ?>"
                    data-ie-menu-title="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-ie-menu-url="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-ie-menu-icon="<?php echo htmlspecialchars($item['icon'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    data-ie-menu-position="<?php echo (int)$item['position']; ?>"
                    data-ie-menu-parent="<?php echo (int)($item['parent_id'] ?? 0); ?>"
                    data-ie-menu-active="<?php echo (int)$item['active']; ?>"
                    data-ie-menu-showicon="<?php echo (int)$showIcon; ?>"
                    <?php endif; ?>>
                    <a class="nav-link<?php echo $hasChildren && !$isDomainNavItem ? ' dropdown-toggle' : ''; ?>"
                       href="<?php echo $isHostingTheme ? htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') : BASE_URL . ltrim($item['url'], '/'); ?>"
                       <?php echo $hasChildren && !$isDomainNavItem ? 'role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"' : ''; ?>>
                        <?php if ($icon && !$isHostingTheme): ?><i class="<?php echo $icon; ?> me-1"></i><?php endif; ?>
                        <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <?php if ($isDomainNavItem): ?>
                    <button class="hosting-nav-submenu-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Afficher les services de domaine" aria-expanded="false"></button>
                    <?php endif; ?>
                    <?php if ($isAdmin && !$isHostingTheme): ?>
                    <div class="ie-menu-actions">
                        <button type="button" class="ie-menu-btn" data-ie-menu-edit title="Modifier" aria-label="Modifier <?php echo htmlspecialchars($item['title']); ?>"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
                        <button type="button" class="ie-menu-btn danger" data-ie-menu-delete title="Supprimer" aria-label="Supprimer <?php echo htmlspecialchars($item['title']); ?>"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                    </div>
                    <?php endif; ?>
                    <?php if ($hasChildren): ?>
                    <ul class="dropdown-menu hosting-nav-dropdown">
                        <?php foreach ($item['children'] as $child):
                            $childParams = !empty($child['params']) ? json_decode($child['params'], true) : [];
                            $childParams = is_array($childParams) ? $childParams : [];
                            $childShowIcon = !isset($childParams['icon']) || (bool)$childParams['icon'];
                            $childIcon = !empty($child['icon']) ? htmlspecialchars($child['icon'], ENT_QUOTES, 'UTF-8') : '';
                            if (!$isHostingTheme && !$childShowIcon) {
                                $childIcon = '';
                            }
                            $childTitle = htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8');
                            $childDescription = htmlspecialchars($child['description'] ?? '', ENT_QUOTES, 'UTF-8');
                            $childUrl = $isHostingTheme
                                ? (string)$child['url']
                                : BASE_URL . ltrim($child['url'], '/');
                        ?>
                        <li<?php if (!$isHostingTheme): ?> class="<?php echo $isAdmin ? 'ie-menu-editable' : ''; ?>"
                            data-ie-menu-item="<?php echo (int)$child['id']; ?>"
                            data-ie-menu-title="<?php echo htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-ie-menu-url="<?php echo htmlspecialchars($child['url'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-ie-menu-icon="<?php echo htmlspecialchars($child['icon'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            data-ie-menu-position="<?php echo (int)$child['position']; ?>"
                            data-ie-menu-parent="<?php echo (int)($child['parent_id'] ?? 0); ?>"
                            data-ie-menu-active="<?php echo (int)$child['active']; ?>"
                            data-ie-menu-showicon="<?php echo (int)$childShowIcon; ?>"<?php endif; ?>>
                            <a class="dropdown-item<?php echo $isHostingTheme && $childDescription !== '' ? ' hosting-nav-dropdown__item' : ''; ?>"
                               href="<?php echo htmlspecialchars($childUrl, ENT_QUOTES, 'UTF-8'); ?>"
                               <?php echo !empty($child['external']) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                <?php if (!$isHostingTheme && $childIcon): ?><i class="<?php echo $childIcon; ?> me-2" aria-hidden="true"></i><?php endif; ?>
                                <?php if ($isHostingTheme && $childDescription !== ''): ?>
                                <span class="hosting-nav-dropdown__copy">
                                    <strong><?php echo $childTitle; ?></strong>
                                    <small><?php echo $childDescription; ?></small>
                                </span>
                                <?php else: ?>
                                <?php echo $childTitle; ?>
                                <?php endif; ?>
                            </a>
                            <?php if ($isAdmin && !$isHostingTheme): ?>
                            <div class="ie-menu-actions">
                                <button type="button" class="ie-menu-btn" data-ie-menu-edit title="Modifier" aria-label="Modifier <?php echo htmlspecialchars($child['title']); ?>"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
                                <button type="button" class="ie-menu-btn danger" data-ie-menu-delete title="Supprimer" aria-label="Supprimer <?php echo htmlspecialchars($child['title']); ?>"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <?php if ($isAdmin && !$isHostingTheme): ?>
                <li class="nav-item nav-ie-menu-add">
                    <button type="button" class="ie-menu-add" data-ie-menu-add title="Ajouter un élément au menu" aria-label="Ajouter un élément au menu">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i><span>Ajouter</span>
                    </button>
                </li>
                <?php endif; ?>
            </ul>
            <?php if ($isHostingTheme): ?>
            <?php endif; ?>
            <div class="navbar-right ms-lg-3<?php echo $isHostingTheme ? ' hosting-account-actions' : ''; ?>">
                <?php if (isset($_SESSION['admin_id'])): ?>
                <?php if ($isHostingTheme): ?>
                <a class="hosting-account-link" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                    <span>Administration</span>
                </a>
                <?php else: ?>
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                    <i class="fas fa-cog me-1"></i>Administration
                </a>
                <?php endif; ?>
                <?php else: ?>
                <?php if ($isHostingTheme): ?>
                <a class="hosting-account-link hosting-account-link--portal" href="https://cloud.baramidj.net/">
                    <span>Espace client</span>
                </a>
                <a class="hosting-account-link" href="<?php echo BASE_URL; ?>login.php">
                    <span>Connexion</span>
                </a>
                <?php else: ?>
                <a class="nav-link welcome-login-link" href="<?php echo BASE_URL; ?>login.php">
                    <i class="fas fa-lock me-1"></i>Connexion
                </a>
                <?php endif; ?>
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