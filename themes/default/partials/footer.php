<?php
// Charger les helpers settings avant toute utilisation (nécessaire pour get_setting)
if (!function_exists('get_setting')) {
    require_once dirname(__DIR__, 3) . '/includes/settings_helpers.php';
}
$footerAdmin = function_exists('isLoggedIn') && isLoggedIn();
?>
<footer style="background:#0F0F1A; color:rgba(255,255,255,0.8); padding:4rem 0 0;">
    <div class="container">
        <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:3rem; padding-bottom:3rem; border-bottom:1px solid rgba(255,255,255,0.1);">
            <div>
                <a href="<?php echo BASE_URL; ?>" class="d-flex align-items-center gap-2 text-decoration-none mb-3" aria-label="Noor Guide — Retour à l'accueil" style="color:#fff; font-size:1.4rem; font-weight:700;">
                    <span class="d-inline-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;background:#FF6B00;color:#fff;font-weight:900;font-size:1rem;border-radius:12px;">N</span>
                    <span>Noor<span style="color:#FF6B00;">Guide</span></span>
                </a>
                <p data-ie-setting="footer_description" style="font-size:0.95rem; color:rgba(255,255,255,0.7); margin-top:0.75rem; line-height:1.7;">
                    <?php echo htmlspecialchars(get_setting($pdo, 'footer_description', 'Application mobile de guidage pour personnes aveugles et malvoyantes. Navigation intelligente, parcours personnalisés et détection Bluetooth.')); ?>
                </p>
            </div>
            <div>
                <h5 style="font-weight:700; font-size:1rem; letter-spacing:1px; text-transform:uppercase; color:#fff; margin-bottom:1.2rem;">Application</h5>
                <ul style="list-style:none; padding:0;">
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo BASE_URL; ?>#features" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;">Fonctionnalités</a></li>
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo BASE_URL; ?>#how-it-works" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;">Comment ça marche</a></li>
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo BASE_URL; ?>#accessibility" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;">Accessibilité</a></li>
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo BASE_URL; ?>#contact" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;">Télécharger</a></li>
                </ul>
            </div>
            <div>
                <h5 style="font-weight:700; font-size:1rem; letter-spacing:1px; text-transform:uppercase; color:#fff; margin-bottom:1.2rem;">Ressources</h5>
                <ul style="list-style:none; padding:0;">
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo BASE_URL; ?>documentation" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;">Documentation</a></li>
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo BASE_URL; ?>faq" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;">FAQ</a></li>
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo BASE_URL; ?>blog" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;">Blog</a></li>
                    <li style="margin-bottom:0.6rem;"><a href="<?php echo BASE_URL; ?>support" style="color:rgba(255,255,255,0.7); font-size:0.95rem; text-decoration:none;">Support</a></li>
                </ul>
            </div>
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
<?php $footerCsrf = function_exists('generateCSRFToken') ? generateCSRFToken() : ''; ?>
<script>
(function() {
    var csrfToken = '<?php echo htmlspecialchars($footerCsrf, ENT_QUOTES); ?>';
    var baseUrl   = '<?php echo BASE_URL; ?>';

    document.querySelectorAll('[data-ie-setting]').forEach(function(el) {
        el.setAttribute('contenteditable', 'true');
        el.classList.add('ie-field');

        var original = el.innerHTML;

        el.addEventListener('focus', function() {
            this.classList.add('ie-editing');
        });

        el.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); this.blur(); }
            if (e.key === 'Escape') { this.innerHTML = original; this.blur(); }
        });

        el.addEventListener('blur', function() {
            var self = this;
            self.classList.remove('ie-editing');
            var current = self.innerHTML;
            if (current === original) return;
            self.classList.add('ie-saving');

            var key = self.getAttribute('data-ie-setting');

            fetch(baseUrl + 'includes/inline_edit_setting.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    key: key,
                    value: current
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                self.classList.remove('ie-saving');
                if (data.success) {
                    original = current;
                    self.classList.add('ie-success');
                    setTimeout(function() { self.classList.remove('ie-success'); }, 1800);
                } else {
                    self.innerHTML = original;
                    self.classList.add('ie-error');
                    setTimeout(function() { self.classList.remove('ie-error'); }, 1800);
                }
            })
            .catch(function() {
                self.classList.remove('ie-saving');
                self.innerHTML = original;
                self.classList.add('ie-error');
                setTimeout(function() { self.classList.remove('ie-error'); }, 1800);
            });
        });
    });
})();
</script>
<?php endif; ?>