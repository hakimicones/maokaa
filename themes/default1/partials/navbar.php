<?php
require_once dirname(__DIR__, 3) . '/app/models/Menu.php';
$menuModel  = new Menu($pdo);
$mainMenu   = $menuModel->getByName('main');
$menuItems  = $mainMenu ? $menuModel->getItemsWithChildren($mainMenu['id']) : [];
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>">
            <img src="<?php echo BASE_URL; ?>assets/img/logo.jpg" alt="Logo" style="height:40px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php foreach ($menuItems as $item):
                    $params   = $item['params'] ? json_decode($item['params'], true) : [];
                    $showIcon = !isset($params['icon']) || $params['icon'];
                    $icon     = ($showIcon && !empty($item['icon'])) ? htmlspecialchars($item['icon']) : '';
                ?>
                <li class="nav-item<?php echo !empty($item['children']) ? ' dropdown' : ''; ?>">
                    <a class="nav-link<?php echo !empty($item['children']) ? ' dropdown-toggle' : ''; ?>"
                       href="<?php echo BASE_URL . ltrim($item['url'], '/'); ?>"
                       <?php echo !empty($item['children']) ? 'role="button" data-bs-toggle="dropdown"' : ''; ?>>
                        <?php if ($icon): ?><i class="<?php echo $icon; ?> me-1"></i><?php endif; ?>
                        <?php echo htmlspecialchars($item['title']); ?>
                    </a>
                    <?php if (!empty($item['children'])): ?>
                    <ul class="dropdown-menu">
                        <?php foreach ($item['children'] as $child):
                            $cp   = $child['params'] ? json_decode($child['params'], true) : [];
                            $ci   = (!isset($cp['icon']) || $cp['icon']) && !empty($child['icon']) ? htmlspecialchars($child['icon']) : '';
                        ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL . ltrim($child['url'], '/'); ?>">
                            <?php if ($ci): ?><i class="<?php echo $ci; ?> me-2"></i><?php endif; ?>
                            <?php echo htmlspecialchars($child['title']); ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <?php if (isset($_SESSION['admin_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                        <i class="fas fa-cog me-1"></i>Dashboard
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>login.php">
                        <i class="fas fa-lock me-1"></i>Admin
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
