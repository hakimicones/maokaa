<?php
// Charger les helpers settings avant toute utilisation (nécessaire pour get_setting)
if (!function_exists('get_setting')) {
    require_once dirname(__DIR__, 3) . '/includes/settings_helpers.php';
}
$footerAdmin = function_exists('isLoggedIn') && isLoggedIn();
?>
<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-3">
                <h5 class="fw-bold mb-3">VEP</h5>
                <p data-ie-setting="footer_description"><?php echo htmlspecialchars(get_setting($pdo, 'footer_description', 'Votre partenaire incontournable du laboratoire en Algérie. Importation et distribution de matériels et consommables de laboratoire depuis plus de 20 ans.')); ?></p>
            </div>
            <div class="col-md-3">
                <h5 class="fw-bold mb-3">Navigation</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>" class="text-white-50 text-decoration-none">Accueil</a></li>
                    <li><a href="<?php echo BASE_URL; ?>about" class="text-white-50 text-decoration-none">À propos</a></li>
                    <li><a href="<?php echo BASE_URL; ?>products" class="text-white-50 text-decoration-none">Produits</a></li>
                    <li><a href="<?php echo BASE_URL; ?>contact" class="text-white-50 text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5 class="fw-bold mb-3">Informations</h5>
                <ul class="list-unstyled">
                    <li><a href="tel:<?php echo htmlspecialchars(get_setting($pdo, 'footer_phone', '+213123456789')); ?>" class="text-white-50 text-decoration-none" data-ie-setting="footer_phone"><i class="fas fa-phone"></i> <?php echo htmlspecialchars(get_setting($pdo, 'footer_phone', '+213 (0) 123 456 789')); ?></a></li>
                    <li><a href="mailto:<?php echo htmlspecialchars(get_setting($pdo, 'footer_email', 'contact@vep.dz')); ?>" class="text-white-50 text-decoration-none" data-ie-setting="footer_email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars(get_setting($pdo, 'footer_email', 'contact@vep.dz')); ?></a></li>
                    <li class="text-white-50" data-ie-setting="footer_address"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(get_setting($pdo, 'footer_address', 'Alger, Algérie')); ?></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5 class="fw-bold mb-3">Réseaux Sociaux</h5>
                <div class="d-flex gap-2">
                    <a href="#" class="text-white-50 text-decoration-none"><i class="fab fa-facebook fs-5"></i></a>
                    <a href="#" class="text-white-50 text-decoration-none"><i class="fab fa-twitter fs-5"></i></a>
                    <a href="#" class="text-white-50 text-decoration-none"><i class="fab fa-linkedin fs-5"></i></a>
                    <a href="#" class="text-white-50 text-decoration-none"><i class="fab fa-instagram fs-5"></i></a>
                </div>
            </div>
        </div>
        <hr class="bg-white-50">
        <div class="row">
            <div class="col-md-6">
                <p class="text-white-50 small mb-0" data-ie-setting="footer_copyright">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(get_setting($pdo, 'footer_copyright', 'VEP - Tous droits réservés.')); ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-white-50 small mb-0">
                    <a href="#" class="text-white-50 text-decoration-none">Conditions d'utilisation</a> |
                    <a href="#" class="text-white-50 text-decoration-none">Politique de confidentialité</a>
                </p>
            </div>
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
