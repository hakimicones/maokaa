<?php
// admin/tools/migrate-shortcodes.php
// Migre les pages avec data-vep-block vers les shortcodes [tag attr="val"]
// Exécuter une seule fois depuis le navigateur (admin authentifié)

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requirePasswordChange();
requireRole('admin');

$dryRun = !isset($_GET['run']);

function vep_block_to_shortcode(string $html): string
{
    return preg_replace_callback(
        '/<div([^>]*\bdata-vep-block=["\'][^"\']+["\'][^>]*)>[\s\S]*?<\/div>/i',
        function (array $m): string {
            $attrString = $m[1];
            preg_match_all('/\bdata-([\w-]+)=["\']([^"\']*)["\']/', $attrString, $matches, PREG_SET_ORDER);
            $attrs = [];
            foreach ($matches as $a) $attrs[$a[1]] = $a[2];

            $tag = str_replace('-', '_', $attrs['vep-block'] ?? '');
            if (empty($tag)) return $m[0];

            $sc = '[' . $tag;
            foreach ($attrs as $k => $v) {
                if ($k === 'vep-block') continue;
                $attrName = str_replace('-', '_', $k);
                $sc .= ' ' . $attrName . '="' . htmlspecialchars($v, ENT_QUOTES) . '"';
            }
            $sc .= ']';
            return $sc;
        },
        $html
    ) ?? $html;
}

$pages = $pdo->query("SELECT id, title, body FROM content WHERE body LIKE '%data-vep-block%'")->fetchAll(PDO::FETCH_ASSOC);

$results = [];
foreach ($pages as $page) {
    $newBody = vep_block_to_shortcode($page['body']);
    $changed = $newBody !== $page['body'];
    if ($changed && !$dryRun) {
        $pdo->prepare("UPDATE content SET body = ? WHERE id = ?")->execute([$newBody, $page['id']]);
    }
    $results[] = ['id' => $page['id'], 'title' => $page['title'], 'changed' => $changed, 'preview' => $newBody];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Migration Shortcodes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="h3 mb-4">Migration data-vep-block → shortcodes</h1>

    <?php if ($dryRun): ?>
    <div class="alert alert-warning">
        <strong>Mode simulation</strong> — aucune modification en base.
        <a href="?run=1" class="btn btn-danger btn-sm ms-3" onclick="return confirm('Lancer la migration réelle ?')">
            Lancer la migration réelle
        </a>
    </div>
    <?php else: ?>
    <div class="alert alert-success"><strong>Migration effectuée.</strong></div>
    <?php endif; ?>

    <p><?php echo count($pages); ?> page(s) avec des blocs VEP trouvée(s).</p>

    <?php foreach ($results as $r): ?>
    <div class="card mb-3 <?php echo $r['changed'] ? 'border-warning' : 'border-success'; ?>">
        <div class="card-header d-flex justify-content-between">
            <span><strong>#<?php echo $r['id']; ?></strong> — <?php echo htmlspecialchars($r['title']); ?></span>
            <span class="badge <?php echo $r['changed'] ? 'bg-warning text-dark' : 'bg-success'; ?>">
                <?php echo $r['changed'] ? 'Modifié' : 'Inchangé'; ?>
            </span>
        </div>
        <?php if ($r['changed']): ?>
        <div class="card-body">
            <pre class="small bg-light p-2 rounded" style="max-height:200px;overflow:auto;"><?php echo htmlspecialchars(substr($r['preview'], 0, 800)); ?></pre>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline-secondary mt-3">Retour</a>
</div>
</body>
</html>
