<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$sql = file_get_contents(__DIR__ . '/db/migration_modules.sql');
$pdo->exec($sql);
echo "Migration OK" . PHP_EOL;

$rows = $pdo->query("SELECT slug, name, type, enabled FROM modules ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['slug'] . ' | ' . $r['name'] . ' | ' . $r['type'] . ' | enabled=' . $r['enabled'] . PHP_EOL;
}
