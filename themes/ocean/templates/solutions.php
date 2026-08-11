<?php
require_once dirname(__DIR__, 3) . '/includes/shortcodes.php';
$isAdmin = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FF6B00">
    <title><?php echo htmlspecialchars($page['meta_title'] ?? 'Nos solutions — Noor Guide'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page['meta_description'] ?? 'Noor Guide — Application mobile de guidage pour personnes aveugles et malvoyantes.'); ?>">
    <?php if ($isAdmin): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
    <meta name="page-slug"  content="<?php echo htmlspecialchars($page['slug'] ?? 'solutions'); ?>">
    <meta name="base-url"   content="<?php echo htmlspecialchars(BASE_URL); ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    <link href="<?php echo theme_url('assets/css/theme.css'); ?>" rel="stylesheet">
    <?php if ($isAdmin): ?>
    <link href="<?php echo BASE_URL; ?>assets/css/inline-edit.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <?php theme_partial('navbar'); ?>

    <section class="solution-hero-section">
        <div class="container">
            <div class="solution-hero">
                <div class="solution-hero-text">
                    <h1>Application mobile <span>Noor Guide</span></h1>
                    <p>
                        L'application utilise la technologie <strong>Bluetooth</strong> pour détecter
                        automatiquement les dispositifs de guidage disponibles à proximité.
                        Après le lancement du scan, l'utilisateur sélectionne le dispositif souhaité
                        dans la liste affichée. L'application établit alors une <strong>connexion
                        Bluetooth sécurisée</strong> avec ce dernier et envoie une commande d'activation.
                    </p>
                    <p>
                        Une fois activé, le dispositif émet un <strong>signal sonore</strong> permettant
                        à l'utilisateur malvoyant ou non-voyant de le localiser facilement et de
                        s'orienter vers le point d'intérêt correspondant.
                    </p>
                    <p class="solution-app-note">
                        <i class="fas fa-user-lock"></i>
                        Un <strong>compte utilisateur</strong> est nécessaire pour activer la connexion
                        entre l'application et les dispositifs Bluetooth.
                    </p>
                </div>
                <div class="solution-image-placeholder">
                    <div class="solution-app-visual">
                        <i class="fas fa-mobile-alt"></i>
                        <div class="solution-app-signal">
                            <i class="fas fa-broadcast-tower"></i>
                        </div>
                        <i class="fas fa-volume-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="solution-steps-section">
        <div class="container">
            <h2>Comment ça fonctionne</h2>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon"><i class="fas fa-search"></i></div>
                    <h3>Détection automatique</h3>
                    <p>L'application lance un scan Bluetooth et détecte automatiquement les dispositifs de guidage à proximité.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon"><i class="fas fa-hand-pointer"></i></div>
                    <h3>Sélection du dispositif</h3>
                    <p>L'utilisateur choisit le dispositif souhaité dans la liste affichée à l'écran.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon"><i class="fas fa-link"></i></div>
                    <h3>Connexion sécurisée</h3>
                    <p>L'application établit une connexion Bluetooth sécurisée et envoie la commande d'activation.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-icon"><i class="fas fa-volume-up"></i></div>
                    <h3>Signal sonore</h3>
                    <p>Le dispositif activé émet un signal sonore pour guider l'utilisateur vers le point d'intérêt.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="solution-screenshots-section">
        <div class="container">
            <h2>Captures de l'application</h2>
            <div class="screenshots-grid">
                <div class="screenshot-item">
                    <div class="screenshot-placeholder">
                        <i class="fas fa-mobile-alt"></i>
                        <span>Scan Bluetooth</span>
                    </div>
                </div>
                <div class="screenshot-item">
                    <div class="screenshot-placeholder">
                        <i class="fas fa-list"></i>
                        <span>Liste des dispositifs</span>
                    </div>
                </div>
                <div class="screenshot-item">
                    <div class="screenshot-placeholder">
                        <i class="fas fa-check-circle"></i>
                        <span>Connexion établie</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php theme_partial('footer'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <script src="<?php echo theme_url('assets/js/maokaa.js'); ?>"></script>
    <?php if ($isAdmin): ?>
    <script src="<?php echo BASE_URL; ?>assets/js/inline-edit.js"></script>
    <?php endif; ?>
</body>
</html>
