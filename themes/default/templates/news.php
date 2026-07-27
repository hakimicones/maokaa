<?php
require_once dirname(__DIR__, 3) . '/includes/shortcodes.php';
require_once dirname(__DIR__, 3) . '/app/models/News.php';

$isAdmin = isLoggedIn();
$newsId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$newsModel = new News($pdo);

if ($newsId > 0) {
    $article = $newsModel->getById($newsId);
    if (!$article || $article['status'] !== 'published') {
        http_response_code(404);
        include dirname(__DIR__, 3) . '/app/views/errors/404.php';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($newsId > 0): ?>
    <title><?php echo htmlspecialchars($article['title']); ?> - VEP</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($article['content'] ?? ''), 0, 160)); ?>">
    <?php else: ?>
    <title><?php echo htmlspecialchars($page['meta_title'] ?? $page['title'] ?? 'Actualités'); ?> - VEP</title>
    <meta name="description" content="<?php echo htmlspecialchars($page['meta_description'] ?? ''); ?>">
    <?php endif; ?>
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
    <link href="<?php echo BASE_URL; ?>assets/css/inline-edit.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <?php theme_partial('navbar'); ?>
    <main class="py-5">
        <div class="container">
        <?php if ($newsId > 0): ?>
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>news">Actualités</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($article['title']); ?></li>
                </ol>
            </nav>
            <?php if ($isAdmin): ?>
            <div class="d-flex gap-2 mb-4">
                <a href="<?php echo BASE_URL; ?>admin/news/edit.php?id=<?php echo $newsId; ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>Modifier cet article
                </a>
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php?section=news" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-cogs me-1"></i>Gérer les Actualités
                </a>
            </div>
            <?php endif; ?>
            <?php if (!empty($article['image'])): ?>
            <img src="<?php echo htmlspecialchars($article['image']); ?>"
                 alt="<?php echo htmlspecialchars($article['title']); ?>"
                 class="img-fluid rounded mb-4 w-100" style="max-height:400px;object-fit:cover;">
            <?php endif; ?>
            <p class="text-muted small"><?php echo date('d/m/Y', strtotime($article['published_at'] ?? 'now')); ?></p>
            <h1 class="h2 fw-bold mb-4"><?php echo htmlspecialchars($article['title']); ?></h1>
            <div class="article-content"><?php echo $article['content'] ?? ''; ?></div>
            <div class="mt-5">
                <a href="<?php echo BASE_URL; ?>news" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour aux actualités
                </a>
            </div>
        <?php else: ?>
            <div class="text-center mb-5">
                
                <?php if (!empty($page['subtitle'])): ?>
                <p class="lead text-muted"><?php echo htmlspecialchars($page['subtitle']); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($isAdmin): ?>
            <div class="text-center mb-4">
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php?section=news" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-cogs me-1"></i>Gérer les Actualités
                </a>
            </div>
            <?php endif; ?>
            <?php $allNews = $newsModel->getAll(); ?>
            <?php if (!empty($allNews)): ?>
            <div class="row g-4 mt-3">
                <?php foreach ($allNews as $article): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($article['image'])): ?>
                        <img src="<?php echo htmlspecialchars($article['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($article['title']); ?>" style="height:200px;object-fit:cover;">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <p class="text-muted small mb-2"><?php echo date('d/m/Y', strtotime($article['published_at'] ?? 'now')); ?></p>
                            <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                            <?php if (!empty($article['excerpt'])): ?>
                            <p class="card-text text-muted flex-grow-1"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>news?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary mt-auto">Lire la suite</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-center text-muted py-5">Aucune actualité pour le moment.</p>
            <?php endif; ?>
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
