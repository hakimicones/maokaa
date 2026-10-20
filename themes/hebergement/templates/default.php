<?php
require_once dirname(__DIR__, 3) . '/includes/shortcodes.php';
require_once dirname(__DIR__, 2) . '/welcome/includes/content.php';
$isAdmin = isLoggedIn();
$storedTitle = trim((string)($page['title'] ?? ''));
$storedBody = trim((string)($page['body'] ?? ''));
$pageTitle = $storedTitle !== '' && !welcome_is_legacy_content($storedTitle)
    ? $storedTitle
    : 'Bienvenue';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page['meta_title'] ?? $pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page['meta_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($isAdmin): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="page-slug" content="<?php echo htmlspecialchars($page['slug'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="base-url" content="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    <link href="<?php echo theme_url('assets/css/theme.css'); ?>?v=<?php echo filemtime(dirname(__DIR__) . '/assets/css/theme.css') . '-' . filesize(dirname(__DIR__) . '/assets/css/theme.css'); ?>" rel="stylesheet">
    <?php if ($isAdmin): ?>
    <link href="<?php echo BASE_URL; ?>assets/css/inline-edit.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body class="hosting-site">
    <?php theme_partial('navbar'); ?>
    <main class="hosting-page hosting-container">
        <h1<?php if ($isAdmin): ?> data-inline-field="title"<?php endif; ?>><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="hosting-page__content"<?php if ($isAdmin): ?> data-inline-field="body"<?php endif; ?>>
            <?php echo do_shortcode($storedBody, $pdo); ?>
        </div>
    </main>
    <?php theme_partial('footer'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($isAdmin): ?>
    <script src="<?php echo BASE_URL; ?>assets/js/inline-edit.js"></script>
    <?php endif; ?>
</body>
</html>
