<?php
// includes/settings_helpers.php — Helpers pour la table settings

function get_setting(PDO $pdo, string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        $cache[$key] = $val !== false ? $val : $default;
    }
    return $cache[$key];
}

function set_setting(PDO $pdo, string $key, string $value): bool {
    $stmt = $pdo->prepare(
        "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = ?"
    );
    return $stmt->execute([$key, $value, $value]);
}

/**
 * Nettoie le HTML riche du nom du logo : ne garde que les spans/b/i/u/strong/em
 * avec la propriété color (protection XSS). Tout le reste est retiré.
 */
function sanitize_brand_html(string $html): string {
    if (trim($html) === '') return '';
    // Supprimer les scripts éventuels
    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8"?><div id="__brand_root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $allowedTags = ['span', 'b', 'strong', 'i', 'em', 'u'];

    $clean = function ($elements) use (&$clean, $allowedTags, $dom) {
        foreach ($elements as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) continue;
            $tag = strtolower($node->nodeName);

            // Extraire la couleur (style="color:..." ou <font color="...">)
            $style = '';
            if ($node->hasAttribute('style')) {
                $props = [];
                foreach (explode(';', $node->getAttribute('style')) as $decl) {
                    $pair = explode(':', $decl, 2);
                    $prop = strtolower(trim($pair[0] ?? ''));
                    $val  = trim($pair[1] ?? '');
                    if ($prop === 'color' && $val !== '') {
                        $props[] = 'color:' . preg_replace('/[^#\w(),.\s-]/', '', $val);
                    }
                }
                $style = implode(';', $props);
            }
            if ($tag === 'font' && $node->hasAttribute('color')) {
                $style = 'color:' . preg_replace('/[^#\w(),.\s-]/', '', $node->getAttribute('color'));
            }

            // Balise non autorisée : la désenvelopper (garder le contenu).
            // Pour <font> avec couleur, on la remplace par un <span color>.
            if (!in_array($tag, $allowedTags, true)) {
                if ($style !== '' && $node->hasChildNodes()) {
                    $span = $dom->createElement('span');
                    $span->setAttribute('style', $style);
                    while ($node->firstChild) $span->appendChild($node->firstChild);
                    $node->parentNode->insertBefore($span, $node);
                } else {
                    while ($node->firstChild) {
                        $node->parentNode->insertBefore($node->firstChild, $node);
                    }
                }
                $node->parentNode->removeChild($node);
                continue;
            }

            // Balise autorisée : ne garder que la propriété color
            foreach (iterator_to_array($node->attributes) as $attr) {
                $node->removeAttribute($attr->nodeName);
            }
            if ($style !== '') $node->setAttribute('style', $style);

            if ($node->hasChildNodes()) {
                $clean(iterator_to_array($node->childNodes));
            }
        }
    };

    $root = $dom->getElementById('__brand_root');
    if (!$root) return '';
    $clean(iterator_to_array($root->childNodes));
    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $dom->saveHTML($child);
    }
    return trim($out);
}
