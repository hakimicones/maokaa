<?php


// Charger les variables d'environnement depuis .env
require_once __DIR__ . '/env.php';

/**
 * Vérifie qu'une URL pointe vers le même domaine (pas de redirect externe).
 */
function is_safe_url(string $url): bool {
    if ($url === '') return false;
    if (str_starts_with($url, '//')) return false;
    if ($url[0] === '/') return true;
    if (!str_contains($url, '://')) return true;
    $parsed = parse_url($url);
    if (!empty($parsed['host'])) {
        $allowedHost = parse_url(BASE_URL)['host'] ?? ($_SERVER['HTTP_HOST'] ?? '');
        return $allowedHost !== '' && $parsed['host'] === $allowedHost;
    }
    return false;
}

/**
 * Retourne l'URL de retour prioritaire : return_url GET > Referer > défaut.
 * Valide que l'URL est sur le même domaine (protection anti-open-redirect).
 */
function return_url(string $default = ''): string {
    $retUrl = $_GET['return_url'] ?? '';
    if (!empty($retUrl) && is_safe_url($retUrl)) return $retUrl;
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if (!empty($ref) && is_safe_url($ref)) return $ref;
    return $default;
}
