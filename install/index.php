<?php
// install/index.php — Point d'entrée principal de l'installation Maokaa CMS
// Gère les 5 étapes : Prérequis → BD → Config → Admin → Finalisation

session_start();

// Empêcher le reinstall si verrouillé
if (file_exists(__DIR__ . '/install.lock')) {
    header('Location: ?done=1');
    exit;
}

// Initialiser les variables
$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

// Charger les fonctions utilitaires
require_once __DIR__ . '/functions.php';

// Charger le template .env si nécessaire
if (!file_exists('../.env')) {
    create_env_template('../.env.template');
}

// --- ÉTAPE 1 : Prérequis Système ---
if ($step == 1) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Installation Maokaa CMS - Étape 1/5</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25); }
            .check-icon { width: 24px; height: 24px; }
        </style>
    </head>
    <body class="min-vh-100 bg-light p-0">
        <nav class="navbar fixed-top bg-white px-3 py-3 border-bottom">
            <div class="container-fluid">
                <h2><i class="fas fa-cog me-2"></i> Installation Maokaa CMS</h2>
            </div>
        </nav>

        <div class="container-fluid mt-5 pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="progress mb-4" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 20%;"></div>
                    </div>

                    <h1 class="mb-4">Bienvenue dans l'installation</h1>

                    <div class="card bg-white p-4 rounded-3 mb-4 shadow-sm">
                        <h2 class="mb-3"><i class="fas fa-check-circle me-2" style="color: #28a745;"></i> Prérequis Système</h2>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="php-version" <?= phpversion() >= '7.4' ? 'checked disabled' : '' ?>>
                                    <label class="form-check-label" for="php-version">
                                        PHP <?= phpversion() ?> (≥ 7.4 requis)
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="php-ext" <?= check_php_extensions(['pdo', 'mbstring', 'gd', 'curl', 'json', 'xml']) ? 'checked disabled' : '' ?>>
                                    <label class="form-check-label" for="php-ext">
                                        Extensions PDO, mbstring, GD, curl, json, xml
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dir-images" <?= is_writable_path('assets/images') ? 'checked disabled' : '' ?>>
                                    <label class="form-check-label" for="dir-images">
                                        assets/images/ (writable)
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dir-brochures" <?= is_writable_path('assets/brochures') ? 'checked disabled' : '' ?>>
                                    <label class="form-check-label" for="dir-brochures">
                                        assets/brochures/ (writable)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" onclick="window.location='?step=2'" class="btn btn-primary">
                            <i class="fas fa-arrow-right me-2"></i>Suivant
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- ÉTAPE 2 : Configuration Base de Données ---
if ($step == 2) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Installation Maokaa CMS - Étape 2/5</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25); }
        </style>
    </head>
    <body class="min-vh-100 bg-light p-0">
        <nav class="navbar fixed-top bg-white px-3 py-3 border-bottom">
            <div class="container-fluid">
                <h2><i class="fas fa-cog me-2"></i> Installation Maokaa CMS</h2>
            </div>
        </nav>

        <div class="container-fluid mt-5 pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="progress mb-4" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 40%;"></div>
                    </div>

                    <h1 class="mb-4">Configuration de la base de données</h1>

                    <div class="card bg-white p-4 rounded-3 mb-4 shadow-sm">
                        <h2 class="mb-3"><i class="fas fa-database me-2"></i> Informations de connexion</h2>

                        <form method="POST" action="" id="db-form">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label>Hôte MySQL</label>
                                    <input type="text" name="db_host" value="localhost" class="form-control" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label>Nom de la base</label>
                                    <input type="text" name="db_name" value="maokaa" class="form-control" required>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-12 col-md-6">
                                    <label>Utilisateur</label>
                                    <input type="text" name="db_user" value="root" class="form-control" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label>Mot de passe</label>
                                    <input type="password" name="db_pass" class="form-control" placeholder="(vide si root sans mot de passe)">
                                </div>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted">
                                    Base de données : MySQL / MariaDB 5.7+
                                </small>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" name="test_db" class="btn btn-primary flex-grow">
                                    <i class="fas fa-connection me-2"></i>Test de connexion
                                </button>
                                <button type="button" onclick="window.location='?step=1'" class="btn btn-outline-secondary flex-grow">
                                    <i class="fas fa-arrow-left me-2"></i>Retour
                                </button>
                            </div>
                        </form>

                        <?php
                        // Tester la connexion si submit
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_db'])) {
                            $dsn = "mysql:host=" . $_POST['db_host'] . ";dbname=" . $_POST['db_name'] . ";charset=utf8mb4";
                            $pdo = init_db($dsn, $_POST['db_user'], $_POST['db_pass']);

                            if ($pdo) {
                                $success[] = "Connexion à la base réussie !";
                                // Stocker uniquement les paramètres, pas l'objet PDO
                                $_SESSION['db_host'] = $_POST['db_host'];
                                $_SESSION['db_name'] = $_POST['db_name'];
                                $_SESSION['db_user'] = $_POST['db_user'];
                                $_SESSION['db_pass'] = $_POST['db_pass'];
                                $_SESSION['db_dsn'] = $dsn;
                                ?>
                                <div class="mt-3 p-3 bg-success text-white rounded">
                                    <i class="fas fa-check me-2"></i>
                                    <?php echo htmlspecialchars($success[0]); ?>
                                    <div class="mt-3">
                                        <a href="?step=3" class="btn btn-light btn-sm">
                                            <i class="fas fa-arrow-right me-1"></i>Continuer à l'étape suivante
                                        </a>
                                    </div>
                                </div>
                                <?php
                            } else {
                                $errors[] = "Échec de la connexion à la base de données";
                                echo '<div class="mt-3 p-3 bg-danger text-white rounded"><i class="fas fa-exclamation-triangle me-2"></i>' . htmlspecialchars($errors[0]) . '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- ÉTAPE 3 : Configuration Application ---
if ($step == 3) {
    // Vérifier que la connexion à la base a été testée
    if (!isset($_SESSION['db_host'])) {
        header('Location: ?step=2');
        exit;
    }

    // Recréer la connexion PDO
    $dsn = $_SESSION['db_dsn'];
    $pdo = init_db($dsn, $_SESSION['db_user'], $_SESSION['db_pass']);
    if (!$pdo) {
        header('Location: ?step=2');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Installation Maokaa CMS - Étape 3/5</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25); }
        </style>
    </head>
    <body class="min-vh-100 bg-light p-0">
        <nav class="navbar fixed-top bg-white px-3 py-3 border-bottom">
            <div class="container-fluid">
                <h2><i class="fas fa-cog me-2"></i> Installation Maokaa CMS</h2>
            </div>
        </nav>

        <div class="container-fluid mt-5 pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="progress mb-4" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 60%;"></div>
                    </div>

                    <h1 class="mb-4">Configuration de l'application</h1>

                    <div class="card bg-white p-4 rounded-3 mb-4 shadow-sm">
                        <h2 class="mb-3"><i class="fas fa-wrench me-2"></i> Paramètres</h2>

                        <form method="POST" action="?step=4" id="app-form">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label>URL de base (BASE_URL)</label>
                                    <input type="text" name="base_url" value="<?php echo isset($_SESSION['base_url']) ? htmlspecialchars($_SESSION['base_url']) : detect_base_url(); ?>" class="form-control" required>
                                    <small class="text-muted">Exemple : /Hebergement/maokaa/ ou https://monsite.com</small>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-12 col-md-6">
                                    <label>Site Name</label>
                                    <input type="text" name="site_name" value="Maokaa CMS" class="form-control" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label>Admin Email</label>
                                    <input type="email" name="admin_email" value="admin@maokaa.com" class="form-control" required>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-12 col-md-6">
                                    <label>reCAPTCHA Site Key (optionnel)</label>
                                    <input type="text" name="recaptcha_key" class="form-control" placeholder="6LcvtTEtAAAA... (optionnel)">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label>reCAPTCHA Secret Key (optionnel)</label>
                                    <input type="text" name="recaptcha_secret" class="form-control" placeholder="optionnel">
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-12 col-md-6">
                                    <label>AI API URL</label>
                                    <input type="text" name="ai_url" value="https://api.deepseek.com/v1" class="form-control" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label>AI API Key</label>
                                    <input type="password" name="ai_key" class="form-control" placeholder="sk-..." required>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" name="save_config" class="btn btn-primary flex-grow">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                                <button type="button" onclick="window.location='?step=2'" class="btn btn-outline-secondary flex-grow">
                                    <i class="fas fa-arrow-left me-2"></i>Retour
                                </button>
                            </div>
                        </form>

                        <?php
                        // Sauvegarder la config si submit
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
                            $env_content = "# Maokaa CMS Configuration\n";
                            $env_content .= "BASE_URL=" . $_POST['base_url'] . "\n";
                            $env_content .= "DB_HOST=" . $_POST['db_host'] . "\n";
                            $env_content .= "DB_NAME=" . $_POST['db_name'] . "\n";
                            $env_content .= "DB_USER=" . $_POST['db_user'] . "\n";
                            $env_content .= "DB_PASS=" . $_POST['db_pass'] . "\n";
                            $env_content .= "UPLOAD_DIR=assets/images/\n";
                            $env_content .= "BROCHURE_DIR=assets/brochures/\n";
                            $env_content .= "MAX_IMAGE_SIZE=2097152\n";
                            $env_content .= "MAX_PDF_SIZE=5242880\n";
                            $env_content .= "RECAPTCHA_SITE_KEY=" . ($_POST['recaptcha_key'] ?? '') . "\n";
                            $env_content .= "RECAPTCHA_SECRET_KEY=" . ($_POST['recaptcha_secret'] ?? '') . "\n";
                            $env_content .= "AI_API_URL=" . $_POST['ai_url'] . "\n";
                            $env_content .= "AI_API_KEY=" . $_POST['ai_key'] . "\n";
                            $env_content .= "AI_MODEL=deepseek-v4-flash\n";

                            if (file_put_contents('../.env', $env_content)) {
                                $success[] = "Fichier .env créé avec succès !";
                                echo '<div class="mt-3 p-3 bg-success text-white rounded"><i class="fas fa-check me-2"></i>' . htmlspecialchars($success[0]) . '</div>';
                            } else {
                                $errors[] = "Erreur lors de la création du .env";
                                echo '<div class="mt-3 p-3 bg-danger text-white rounded"><i class="fas fa-exclamation-triangle me-2"></i>' . htmlspecialchars($errors[0]) . '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- ÉTAPE 4 : Compte Administrateur ---
if ($step == 4) {
    // Vérifier que la connexion à la base a été testée
    if (!isset($_SESSION['db_host'])) {
        header('Location: ?step=2');
        exit;
    }

    // Charger la config
    $env = parse_ini_file('../.env', true);
    // Recréer la connexion PDO
    $dsn = $_SESSION['db_dsn'];
    $pdo = init_db($dsn, $_SESSION['db_user'], $_SESSION['db_pass']);
    if (!$pdo) {
        header('Location: ?step=2');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Installation Maokaa CMS - Étape 4/5</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25); }
        </style>
    </head>
    <body class="min-vh-100 bg-light p-0">
        <nav class="navbar fixed-top bg-white px-3 py-3 border-bottom">
            <div class="container-fluid">
                <h2><i class="fas fa-cog me-2"></i> Installation Maokaa CMS</h2>
            </div>
        </nav>

        <div class="container-fluid mt-5 pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="progress mb-4" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 80%;"></div>
                    </div>

                    <h1 class="mb-4">Créer l'administrateur</h1>

                    <div class="card bg-white p-4 rounded-3 mb-4 shadow-sm">
                        <h2 class="mb-3"><i class="fas fa-user-shield me-2"></i> Compte Administrateur</h2>

                        <form method="POST" action="?step=5" id="admin-form">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label>Username</label>
                                    <input type="text" name="admin_username" value="admin" class="form-control" required>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-12 col-md-6">
                                    <label>Email</label>
                                    <input type="email" name="admin_email" value="admin@maokaa.com" class="form-control" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label>Mot de passe</label>
                                    <input type="password" name="admin_password" class="form-control" placeholder="Minimum 8 chars, 1 majuscule, 1 chiffre" required>
                                    <small class="text-muted">Mot de passe : au moins 8 caractères, une majuscule et un chiffre</small>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-12 col-md-6">
                                    <label>Vérifier le mot de passe</label>
                                    <input type="password" name="admin_password_confirm" class="form-control" placeholder="Confirmez le mot de passe" required>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" name="create_admin" class="btn btn-primary flex-grow">
                                    <i class="fas fa-user-plus me-2"></i>Créer l'admin
                                </button>
                                <button type="button" onclick="window.location='?step=3'" class="btn btn-outline-secondary flex-grow">
                                    <i class="fas fa-arrow-left me-2"></i>Retour
                                </button>
                            </div>
                        </form>

                        <?php
                        // Créer l'admin si submit
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
                            $username = $_POST['admin_username'];
                            $email = $_POST['admin_email'];
                            $password = $_POST['admin_password'];
                            $confirm = $_POST['admin_password_confirm'];

                            if ($password !== $confirm) {
                                $errors[] = "Les mots de passe ne correspondent pas";
                            } elseif (!is_valid_password($password)) {
                                $errors[] = "Le mot de passe doit contenir au moins 8 caractères, 1 majuscule et 1 chiffre";
                            } else {
                                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                                try {
                                    $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash, fullname, email, active, must_change_password) VALUES (?, ?, ?, ?, 1, 1)");
                                    if ($stmt->execute([$username, $password_hash, 'Admin Maokaa', $email])) {
                                        $success[] = "Compte administrateur créé !";
                                        echo '<div class="mt-3 p-3 bg-success text-white rounded"><i class="fas fa-check me-2"></i>' . htmlspecialchars($success[0]) . '</div>';
                                        $_SESSION['admin_created'] = true;
                                    } else {
                                        $errors[] = "Erreur lors de la création de l'admin";
                                    }
                                } catch (PDOException $e) {
                                    if (strpos($e->getMessage(), 'username') !== false && strpos($e->getMessage(), 'unique') !== false) {
                                        $errors[] = "Le nom d'utilisateur '$username' existe déjà. Choisissez un autre.";
                                    } else {
                                        $errors[] = "Erreur : " . $e->getMessage();
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- ÉTAPE 5 : Finalisation ---
if ($step == 5) {
    // Vérifier que la connexion à la base a été testée
    if (!isset($_SESSION['db_host'])) {
        header('Location: ?step=2');
        exit;
    }

    // Recréer la connexion PDO
    $dsn = $_SESSION['db_dsn'];
    $pdo = init_db($dsn, $_SESSION['db_user'], $_SESSION['db_pass']);
    if (!$pdo) {
        header('Location: ?step=2');
        exit;
    }

    // Créer les modules par défaut
    $modules = [
        ['slug' => 'pages', 'name' => 'Pages CMS', 'type' => 'core', 'enabled' => 1, 'depends_on' => ''],
        ['slug' => 'media', 'name' => 'Médias', 'type' => 'annex', 'enabled' => 1, 'depends_on' => 'pages'],
        ['slug' => 'menus', 'name' => 'Menus', 'type' => 'annex', 'enabled' => 1, 'depends_on' => 'pages'],
        ['slug' => 'messages', 'name' => 'Messages', 'type' => 'annex', 'enabled' => 1, 'depends_on' => 'pages'],
        ['slug' => 'sliders', 'name' => 'Sliders', 'type' => 'annex', 'enabled' => 1, 'depends_on' => 'pages'],
        ['slug' => 'products', 'name' => 'Produits', 'type' => 'addon', 'enabled' => 1, 'depends_on' => 'pages,media'],
        ['slug' => 'brands', 'name' => 'Marques', 'type' => 'addon', 'enabled' => 0, 'depends_on' => 'pages,media'],
        ['slug' => 'partners', 'name' => 'Partenaires', 'type' => 'addon', 'enabled' => 0, 'depends_on' => 'pages,media'],
        ['slug' => 'news', 'name' => 'Actualités', 'type' => 'addon', 'enabled' => 0, 'depends_on' => 'pages,media'],
    ];

    foreach ($modules as $mod) {
        $stmt = $pdo->prepare("SELECT id FROM modules WHERE slug = ?");
        $stmt->execute([$mod['slug']]);

        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE modules SET enabled = 1 WHERE slug = ?");
            $stmt->execute([$mod['slug']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO modules (slug, name, type, enabled, depends_on) VALUES (?, ?, ?, 1, ?)");
            $stmt->execute([$mod['slug'], $mod['name'], $mod['type'], $mod['depends_on']]);
        }
    }

    // Créer les pages par défaut
    $default_pages = [
        ['slug' => 'home', 'title' => 'Accueil', 'template' => 'home', 'status' => 'published'],
        ['slug' => 'about', 'title' => 'À propos', 'template' => 'page', 'status' => 'published'],
        ['slug' => 'contact', 'title' => 'Contact', 'template' => 'page', 'status' => 'published'],
    ];

    foreach ($default_pages as $page) {
        $stmt = $pdo->prepare("SELECT id FROM content WHERE slug = ?");
        $stmt->execute([$page['slug']]);

        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO content (slug, title, template, status, language) VALUES (?, ?, ?, ?, 'fr')");
            $stmt->execute([$page['slug'], $page['title'], $page['template'], $page['status']]);
        }
    }

    // Créer le menu principal
    $stmt = $pdo->prepare("SELECT id FROM menus WHERE name = 'main'");
    $stmt->execute();

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO menus (name, label, description) VALUES ('main', 'Menu Principal', 'Menu de navigation principal du site')");
        $stmt->execute();

        // Créer les items de menu
        $menu_items = [
            ['title' => 'Accueil', 'url' => '/', 'icon' => 'fas fa-home', 'position' => 0],
            ['title' => 'A propos', 'url' => '/about', 'icon' => 'fas fa-info-circle', 'position' => 1],
            ['title' => 'Nos marques', 'url' => '/brands', 'icon' => 'fas fa-award', 'position' => 2],
            ['title' => 'Nos partenaires', 'url' => '/partners', 'icon' => 'fas fa-handshake', 'position' => 3],
            ['title' => 'Produits', 'url' => '/products', 'icon' => 'fas fa-box', 'position' => 4],
            ['title' => 'Actualités', 'url' => '/news', 'icon' => 'fas fa-newspaper', 'position' => 5],
            ['title' => 'Contact', 'url' => '/contact', 'icon' => 'fas fa-envelope', 'position' => 6],
        ];

        foreach ($menu_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO menu_items (menu_id, title, url, icon, position, active, params) VALUES ((SELECT id FROM menus WHERE name = 'main'), ?, ?, ?, ?, 1, '')");
            $stmt->execute([$item['title'], $item['url'], $item['icon'], $item['position']]);
        }
    }

    // Créer le fichier de verrouillage
    file_put_contents(__DIR__ . '/install.lock', date('Y-m-d H:i:s') . ' - Installation Maokaa CMS');

    // Configurer les permissions
    setup_directory_permissions([
        'assets/images',
        'assets/brochures',
        'assets/images/products',
        'assets/images/settings',
    ]);

    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Installation Maokaa CMS - Terminée</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="min-vh-100 bg-light p-0">
        <nav class="navbar fixed-top bg-white px-3 py-3 border-bottom">
            <div class="container-fluid">
                <h2><i class="fas fa-cog me-2"></i> Installation Maokaa CMS</h2>
            </div>
        </nav>

        <div class="container-fluid mt-5 pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="progress mb-4" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div>
                    </div>

                    <div class="card bg-white p-4 rounded-3 mb-4 shadow-sm text-center">
                        <i class="fas fa-check-circle" style="color: #28a745; font-size: 4rem;"></i>
                        <h1 class="mb-3">Installation Terminée !</h1>

                        <h3>Félicitations ! Maokaa CMS est maintenant installé.</h3>

                        <div class="mt-4 p-3 bg-success text-white rounded mb-4">
                            <i class="fas fa-check me-2"></i>Base de données configurée
                        </div>

                        <div class="mt-4 text-start">
                            <h4>Accès à l'administration :</h4>
                            <ul class="list-unstyled">
                                <li><strong>URL :</strong> http://localhost/Hebergement/maokaa/login.php</li>
                                <li><strong>Identifiant :</strong> admin</li>
                                <li><strong>Mot de passe :</strong> [mot de passe que vous avez créé]</li>
                            </ul>
                        </div>

                        <div class="mt-4 text-start">
                            <h4>Liens utiles :</h4>
                            <ul>
                                <li><a href="../index.php" target="_blank">Voir le site web</a></li>
                                <li><a href="../admin/dashboard.php" target="_blank">Tableau de bord admin</a></li>
                            </ul>
                        </div>

                        <div class="mt-4 bg-warning bg-opacity-10 p-3 rounded">
                            <small>
                                <strong>Important :</strong> Supprimez le dossier <code>install/</code> pour des raisons de sécurité.<br>
                                <strong>Attention :</strong> Changez le mot de passe administrateur par défaut lors de la première connexion.<br>
                                <strong>Lecture :</strong> Consultez le fichier <code>INSTALLATION.md</code> pour les procédures courantes.
                            </small>
                        </div>

                        <div class="mt-4">
                            <a href="../index.php" class="btn btn-primary me-2">
                                <i class="fas fa-home me-2"></i>Voir le site
                            </a>
                            <a href="javascript:location.reload()" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i>Recommencer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Redirection par défaut
header('Location: ?step=1');
exit;
