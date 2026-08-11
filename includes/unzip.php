<?php
// includes/unzip.php — Extraction de fichiers ZIP en PHP pur (sans extension "zip")
// Utilisé pour l'upload de thèmes. Basé sur le répertoire central + gzinflate (zlib).

/**
 * Extrait un archive ZIP dans un répertoire.
 *
 * @param string $zipPath Chemin du fichier .zip
 * @param string $destDir Répertoire de destination (créé si absent)
 * @param array  $opts    Options : maxSize (total décompressé), maxFileSize, maxEntries, allowedExt
 * @return array ['ok' => bool, 'error' => ?string, 'entries' => int]
 */
function zip_extract(string $zipPath, string $destDir, array $opts = []): array {
    $maxTotal    = $opts['maxSize']    ?? 50 * 1024 * 1024; // 50 Mo décompressés au total
    $maxFileSize = $opts['maxFileSize'] ?? 5 * 1024 * 1024;  // 5 Mo par fichier
    $maxEntries  = $opts['maxEntries'] ?? 500;
    $allowedExt  = $opts['allowedExt'] ?? [
        'php', 'css', 'js', 'json', 'png', 'jpg', 'jpeg', 'webp', 'svg', 'gif', 'ico',
        'woff', 'woff2', 'ttf', 'eot', 'txt', 'md', 'html', 'map',
    ];

    if (!is_file($zipPath)) return ['ok' => false, 'error' => 'Fichier ZIP introuvable.', 'entries' => 0];
    $data = file_get_contents($zipPath);
    if ($data === false || strlen($data) < 22) {
        return ['ok' => false, 'error' => 'Fichier ZIP invalide (trop court).', 'entries' => 0];
    }

    // --- 1. Localiser le End Of Central Directory (signature PK\x05\x06) ---
    $eocdPos = strrpos($data, "PK\x05\x06");
    if ($eocdPos === false) {
        return ['ok' => false, 'error' => 'Fichier ZIP invalide (EOCD introuvable).', 'entries' => 0];
    }
    // Structure EOCD (46+ octets) :
    //   sig(4) disc(2) cdDisc(2) entDisc(2) entTotal(2) cdSize(4) cdOffset(4) commentLen(2)
    $unpack = unpack(
        'Vsig/vdisc/vcddisc/ventdisc/venttotal/Vcdsize/Vcdoffset/vcommentlen',
        substr($data, $eocdPos, 22)
    );
    $cdOffset = $unpack['cdoffset'];
    $cdSize   = $unpack['cdsize'];
    $total    = $unpack['enttotal'];
    if ($total <= 0) return ['ok' => false, 'error' => 'L\'archive ZIP est vide.', 'entries' => 0];
    if ($total > $maxEntries) {
        return ['ok' => false, 'error' => "Archive trop volumineuse (max $maxEntries fichiers).", 'entries' => 0];
    }

    // --- 2. Lire les entrées du répertoire central (signature PK\x01\x02) ---
    $cd = substr($data, $cdOffset, $cdSize);
    if ($cd === false || strlen($cd) !== $cdSize) {
        return ['ok' => false, 'error' => 'Fichier ZIP invalide (répertoire central).', 'entries' => 0];
    }

    // Candidats : on récupère un chemin cible + méthode + tailles + offset local.
    $entries = [];
    $pos     = 0;
    while ($pos + 46 <= strlen($cd)) {
        if (substr($cd, $pos, 4) !== "PK\x01\x02") {
            $pos++;
            continue;
        }
        $h = unpack(
            'Vsig/vvermade/vverneed/vflags/vmethod/vmodtime/vmoddate/Vcrc/Vcsize/Vusize/vnamelen/vextralen/vcommentlen/vdiskstart/vinternal/Vexternal/Vlocaloffset',
            substr($cd, $pos, 46)
        );
        $name     = substr($cd, $pos + 46, $h['namelen']);
        $extraLen = $h['extralen'];
        $pos     += 46 + $h['namelen'] + $extraLen + $h['commentlen'];

        // Normaliser le nom : '\' -> '/', décodage UTF-8 si drapeau bit 11
        if ($h['flags'] & 0x0800) {
            // déjà UTF-8
        } else {
            $name = @iconv('CP437', 'UTF-8//IGNORE', $name) ?: $name;
        }
        $name = str_replace('\\', '/', $name);
        $name = trim($name, '/');

        // Sécurité : rejet des chemins dangereux
        $cleanName = preg_replace('@/+@', '/', $name);
        if ($cleanName === '' ) continue;
        $parts = explode('/', $cleanName);
        foreach ($parts as $p) {
            if ($p === '..' || $p === '.' ) {
                return ['ok' => false, 'error' => "Chemin invalide dans l'archive : $name", 'entries' => 0];
            }
        }
        if (strpos($cleanName, "\0") !== false) {
            return ['ok' => false, 'error' => "Nom de fichier invalide dans l'archive.", 'entries' => 0];
        }
        if ($h['csize'] < 0 || $h['usize'] < 0) {
            return ['ok' => false, 'error' => 'Tailles invalides dans l\'archive.', 'entries' => 0];
        }

        $entries[] = [
            'name'   => $cleanName,
            'dir'    => (substr($cleanName, -1) === '/'),
            'method' => $h['method'],
            'csize'  => $h['csize'],
            'usize'  => $h['usize'],
            'offset' => $h['localoffset'],
        ];
        if (count($entries) > $maxEntries) {
            return ['ok' => false, 'error' => "Archive trop volumineuse (max $maxEntries fichiers).", 'entries' => 0];
        }
    }

    // --- 3. Extraire chaque entrée ---
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    $totalOut  = 0;
    $extracted = 0;

    foreach ($entries as $e) {
        if ($e['dir']) {
            @mkdir($destDir . '/' . $e['name'], 0755, true);
            continue;
        }

        // Répertoire stocké comme entrée vide sans extension (Compress-Archive Windows)
        if ($e['usize'] === 0 && $e['csize'] === 0 && pathinfo($e['name'], PATHINFO_EXTENSION) === '') {
            @mkdir($destDir . '/' . $e['name'], 0755, true);
            continue;
        }

        // Whitelist d'extensions
        $ext = strtolower(pathinfo($e['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            return ['ok' => false, 'error' => "Extension non autorisée : .$ext", 'entries' => 0];
        }
        if ($e['usize'] > $maxFileSize) {
            return ['ok' => false, 'error' => "Fichier trop volumineux : " . $e['name'], 'entries' => 0];
        }
        $totalOut += $e['usize'];
        if ($totalOut > $maxTotal) {
            return ['ok' => false, 'error' => 'Archive trop volumineuse une fois décompressée.', 'entries' => 0];
        }

        // Lire l'en-tête local pour connaître la taille des champs nom + extra
        $lh = substr($data, $e['offset'], 30);
        if (strlen($lh) < 30 || substr($lh, 0, 4) !== "PK\x03\x04") {
            return ['ok' => false, 'error' => 'En-tête local introuvable : ' . $e['name'], 'entries' => 0];
        }
        $lhUnpack = unpack('Vsig/vver/vflags/vmethod/vmodtime/vmoddate/Vcrc/Vcsize/Vusize/vnamelen/vextralen', $lh);
        $dataStart = $e['offset'] + 30 + $lhUnpack['namelen'] + $lhUnpack['extralen'];
        $raw = substr($data, $dataStart, $e['csize']);
        if (strlen($raw) !== $e['csize']) {
            return ['ok' => false, 'error' => 'Données tronquées dans l\'archive : ' . $e['name'], 'entries' => 0];
        }

        // Décompression
        switch ($e['method']) {
            case 0: // stocké
                $content = $raw;
                break;
            case 8: // deflate
                $content = gzinflate($raw);
                if ($content === false) {
                    return ['ok' => false, 'error' => 'Décompression impossible : ' . $e['name'], 'entries' => 0];
                }
                break;
            default:
                return ['ok' => false, 'error' => 'Méthode de compression non supportée : ' . $e['name'], 'entries' => 0];
        }
        if (strlen($content) !== $e['usize']) {
            return ['ok' => false, 'error' => 'Taille incohérente : ' . $e['name'], 'entries' => 0];
        }

        $target = $destDir . '/' . $e['name'];
        $parent = dirname($target);
        if (!is_dir($parent)) @mkdir($parent, 0755, true);
        if (file_put_contents($target, $content) === false) {
            return ['ok' => false, 'error' => 'Impossible d\'écrire : ' . $e['name'], 'entries' => 0];
        }
        $extracted++;
    }

    return ['ok' => true, 'error' => null, 'entries' => $extracted];
}

/**
 * Supprime récursivement un répertoire (et son contenu).
 */
function rrmdir(string $dir): void {
    if (!is_dir($dir)) {
        if (is_file($dir)) @unlink($dir);
        return;
    }
    foreach (scandir($dir) ?: [] as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? rrmdir($path) : @unlink($path);
    }
    @rmdir($dir);
}
