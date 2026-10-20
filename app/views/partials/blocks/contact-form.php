<?php
// $blockTitle = optional heading, $formSent = bool, $formError = string, $formData = array
$csrfToken = function_exists('generateCSRFToken') ? generateCSRFToken() : '';
$siteKey = defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '';
?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <?php if (!empty($blockTitle)): ?>
                    <h2 class="section-title"><?php echo htmlspecialchars($blockTitle); ?></h2>
                <?php else: ?>
                    <h2 class="section-title">Contactez-nous</h2>
                <?php endif; ?>

                <?php if ($formSent): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.
                    </div>
                <?php else: ?>

                    <?php if (!empty($domainSearchRequest)): ?>
                        <div class="alert alert-info">
                            Vous demandez la vérification de disponibilité de <strong><?php echo htmlspecialchars($domainSearchRequest, ENT_QUOTES, 'UTF-8'); ?></strong>.
                            Notre équipe confirmera le résultat après réception du formulaire.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($formError)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($formError); ?></div>
                    <?php endif; ?>

                    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($siteKey); ?>"></script>
                    <form method="POST" id="contact-form-recaptcha">
                        <input type="hidden" name="vep_contact_form" value="1">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="g-recaptcha-response" id="recaptcha-response" value="">
                        <div class="mb-3">
                            <label class="form-label">Nom complet *</label>
                            <input type="text" name="contact_nom" class="form-control"
                                   value="<?php echo htmlspecialchars($formData['nom'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="contact_email" class="form-control"
                                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="contact_telephone" class="form-control"
                                   value="<?php echo htmlspecialchars($formData['telephone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sujet</label>
                            <input type="text" name="contact_sujet" class="form-control"
                                   value="<?php echo htmlspecialchars($formData['sujet'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message *</label>
                            <textarea name="contact_message" class="form-control" rows="5" required><?php echo htmlspecialchars($formData['message'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" id="contact-submit-btn">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                        </button>
                    </form>
                    <script>
                    document.getElementById('contact-form-recaptcha').addEventListener('submit', function(e) {
                        e.preventDefault();
                        var btn = document.getElementById('contact-submit-btn');
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Vérification...';
                        grecaptcha.ready(function() {
                            grecaptcha.execute('<?php echo htmlspecialchars($siteKey, ENT_QUOTES); ?>', {action: 'submit'}).then(function(token) {
                                document.getElementById('recaptcha-response').value = token;
                                e.target.submit();
                            });
                        });
                    });
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
