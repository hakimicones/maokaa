<?php
// includes/theme_preview.php — Génération d'un aperçu visuel (preview.svg) pour un thème.
// L'aperçu est généré à partir des couleurs réelles de assets/css/theme.css (:root).
// Format SVG : texte, pas d'exécution possible (les couleurs sont validées en hexadécimal).

/**
 * Extrait la palette d'un CSS : variables :root de type couleur hexadécimale.
 */
function theme_preview_palette(string $css): array
{
    $fallback = [
        'primary'      => '#FF6B00',
        'primaryDark'  => '#E05500',
        'primaryLight' => '#FF8A33',
        'secondary'    => '#1A1A2E',
        'dark'         => '#0F0F1A',
        'text'         => '#1A1A2E',
        'textLight'    => '#4A4A6A',
        'bg'           => '#FFFFFF',
        'bgAlt'        => '#F7F7FA',
        'bgDark'       => '#1A1A2E',
        'border'       => '#E0E0E8',
        'white'        => '#FFFFFF',
    ];

    $found = [];
    if (preg_match_all('/--([a-zA-Z0-9-]+)\s*:\s*([^;]+);/', $css, $m)) {
        foreach ($m[1] as $i => $name) {
            $val = trim($m[2][$i]);
            if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $val)) {
                $found[strtolower($name)] = $val;
            }
        }
    }

    $keyMap = [
        'primary'      => 'primary',
        'primary-dark' => 'primaryDark',
        'primary-light' => 'primaryLight',
        'secondary'    => 'secondary',
        'dark'         => 'dark',
        'text'         => 'text',
        'text-light'   => 'textLight',
        'bg'           => 'bg',
        'bg-alt'       => 'bgAlt',
        'bg-dark'      => 'bgDark',
        'border'       => 'border',
        'white'        => 'white',
    ];

    $palette = $fallback;
    foreach ($keyMap as $cssName => $key) {
        if (isset($found[$cssName])) {
            $palette[$key] = $found[$cssName];
        }
    }

    // Rayon d'arrondi (--radius: 12px) borné
    $palette['radius'] = 14;
    if (preg_match('/--radius\s*:\s*(\d+(?:\.\d+)?)px/', $css, $rm)) {
        $r = (int)$rm[1];
        if ($r >= 0 && $r <= 32) $palette['radius'] = $r;
    }

    return $palette;
}

/**
 * Dessine un « mini-site » de démonstration aux couleurs de la palette.
 */
