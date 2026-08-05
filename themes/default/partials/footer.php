<?php
// Charger les helpers settings avant toute utilisation (nécessaire pour get_setting)
if (!function_exists('get_setting')) {
    require_once dirname(__DIR__, 3) . '/includes/settings_helpers.php';
}
$footerAdmin = function_exists('isLoggedIn') && isLoggedIn();
$siteName = get_setting($pdo, 'site_name', 'Noor Guide');
$siteName = str_replace("\xC2\xA0", ' ', html_entity_decode($siteName, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
$siteLogo = get_setting($pdo, 'site_logo', '');
$nameColor = get_setting($pdo, 'site_name_color', '#1A1A2E');
$accentColor = get_setting($pdo, 'site_name_color_accent', '#FF6B00');
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
?>
<footer style="background:#0F0F1A; color:rgba(255,255,255,0.8); padding:4rem 0 0;">
    <div class="container">
        <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:3rem; padding-bottom:3rem; border-bottom:1px solid rgba(255,255,255,0.1);" data-footer-cols='<?php echo htmlspecialchars(json_encode($footerColumns, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS), ENT_QUOTES, 'UTF-8'); ?>'>
            <div>
                <a href="<?php echo BASE_URL; ?>" class="d-flex align-items-center gap-2 text-decoration-none mb-3" aria-label="<?php echo htmlspecialchars($siteName); ?> — Retour à l'accueil" style="color:#fff; font-size:1.4rem; font-weight:700;">
                    <?php if (!empty($siteLogo)): ?>
                        <img src="<?php echo BASE_URL . ltrim($siteLogo, '/'); ?>" alt="<?php echo htmlspecialchars($siteName); ?>" data-ie-logo style="height:40px;width:auto;border-radius:12px;">
                    <?php else: ?>
                        <span data-ie-logo class="d-inline-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;background:#FF6B00;color:#fff;font-weight:900;font-size:1rem;border-radius:12px;"><?php echo htmlspecialchars($siteInitial); ?></span>
                    <?php endif; ?>
                    <span data-ie-setting="site_name" style="<?php echo htmlspecialchars($brandStyle, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($nameParts[0]); ?><?php if (isset($nameParts[1]) && $nameParts[1] !== ''): ?><span style="color:<?php echo htmlspecialchars($accentColor, ENT_QUOTES, 'UTF-8'); ?>;"> <?php echo htmlspecialchars($nameParts[1]); ?></span><?php endif; ?></span>
                    <?php if ($footerAdmin): ?>
                    <span data-ie-color-edit class="ie-color-btn" title="Changer les couleurs du logo" aria-label="Changer les couleurs du logo" role="button" tabindex="0">
                        <i class="fas fa-palette"></i>
                    </span>
                    <?php endif; ?>
                </a>
                <p data-ie-setting="footer_description" style="font-size:0.95rem; color:rgba(255,255,255,0.7); margin-top:0.75rem; line-height:1.7;">
                    <?php echo htmlspecialchars(get_setting($pdo, 'footer_description', 'Application mobile de guidage pour personnes aveugles et malvoyantes. Navigation intelligente, parcours personnalisés et détection Bluetooth.')); ?>
                </p>
            </div>
            <?php foreach ($footerColumns as $fcIndex => $fc):
                $fcTitle = $fc['title'] ?? '';
                $fcLinks = isset($fc['links']) && is_array($fc['links']) ? $fc['links'] : [];
            ?>
            <div data-ie-footer-col="<?php echo $fcIndex; ?>" style="position:relative;">
                <?php if ($footerAdmin): ?>
                <button type="button" class="ie-footer-edit" data-ie-footer-edit="<?php echo $fcIndex; ?>" title="Modifier cette colonne" aria-label="Modifier la colonne <?php echo htmlspecialchars($fcTitle); ?>">✎</button>
                <?php endif; ?>
                <h5 style="font-weight:700; font-size:1rem; letter-spacing:1px; text-transform:uppercase; color:#fff; margin-bottom:1.2rem;"><?php echo htmlspecialchars($fcTitle); ?></h5>
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
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo htmlspecialchars($href); ?>" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;"><?php echo htmlspecialchars($link['label'] ?? ''); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
            <div>
                <h5 style="font-weight:700; font-size:1rem; letter-spacing:1px; text-transform:uppercase; color:#fff; margin-bottom:1.2rem;">Contact</h5>
                <ul style="list-style:none; padding:0;">
                    <li style="margin-bottom:0.6rem;"><a href="mailto:<?php echo htmlspecialchars(get_setting($pdo, 'footer_email', 'contact@noorguide.com')); ?>" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;" data-ie-setting="footer_email"><?php echo htmlspecialchars(get_setting($pdo, 'footer_email', 'contact@noorguide.com')); ?></a></li>
                    <li style="margin-bottom:0.6rem;"><a href="tel:<?php echo htmlspecialchars(get_setting($pdo, 'footer_phone', '+33123456789')); ?>" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;" data-ie-setting="footer_phone"><?php echo htmlspecialchars(get_setting($pdo, 'footer_phone', '+33 (0)1 23 45 67 89')); ?></a></li>
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo BASE_URL; ?>contact" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;">Formulaire de contact</a></li>
                </ul>
            </div>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:1.5rem 0; font-size:0.85rem; color:rgba(255,255,255,0.5);">
            <p data-ie-setting="footer_copyright" class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(get_setting($pdo, 'footer_copyright', 'Noor Guide — Tous droits réservés.')); ?></p>
            <nav aria-label="Liens légaux" class="d-flex gap-2">
                <a href="#" style="color:rgba(255,255,255,0.5); text-decoration:none;">Mentions légales</a>
                <span>&middot;</span>
                <a href="#" style="color:rgba(255,255,255,0.5); text-decoration:none;">Politique de confidentialité</a>
            </nav>
        </div>
    </div>
</footer>

<?php if ($footerAdmin): ?>
<script src="<?php echo theme_url('assets/js/site-editing.js'); ?>"></script>
<?php endif; ?>