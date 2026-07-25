<?php
// login.php
// Page de connexion administrateur

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Si déjà connecté, rediriger vers dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

$error = '';

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isLoginRateLimited()) {
        $error = 'Trop de tentatives échouées. Réessayez dans 15 minutes.';
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Valider les données
        $errors = validateForm(
            ['username' => $username, 'password' => $password],
            ['username' => 'required|min:2', 'password' => 'required|min:6']
        );
        
        if (empty($errors)) {
            if (login($username, $password)) {
                setFlash('success', 'Connexion réussie !');
                if (isPasswordChangeRequired()) {
                    header('Location: ' . BASE_URL . 'admin/change-password.php');
                } else {
                    header('Location: ' . BASE_URL . 'admin/dashboard.php');
                }
                exit;
            } else {
                $error = 'Identifiants invalides.';
            }
        } else {
            $error = reset($errors);
        }
    }
}

// Vérifier le token CSRF
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - VEP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #435980 0%, #345075 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 12px 15px;
            font-size: 14px;
            margin-bottom: 15px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #435980;
            box-shadow: 0 0 0 0.2rem rgba(67, 89, 128, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #435980 0%, #345075 100%);
            border: none;
            border-radius: 5px;
            color: white;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            border-radius: 5px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>VEP Admin</h1>
                <p>Connexion Administrateur</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group">
                    <label for="username" class="form-label" style="display: block; margin-bottom: 5px; font-size: 14px; color: #333;">Nom d'utilisateur</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="username" 
                        name="username" 
                        placeholder="Entrez votre nom d'utilisateur"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label" style="display: block; margin-bottom: 5px; font-size: 14px; color: #333;">Mot de passe</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Entrez votre mot de passe"
                        required
                    >
                </div>

                <button type="submit" class="btn-login">Se connecter</button>
            </form>

            <div class="footer-text">
                <p>© 2024 VEP - Tous droits réservés</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
