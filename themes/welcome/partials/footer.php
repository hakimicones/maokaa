<?php
// Charger les helpers settings avant toute utilisation (nécessaire pour get_setting)
require_once dirname(__DIR__) . '/includes/content.php';
if (!function_exists('get_setting')) {
    require_once dirname(__DIR__, 3) . '/includes/settings_helpers.php';
}
$footerAdmin = function_exists('isLoggedIn') && isLoggedIn();
$siteName = get_setting($pdo, 'site_name', 'Votre site');
$siteName = welcome_is_legacy_content($siteName) ? 'Votre site' : $siteName;
$siteName = str_replace("\xC2\xA0", ' ', html_entity_decode($siteName, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
$siteLogo = get_setting($pdo, 'site_logo', '');
$siteLogo = welcome_is_legacy_content($siteLogo) ? '' : $siteLogo;
$nameColor = get_setting($pdo, 'site_name_color', '#202533');
$accentColor = get_setting($pdo, 'site_name_color_accent', '#315efb');
$brandFont = get_setting($pdo, 'site_name_font_family', '');
$brandSize = get_setting($pdo, 'site_name_font_size', '');
$brandBold = get_setting($pdo, 'site_name_bold', '1');
$brandItalic = get_setting($pdo, 'site_name_italic', '0');
$brandUnderline = get_setting($pdo, 'site_name_underline', '0');
$brandStyle = 'color:' . $nameColor . ';'
    . ($brandFont !== '' ? 'font-family:' . $brandFont . ';' : '')
    . ($brandSize !== '' ? 'font-size:' . $brandSize . ';' : '')
    . 'font-weight:' . ($brandBold === '1' ? '700' : '400') . ';'
    . 'font-style:' . ($brandItalic === '1' ? 'italic' : 'normal') . ';'
    . 'text-decoration:' . ($brandUnderline === '1' ? 'underline' : 'none') . ';';
$siteInitial = function_exists('mb_substr') ? mb_substr(trim($siteName), 0, 1) : substr(trim($siteName), 0, 1);
$nameParts = explode(' ', trim($siteName), 2);

$brandHtml = get_setting($pdo, 'site_name_html', '');
if ($brandHtml !== '' && !welcome_is_legacy_content($brandHtml)) {
    $brandInner = sanitize_brand_html($brandHtml);
} else {
    $brandInner = htmlspecialchars($nameParts[0], ENT_QUOTES, 'UTF-8');
    if (isset($nameParts[1]) && $nameParts[1] !== '') {
        $brandInner .= ' <span style="color:' . htmlspecialchars($accentColor, ENT_QUOTES, 'UTF-8') . ';">' . htmlspecialchars($nameParts[1], ENT_QUOTES, 'UTF-8') . '</span>';
    }
}

$footerColumnsSetting = get_setting($pdo, 'footer_columns', '');
$footerColumns = json_decode($footerColumnsSetting, true);
$storyPage = $pdo->prepare(
    "SELECT slug FROM content WHERE slug IN ('notre-histoire', 'histoire', 'our-story', 'history') AND status = 'published' LIMIT 1"
);
$storyPage->execute();
$storySlug = $storyPage->fetchColumn();
if (
    !is_array($footerColumns)
    || empty($footerColumns)
    || welcome_is_legacy_content($footerColumnsSetting)
) {
    $footerColumns = [
        ['title' => 'Navigation', 'links' => [
            ['label' => 'Accueil', 'url' => '/'],
            ['label' => 'À propos', 'url' => 'about'],
        ]],
        ['title' => 'À découvrir', 'links' => [
            ['label' => 'Nos solutions', 'url' => 'solutions'],
        ]],
    ];
    if ($storySlug) {
        $footerColumns[1]['links'][] = ['label' => 'Notre histoire', 'url' => $storySlug];
    }
}
$footerDescription = get_setting($pdo, 'footer_description', '');
if ($footerDescription === '' || welcome_is_legacy_content($footerDescription)) {
    $footerDescription = 'Présentez votre activité et donnez à vos visiteurs une raison d’en savoir plus.';
}
$footerEmail = get_setting($pdo, 'footer_email', '');
$footerPhone = get_setting($pdo, 'footer_phone', '');
if (preg_match('/(?:vep|noor)/i', $footerEmail . ' ' . $footerPhone)) {
    $footerEmail = '';
    $footerPhone = '';
}
$footerCopyright = get_setting($pdo, 'footer_copyright', 'Tous droits réservés.');
if (welcome_is_legacy_content($footerCopyright)) {
    $footerCopyright = 'Tous droits réservés.';
}
?>
<footer class="welcome-footer" style="background:#fff; color:#657187; padding:2rem 0 0; border-top:1px solid #eceff3;">
    <div class="container">
        <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:2rem; padding-bottom:2rem; border-bottom:1px solid #eceff3;" data-footer-cols='<?php echo htmlspecialchars(json_encode($footerColumns, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8'); ?>'>
            <div>
                <a href="<?php echo BASE_URL; ?>" class="d-flex align-items-center gap-2 text-decoration-none mb-3" aria-label="<?php echo htmlspecialchars($siteName); ?> — Retour à l'accueil" style="color:#202533; font-size:1.4rem; font-weight:700;">
                    <?php if (!empty($siteLogo)): ?>
                        <img src="<?php echo BASE_URL . ltrim($siteLogo, '/'); ?>" alt="<?php echo htmlspecialchars($siteName); ?>" data-ie-logo style="height:40px;width:auto;border-radius:12px;">
                    <?php else: ?>
                        <span data-ie-logo class="d-inline-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;background:#315efb;color:#fff;font-weight:900;font-size:1rem;border-radius:12px;"><?php echo htmlspecialchars($siteInitial); ?></span>
                    <?php endif; ?>
                    <span data-ie-setting="site_name" style="<?php echo htmlspecialchars($brandStyle, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $brandInner; ?></span>
                    <?php if ($footerAdmin): ?>
                    <span data-ie-color-edit class="ie-color-btn" title="Changer les couleurs du logo" aria-label="Changer les couleurs du logo" role="button" tabindex="0">
                        <i class="fas fa-palette"></i>
                    </span>
                    <?php endif; ?>
                </a>
                <p data-ie-setting="footer_description" style="font-size:0.95rem; color:#596579; margin-top:0.75rem; line-height:1.7;">
                    <?php echo htmlspecialchars($footerDescription); ?>
                </p>
            </div>
            <?php foreach ($footerColumns as $fcIndex => $fc):
                $fcTitle = $fc['title'] ?? '';
                $fcLinks = isset($fc['links']) && is_array($fc['links']) ? $fc['links'] : [];
            ?>
            <div data-ie-footer-col="<?php echo $fcIndex; ?>" style="position:relative;">
                <?php if ($footerAdmin): ?>
                <button type="button" class="btn-footer-edit ie-footer-edit" data-ie-footer-edit="<?php echo $fcIndex; ?>" title="Modifier cette colonne" aria-label="Modifier la colonne <?php echo htmlspecialchars($fcTitle); ?>">✎</button>
                <?php endif; ?>
                <h5 style="font-weight:700; font-size:1rem; letter-spacing:1px; text-transform:uppercase; color:#202533; margin-bottom:1.2rem;"><?php echo htmlspecialchars($fcTitle); ?></h5>
                <ul style="list-style:none; padding:0;">
                    <?php foreach ($fcLinks as $link):
                        $linkUrl = trim($link['url'] ?? '');
                        if ($linkUrl === '') {
                            $href = '#';
                        } elseif (preg_match('#^(https?://|//|mailto:|tel:)#i', $linkUrl)) {
                            $href = $linkUrl;
                        } else {
                            $href = BASE_URL . ltrim($linkUrl, '/');
                        }
                    ?>
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo htmlspecialchars($href); ?>" style="color:#596579; font-size:0.95rem; text-decoration:none;"><?php echo htmlspecialchars($link['label'] ?? ''); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
            <?php if ($footerEmail !== '' || $footerPhone !== ''): ?>
            <div>
                <h5 style="font-weight:700; font-size:1rem; letter-spacing:1px; text-transform:uppercase; color:#202533; margin-bottom:1.2rem;">Contact</h5>
                <ul style="list-style:none; padding:0;">
                    <?php if ($footerEmail !== ''): ?>
                    <li style="margin-bottom:0.6rem;"><a href="mailto:<?php echo htmlspecialchars($footerEmail); ?>" style="color:#596579; font-size:0.95rem; text-decoration:none;" data-ie-setting="footer_email"><?php echo htmlspecialchars($footerEmail); ?></a></li>
                    <?php endif; ?>
                    <?php if ($footerPhone !== ''): ?>
                    <li style="margin-bottom:0.6rem;"><a href="tel:<?php echo htmlspecialchars($footerPhone); ?>" style="color:#596579; font-size:0.95rem; text-decoration:none;" data-ie-setting="footer_phone"><?php echo htmlspecialchars($footerPhone); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php else: ?>
            <div class="welcome-footer__contact">
                <h5 style="font-weight:700; font-size:1rem; letter-spacing:1px; text-transform:uppercase; color:#202533; margin-bottom:1.2rem;">Contact</h5>
                <p>Ajoutez vos coordonnées pour permettre à vos visiteurs de vous contacter.</p>
            </div>
            <?php endif; ?>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:1.5rem 0; font-size:0.85rem; color:#7a8495;">
            <p data-ie-setting="footer_copyright" class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($footerCopyright); ?></p>
        </div>
    </div>
</footer>

<?php if ($footerAdmin): ?>
<script src="<?php echo theme_url('assets/js/site-editing.js'); ?>"></script>
<?php endif; ?>