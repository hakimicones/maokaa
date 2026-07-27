<?php
require_once dirname(__DIR__, 3) . '/includes/shortcodes.php';
$isAdmin = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page['meta_title'] ?? $page['title'] ?? 'VEP'); ?> - VEP</title>
    <meta name="description" content="<?php echo htmlspecialchars($page['meta_description'] ?? ''); ?>">
    <?php if ($isAdmin): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
    <meta name="page-slug"  content="<?php echo htmlspecialchars($page['slug'] ?? ''); ?>">
    <meta name="base-url"   content="<?php echo htmlspecialchars(BASE_URL); ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    <link href="<?php echo theme_url('assets/css/theme.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    <?php if ($isAdmin): ?>
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/inline-edit.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <?php theme_partial('navbar'); ?>
    <main class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 mx-auto">
                    <h1 class="display-6 mb-3 page"
                        <?php if ($isAdmin): ?>data-inline-field="title"<?php endif; ?>>
                       
                    </h1>
                    <?php if (!empty($page['subtitle'])): ?>
                    <p class="lead text-muted mb-4"
                       <?php if ($isAdmin): ?>data-inline-field="subtitle"<?php endif; ?>>
                        <?php echo htmlspecialchars($page['subtitle']); ?>
                    </p>
                    <?php endif; ?>
                    <div class="page-content"
                         <?php if ($isAdmin): ?>data-inline-field="body"<?php endif; ?>>
                        <?php echo do_shortcode($page['body'] ?? '', $pdo); ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php theme_partial('footer'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
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
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/inline-edit.js"></script>
    <?php endif; ?>
</body>
</html>
