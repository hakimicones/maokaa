<?php
// includes/modules.php — Registre des modules du CMS

class ModuleRegistry
{
    private static ?array $modules = null;
    private static PDO $pdo;

    public static function init(PDO $pdo): void
    {
        self::$pdo = $pdo;
        self::$modules = null;
    }

    private static function load(): array
    {
        if (self::$modules !== null) return self::$modules;

        self::$modules = [];
        try {
            $rows = self::$pdo->query("SELECT slug, name, type, enabled, depends_on FROM modules ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                self::$modules[$row['slug']] = [
                    'name'       => $row['name'],
                    'type'       => $row['type'],
                    'enabled'    => (bool)$row['enabled'],
                    'depends_on' => $row['depends_on'] ? array_map('trim', explode(',', $row['depends_on'])) : [],
                ];
            }
        } catch (PDOException $e) {
        }

        return self::$modules;
    }

    public static function isEnabled(string $slug): bool
    {
        $modules = self::load();
        return isset($modules[$slug]) && $modules[$slug]['enabled'];
    }

    public static function get(string $slug): ?array
    {
        $modules = self::load();
        return $modules[$slug] ?? null;
    }

    public static function getEnabled(string $type = null): array
    {
        $modules = self::load();
        $result = [];
        foreach ($modules as $slug => $mod) {
            if (!$mod['enabled']) continue;
            if ($type !== null && $mod['type'] !== $type) continue;
            $result[$slug] = $mod;
        }
        return $result;
    }

    public static function getAll(): array
    {
        return self::load();
    }

    public static function enable(string $slug): bool
    {
        $modules = self::load();
        if (!isset($modules[$slug])) return false;

        $mod = $modules[$slug];
        foreach ($mod['depends_on'] as $dep) {
            if (!self::isEnabled($dep)) return false;
        }

        $stmt = self::$pdo->prepare("UPDATE modules SET enabled = 1 WHERE slug = ?");
        $stmt->execute([$slug]);
        self::$modules = null;
        return true;
    }

    public static function disable(string $slug): bool
    {
        $modules = self::load();
        if (!isset($modules[$slug])) return false;
        if ($modules[$slug]['type'] === 'core') return false;

        foreach ($modules as $otherSlug => $other) {
            if ($other['enabled'] && in_array($slug, $other['depends_on'], true)) {
                return false;
            }
        }

        $stmt = self::$pdo->prepare("UPDATE modules SET enabled = 0 WHERE slug = ?");
        $stmt->execute([$slug]);
        self::$modules = null;
        return true;
    }
}

function is_module_enabled(string $slug): bool
{
    return ModuleRegistry::isEnabled($slug);
}
