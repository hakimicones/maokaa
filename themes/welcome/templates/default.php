<?php
require_once dirname(__DIR__, 3) . '/includes/shortcodes.php';
require_once dirname(__DIR__) . '/includes/content.php';
$isAdmin = isLoggedIn();
$pageSlug = strtolower(trim((string)($page['slug'] ?? '')));
$pagePresets = [
    'about' => [
        'title' => 'À propos',
        'heading' => 'Présentez votre activité',
        'body' => 'Parlez de votre projet, de votre équipe ou de ce qui rend votre activité unique. Ce texte est un point de départ : cliquez dessus pour le remplacer par vos propres mots.',
    ],
    'a-propos' => [
        'title' => 'À propos',
        'heading' => 'Présentez votre activité',
        'body' => 'Parlez de votre projet, de votre équipe ou de ce qui rend votre activité unique. Ce texte est un point de départ : cliquez dessus pour le remplacer par vos propres mots.',
    ],
    'solutions' => [
        'title' => 'Nos solutions',
        'heading' => 'Ce que nous proposons',
        'body' => 'Présentez ici vos services, vos produits ou les solutions que vous proposez. Ajoutez des détails utiles pour vos visiteurs et illustrez-les avec vos images.',
    ],
    'nos-solutions' => [
        'title' => 'Nos solutions',
        'heading' => 'Ce que nous proposons',
        'body' => 'Présentez ici vos services, vos produits ou les solutions que vous proposez. Ajoutez des détails utiles pour vos visiteurs et illustrez-les avec vos images.',
    ],
    'histoire' => [
        'title' => 'Notre histoire',
        'heading' => 'Une histoire à raconter',
        'body' => 'Partagez les débuts de votre projet, les étapes importantes et les personnes qui le font vivre. Remplacez ce texte par votre propre histoire.',
    ],
    'notre-histoire' => [
        'title' => 'Notre histoire',
        'heading' => 'Une histoire à raconter',
        'body' => 'Partagez les débuts de votre projet, les étapes importantes et les personnes qui le font vivre. Remplacez ce texte par votre propre histoire.',
    ],
    'our-story' => [
        'title' => 'Notre histoire',
        'heading' => 'Une histoire à raconter',
        'body' => 'Partagez les débuts de votre projet, les étapes importantes et les personnes qui le font vivre. Remplacez ce texte par votre propre histoire.',
    ],
    'history' => [
        'title' => 'Notre histoire',
        'heading' => 'Une histoire à raconter',
        'body' => 'Partagez les débuts de votre projet, les étapes importantes et les personnes qui le font vivre. Remplacez ce texte par votre propre histoire.',
    ],
    'notre_histoire' => [
        'title' => 'Notre histoire',
        'heading' => 'Une histoire à raconter',
        'body' => 'Partagez les débuts de votre projet, les étapes importantes et les personnes qui le font vivre. Remplacez ce texte par votre propre histoire.',
    ],
    'our-history' => [
        'title' => 'Notre histoire',
        'heading' => 'Une histoire à raconter',
        'body' => 'Partagez les débuts de votre projet, les étapes importantes et les personnes qui le font vivre. Remplacez ce texte par votre propre histoire.',
    ],
    'about-us' => [
        'title' => 'À propos',
        'heading' => 'Présentez votre activité',
        'body' => 'Parlez de votre projet, de votre équipe ou de ce qui rend votre activité unique. Ce texte est un point de départ : cliquez dessus pour le remplacer par vos propres mots.',
    ],
];
$preset = $pagePresets[$pageSlug] ?? [
    'title' => 'Bienvenue sur cette page',
    'heading' => 'Ajoutez votre contenu',
    'body' => 'Personnalisez cette page avec vos textes, vos images et les informations que vous souhaitez partager.',
];
$storedTitle = trim((string)($page['title'] ?? ''));
$storedMetaTitle = trim((string)($page['meta_title'] ?? ''));
$storedMetaDescription = trim((string)($page['meta_description'] ?? ''));
$storedBody = trim((string)($page['body'] ?? ''));
$pageTitle = $storedTitle !== '' && !welcome_is_legacy_content($storedTitle) ? $storedTitle : $preset['title'];
$metaTitle = $storedMetaTitle !== '' && !welcome_is_legacy_content($storedMetaTitle) ? $storedMetaTitle : $pageTitle;
$metaDescription = $storedMetaDescription !== '' && !welcome_is_legacy_content($storedMetaDescription)
    ? $storedMetaDescription
    : 'Personnalisez cette page avec le contenu de votre site.';
$hasStarterBody = $storedBody === '' || welcome_is_legacy_content($storedBody);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($metaTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <?php if ($isAdmin): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
    <meta name="page-slug" content="<?php echo htmlspecialchars($page['slug'] ?? ''); ?>">
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
    <main class="welcome-page">
        <h1<?php if ($isAdmin): ?> data-inline-field="title"<?php endif; ?>>
            <?php echo htmlspecialchars($pageTitle); ?>
        </h1>
        <div class="welcome-page__content"<?php if ($isAdmin): ?> data-inline-field="body"<?php endif; ?>>
            <?php if ($hasStarterBody): ?>
            <section class="welcome-page__intro">
                <div>
                    <span class="welcome-eyebrow">Votre site, votre histoire</span>
                    <h2><?php echo htmlspecialchars($preset['heading']); ?></h2>
                    <p><?php echo htmlspecialchars($preset['body']); ?></p>
                </div>
                <img src="<?php echo theme_url('assets/images/site-illustration.svg'); ?>" alt="Illustration à remplacer par une image de votre choix">
            </section>
            <?php else: ?>
                <?php echo do_shortcode($storedBody, $pdo); ?>
            <?php endif; ?>
        </div>
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
