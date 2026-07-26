<?php
// sitemap.php — Sitemap XML dynamique

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: index, follow');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . '://' . $host . rtrim(BASE_URL, '/');
$excludedSlugs = ['login', 'admin', '404'];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// 1. Pages content publiées
$stmt = $pdo->query("SELECT slug, updated_at, created_at FROM content WHERE status = 'published' ORDER BY id ASC");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    if (in_array($row['slug'], $excludedSlugs, true)) continue;
    $lastmod = $row['updated_at'] ?: $row['created_at'];
    echo '<url>' . "\n";
    echo '  <loc>' . htmlspecialchars($baseUrl . '/' . $row['slug']) . '</loc>' . "\n";
    if ($lastmod) echo '  <lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>' . "\n";
    echo '  <changefreq>weekly</changefreq>' . "\n";
    echo '</url>' . "\n";
}

// 2. Produits actifs
$stmt = $pdo->query("SELECT id, updated_at, created_at FROM produits WHERE active = 1 ORDER BY id ASC");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $lastmod = $row['updated_at'] ?: $row['created_at'];
    echo '<url>' . "\n";
    echo '  <loc>' . htmlspecialchars($baseUrl . '/products?id=' . $row['id']) . '</loc>' . "\n";
    if ($lastmod) echo '  <lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>' . "\n";
    echo '  <changefreq>monthly</changefreq>' . "\n";
    echo '</url>' . "\n";
}

// 3. Actualités publiées
$stmt = $pdo->query("SELECT id, published_at, updated_at, created_at FROM actualites WHERE status = 'published' ORDER BY published_at DESC");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $lastmod = $row['updated_at'] ?: $row['published_at'] ?: $row['created_at'];
    echo '<url>' . "\n";
    echo '  <loc>' . htmlspecialchars($baseUrl . '/news?id=' . $row['id']) . '</loc>' . "\n";
    if ($lastmod) echo '  <lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>' . "\n";
    echo '  <changefreq>monthly</changefreq>' . "\n";
    echo '</url>' . "\n";
}

echo '</urlset>' . "\n";
