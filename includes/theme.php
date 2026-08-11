<?php
// includes/theme.php — Gestionnaire de thèmes

class ThemeManager
{
    private static string $active = 'default';
    private static string $root   = '';

    public static function init(PDO $pdo): void
    {
        self::$root = dirname(__DIR__) . '/themes/';
        try {
            $val = $pdo->query(
                "SELECT setting_value FROM settings WHERE setting_key = 'active_theme'"
            )->fetchColumn();
            if ($val) self::$active = $val;
        } catch (PDOException $e) {
            // table settings absente, on garde 'default'
        }
    }

    public static function template(string $name): string
    {
        $name = basename($name, '.php');
        if ($name === '' || preg_match('/[^a-zA-Z0-9_\-]/', $name)) return '';

        foreach ([self::$active, 'default'] as $theme) {
            $f = self::$root . $theme . '/templates/' . $name . '.php';
            if (file_exists($f)) return $f;
        }
        return '';
    }

    public static function partial(string $name): string
    {
        foreach ([self::$active, 'default'] as $theme) {
            $f = self::$root . $theme . '/partials/' . $name . '.php';
            if (file_exists($f)) return $f;
        }
        return '';
    }

    public static function url(string $path = ''): string
    {
        return BASE_URL . 'themes/' . self::$active . '/' . ltrim($path, '/');
    }

    public static function getActive(): string { return self::$active; }

    public static function setActive(PDO $pdo, string $theme): bool
    {
        if (!is_dir(self::$root . $theme)) return false;
        $stmt = $pdo->prepare(
            "INSERT INTO settings (setting_key, setting_value) VALUES ('active_theme', ?)
             ON DUPLICATE KEY UPDATE setting_value = ?"
        );
        if ($stmt->execute([$theme, $theme])) {
            self::$active = $theme;
            return true;
        }
        return false;
    }

    public static function list(): array
    {
        $themes = [];
        foreach (glob(self::$root . '*/theme.json') ?: [] as $file) {
            $meta   = json_decode(file_get_contents($file), true) ?? [];
            $folder = basename(dirname($file));
            $themes[] = array_merge($meta, [
                'folder'  => $folder,
                'active'  => ($folder === self::$active),
                'preview' => is_file(dirname($file) . '/preview.png')
                    ? BASE_URL . 'themes/' . $folder . '/preview.png'
                    : (is_file(dirname($file) . '/preview.svg')
                        ? BASE_URL . 'themes/' . $folder . '/preview.svg'
                        : null),
            ]);
        }
        return $themes;
    }

    public static function getAvailableTemplates(): array
    {
        $templates = [];
        foreach (glob(self::$root . '*/templates/*.php') ?: [] as $f) {
            $name = basename($f, '.php');
            if (!in_array($name, $templates, true)) {
                $templates[] = $name;
            }
        }
        sort($templates);
        return $templates;
    }

    /**
     * Vérifie la structure d'un thème installé.
     * Retourne la liste des éléments manquants (chemins relatifs, parents inclus).
     * Ex. : ["templates/", "assets/css/theme.css"]
     */
    public static function structureIssues(string $folder): array
    {
        $root = self::$root . $folder . '/';
        $missing = [];

        if (!is_dir($root . 'templates')) $missing[] = 'templates/';
        if (!is_dir($root . 'partials'))   $missing[] = 'partials/';
        if (!is_dir($root . 'assets')) {
            $missing[] = 'assets/';
        } else {
            if (!is_dir($root . 'assets/css')) {
                $missing[] = 'assets/css/';
            } elseif (!is_file($root . 'assets/css/theme.css')) {
                $missing[] = 'assets/css/theme.css';
            }
            if (!is_dir($root . 'assets/js')) $missing[] = 'assets/js/';
        }

        return $missing;
    }
}

function theme_partial(string $name): void
{
    global $pdo, $page;
    $file = ThemeManager::partial($name);
    if ($file) include $file;
}

function theme_url(string $path = ''): string
{
    return ThemeManager::url($path);
}
