<?php
require_once dirname(__DIR__, 3) . '/includes/shortcodes.php';
require_once dirname(__DIR__) . '/includes/content.php';
$isAdmin = isLoggedIn();
$storedTitle = trim((string)($page['title'] ?? ''));
$storedMetaTitle = trim((string)($page['meta_title'] ?? ''));
$storedMetaDescription = trim((string)($page['meta_description'] ?? ''));
$pageBody = trim((string)($page['body'] ?? ''));
$hasLegacyTitle = welcome_is_legacy_content($storedTitle);
$hasLegacyBody = welcome_is_legacy_content($pageBody);
$pageTitle = $storedTitle !== '' && !$hasLegacyTitle ? $storedTitle : 'Bienvenue sur votre site';
$metaTitle = $storedMetaTitle !== '' && !welcome_is_legacy_content($storedMetaTitle)
    ? $storedMetaTitle
    : $pageTitle;
$metaDescription = $storedMetaDescription !== '' && !welcome_is_legacy_content($storedMetaDescription)
    ? $storedMetaDescription
    : 'Bienvenue sur votre site. Personnalisez-le pour présenter votre activité.';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <title><?php echo htmlspecialchars($metaTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <?php if ($isAdmin): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
    <meta name="page-slug" content="<?php echo htmlspecialchars($page['slug'] ?? 'home'); ?>">
    <meta name="base-url" content="<?php echo htmlspecialchars(BASE_URL); ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    <link href="<?php echo theme_url('assets/css/theme.css'); ?>" rel="stylesheet">
    <?php if ($isAdmin): ?>
    <link href="<?php echo BASE_URL; ?>assets/css/inline-edit.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <?php theme_partial('navbar'); ?>
    <main id="bienvenue" class="welcome-home"<?php if ($isAdmin): ?> data-inline-field="body"<?php endif; ?>>
        <?php if ($pageBody !== '' && !$hasLegacyBody): ?>
            <?php echo do_shortcode($pageBody, $pdo); ?>
        <?php else: ?>
            <h1>
                <?php echo htmlspecialchars($pageTitle); ?>
            </h1>
            <section class="welcome-hero" aria-labelledby="welcome-title">
                <div class="welcome-hero__copy">
                    <span class="welcome-eyebrow">Votre espace, votre identité</span>
                    <h2 id="welcome-title">Créez un site à votre image</h2>
                    <p>Présentez votre activité, partagez vos idées et faites découvrir vos services. Personnalisez cette page avec vos propres textes et images.</p>
                    <a class="welcome-hero__link" href="#decouvrir">Découvrir votre site <span aria-hidden="true">→</span></a>
                </div>
                <div class="welcome-hero__visual">
                    <img src="<?php echo theme_url('assets/images/site-illustration.svg'); ?>" alt="Illustration d'un site web personnalisable">
                </div>
            </section>
            <section id="decouvrir" class="welcome-intro">
                <span class="welcome-eyebrow">Tout commence ici</span>
                <h2>Votre contenu, vos règles</h2>
                <p>Ajoutez vos pages, modifiez les textes et choisissez les images qui racontent votre histoire. Chaque élément est là pour être adapté à votre projet.</p>
            </section>
            <section class="welcome-cards" aria-label="Idées pour personnaliser votre site">
                <article class="welcome-card">
                    <span class="welcome-card__number">01</span>
                    <h3>Présentez votre activité</h3>
                    <p>Expliquez simplement qui vous êtes et ce que vous proposez.</p>
                </article>
                <article class="welcome-card">
                    <span class="welcome-card__number">02</span>
                    <h3>Ajoutez vos pages</h3>
                    <p>Créez des pages pour partager vos projets, vos services ou vos actualités.</p>
                </article>
                <article class="welcome-card">
                    <span class="welcome-card__number">03</span>
                    <h3>Choisissez vos images</h3>
                    <p>Remplacez cette illustration par vos propres photos et visuels.</p>
                </article>
            </section>
        <?php endif; ?>
    </main>
    <?php theme_partial('footer'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <script src="<?php echo theme_url('assets/js/maokaa.js'); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.splide:not(.is-initialized)').forEach(function (el) {
            if (el.classList.contains('brands-carousel')) {
                new Splide(el, {
                    type: 'loop', perPage: 5, perMove: 1, autoplay: true,
                    interval: 3000, pauseOnHover: true, gap: '24px',
                    breakpoints: { 992: { perPage: 3 }, 576: { perPage: 2 } },
                    pagination: false, arrows: true
                }).mount();
            } else {
                new Splide(el, {
                    type: 'fade', autoplay: true, interval: 4000,
                    pauseOnHover: true, rewind: true, cover: true, heightRatio: 0.4
                }).mount();
            }
        });
    });
    </script>
    <?php if ($isAdmin): ?>
    <script src="<?php echo BASE_URL; ?>assets/js/inline-edit.js"></script>
    <?php endif; ?>
</body>
</html>