function theme_preview_svg(array $p): string
{
    $r  = $p['radius'];
    $ri = $r > 0 ? $r : 6;

    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 520" font-family="&#39;Inter&#39;, &#39;Segoe UI&#39;, Arial, sans-serif" width="100%" height="100%">'
        . '<defs>'
        . '<linearGradient id="tp-grad" x1="0" y1="0" x2="1" y2="1">'
        .   '<stop offset="0" stop-color="' . $p['primary'] . '"/>'
        .   '<stop offset="1" stop-color="' . $p['primaryDark'] . '"/>'
        . '</linearGradient>'
        . '<radialGradient id="tp-glow">'
        .   '<stop offset="0" stop-color="' . $p['primaryLight'] . '" stop-opacity="0.55"/>'
        .   '<stop offset="1" stop-color="' . $p['primaryLight'] . '" stop-opacity="0"/>'
        . '</radialGradient>'
        . '<filter id="tp-shadow" x="-30%" y="-30%" width="160%" height="160%">'
        .   '<feDropShadow dx="0" dy="10" stdDeviation="14" flood-color="#000000" flood-opacity="0.12"/>'
        . '</filter>'
        . '<filter id="tp-shadow-sm" x="-30%" y="-30%" width="160%" height="160%">'
        .   '<feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="#000000" flood-opacity="0.08"/>'
        . '</filter>'
        . '</defs>'
        // Navbar
        . '<rect x="0" y="0" width="800" height="64" fill="' . $p['dark'] . '"/>'
        . '<rect x="0" y="63" width="800" height="1" fill="' . $p['white'] . '" opacity="0.08"/>'
        . '<rect x="32" y="16" width="36" height="32" rx="9" fill="url(#tp-grad)"/>'
        . '<rect x="45" y="25" width="12" height="14" rx="3" fill="' . $p['white'] . '" opacity="0.9"/>'
        . '<text x="80" y="38" fill="' . $p['white'] . '" font-size="15" font-weight="700">MonSite</text>'
        . '<circle cx="462" cy="32" r="4" fill="' . $p['white'] . '" opacity="0.35"/>'
        . '<circle cx="490" cy="32" r="4" fill="' . $p['white'] . '" opacity="0.35"/>'
        . '<circle cx="518" cy="32" r="4" fill="' . $p['white'] . '" opacity="0.35"/>'
        . '<circle cx="546" cy="32" r="4" fill="' . $p['white'] . '" opacity="0.35"/>'
        . '<rect x="640" y="15" width="128" height="34" rx="17" fill="url(#tp-grad)"/>'
        . '<text x="704" y="37" fill="' . $p['white'] . '" font-size="13" font-weight="700" text-anchor="middle">Commencer</text>'
        // Hero
        . '<rect x="0" y="64" width="800" height="236" fill="url(#tp-grad)"/>'
        . '<circle cx="680" cy="110" r="190" fill="url(#tp-glow)"/>'
        . '<rect x="48" y="104" width="192" height="28" rx="14" fill="' . $p['white'] . '" opacity="0.14"/>'
        . '<text x="66" y="123" fill="' . $p['white'] . '" opacity="0.95" font-size="12" font-weight="600">Nouveau &#8226; Interface moderne</text>'
        . '<text x="48" y="178" fill="' . $p['white'] . '" font-size="40" font-weight="800">Créez quelque</text>'
        . '<text x="48" y="216" fill="' . $p['white'] . '" font-size="40" font-weight="800">chose d&#8217;incroyable</text>'
        . '<text x="48" y="248" fill="' . $p['white'] . '" opacity="0.78" font-size="14">Une plateforme moderne, élégante et rapide.</text>'
        . '<rect x="48" y="262" width="146" height="40" rx="20" fill="' . $p['white'] . '" filter="url(#tp-shadow-sm)"/>'
        . '<text x="121" y="288" fill="' . $p['primary'] . '" font-size="14" font-weight="700" text-anchor="middle">Commencer</text>'
        . '<rect x="208" y="262" width="150" height="40" rx="20" fill="none" stroke="' . $p['white'] . '" stroke-width="1.5" opacity="0.5"/>'
        . '<text x="283" y="288" fill="' . $p['white'] . '" opacity="0.95" font-size="14" font-weight="600" text-anchor="middle">En savoir plus</text>'
        // Mock de fenêtre navigateur
        . '<g filter="url(#tp-shadow)">'
        . '<rect x="462" y="96" width="290" height="180" rx="16" fill="' . $p['white'] . '"/>'
        . '</g>'
        . '<circle cx="490" cy="124" r="5" fill="' . $p['textLight'] . '" opacity="0.45"/>'
        . '<circle cx="510" cy="124" r="5" fill="' . $p['textLight'] . '" opacity="0.35"/>'
        . '<circle cx="530" cy="124" r="5" fill="' . $p['textLight'] . '" opacity="0.25"/>'
        . '<rect x="486" y="142" width="244" height="44" rx="10" fill="url(#tp-grad)"/>'
        . '<circle cx="700" cy="164" r="6" fill="' . $p['white'] . '" opacity="0.85"/>'
        . '<rect x="486" y="196" width="244" height="18" rx="6" fill="' . $p['border'] . '" opacity="0.85"/>'
        . '<rect x="486" y="222" width="116" height="18" rx="6" fill="' . $p['border'] . '" opacity="0.6"/>'
        . '<rect x="614" y="222" width="116" height="18" rx="6" fill="' . $p['border'] . '" opacity="0.4"/>'
        // Section fonctionnalités
        . '<rect x="0" y="300" width="800" height="152" fill="' . $p['bgAlt'] . '"/>'
        . '<rect x="48" y="320" width="212" height="112" rx="' . $ri . '" fill="' . $p['white'] . '" stroke="' . $p['border'] . '" stroke-width="1.5" filter="url(#tp-shadow-sm)"/>'
        . '<rect x="294" y="320" width="212" height="112" rx="' . $ri . '" fill="' . $p['white'] . '" stroke="' . $p['border'] . '" stroke-width="1.5" filter="url(#tp-shadow-sm)"/>'
        . '<rect x="540" y="320" width="212" height="112" rx="' . $ri . '" fill="' . $p['white'] . '" stroke="' . $p['border'] . '" stroke-width="1.5" filter="url(#tp-shadow-sm)"/>'
        . theme_preview_svg_card($p, 48, $ri)
        . theme_preview_svg_card($p, 294, $ri)
        . theme_preview_svg_card($p, 540, $ri)
        // Footer
        . '<rect x="0" y="452" width="800" height="68" fill="' . $p['secondary'] . '"/>'
        . '<circle cx="400" cy="470" r="5" fill="' . $p['primaryLight'] . '"/>'
        . '<text x="400" y="498" fill="' . $p['white'] . '" opacity="0.45" font-size="12" text-anchor="middle">Nom du site — Aperçu du thème</text>'
        . '</svg>';
}

/**
 * Une carte de fonctionnalité (icône en tuile + titre + lignes) à partir de la position x.
 */
function theme_preview_svg_card(array $p, int $x, int $ri): string
{
    return '<rect x="' . ($x + 22) . '" y="' . (320 + 18) . '" width="42" height="42" rx="11" fill="' . $p['primaryLight'] . '" opacity="0.22"/>'
        . '<rect x="' . ($x + 30) . '" y="' . (320 + 30) . '" width="26" height="18" rx="4" fill="' . $p['primary'] . '" opacity="0.85"/>'
        . '<text x="' . ($x + 22) . '" y="' . (320 + 98) . '" fill="' . $p['text'] . '" font-size="15" font-weight="700">Fonctionnalité</text>'
        . '<rect x="' . ($x + 22) . '" y="' . (320 + 108) . '" width="150" height="7" rx="3.5" fill="' . $p['textLight'] . '" opacity="0.45"/>'
        . '<rect x="' . ($x + 22) . '" y="' . (320 + 120) . '" width="110" height="7" rx="3.5" fill="' . $p['textLight'] . '" opacity="0.25"/>';
}

/**
 * Écrit preview.svg dans le dossier du thème à partir de son theme.css.
 * Retourne false si le thème n'a pas de theme.css.
 */
function theme_preview_write(string $themeDir): bool
{
    $cssFile = $themeDir . '/assets/css/theme.css';
    if (!is_file($cssFile)) return false;

    $palette = theme_preview_palette((string)file_get_contents($cssFile));
    $svg     = theme_preview_svg($palette);
    return file_put_contents($themeDir . '/preview.svg', $svg) !== false;
}
