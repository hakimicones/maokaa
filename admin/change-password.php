<?php
// admin/change-password.php
// Oblige l'administrateur à modifier son mot de passe à la première connexion

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

if (!isPasswordChangeRequired()) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = validateForm(
            [
                'current_password' => $currentPassword,
                'new_password' => $newPassword,
                'confirm_password' => $confirmPassword,
            ],
            [
                'current_password' => 'required|min:6',
                'new_password' => 'required|min:8',
                'confirm_password' => 'required|min:8',
            ]
        );

        if (!empty($errors)) {
            $error = reset($errors);
        } else {
            $stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ? AND active = 1');
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($currentPassword, $admin['password_hash'])) {
                $error = 'Le mot de passe actuel est incorrect.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Les deux mots de passe ne correspondent pas.';
            } elseif (password_verify($newPassword, $admin['password_hash'])) {
                $error = 'Le nouveau mot de passe doit être différent de l\'ancien.';
            } else {
                $update = $pdo->prepare('UPDATE admins SET password_hash = ?, must_change_password = 0, updated_at = NOW() WHERE id = ?');
                if ($update->execute([password_hash($newPassword, PASSWORD_BCRYPT), $_SESSION['admin_id']])) {
                    setPasswordChangeRequired(false);
                    setFlash('success', 'Mot de passe mis à jour avec succès.');
                    header('Location: ' . BASE_URL . 'admin/dashboard.php');
                    exit;
                }

                $error = 'Impossible de mettre à jour le mot de passe.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe - VEP</title>
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
        .change-password-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 16px 50px rgba(0, 0, 0, 0.18);
            padding: 2rem;
            width: min(100%, 520px);
        }
        .form-control:focus {
            border-color: #435980;
            box-shadow: 0 0 0 0.2rem rgba(67, 89, 128, 0.2);
        }
    </style>
</head>
<body>
    <div class="change-password-card">
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold mb-2">Changer votre mot de passe</h1>
            <p class="text-muted mb-0">Pour des raisons de sécurité, vous devez remplacer le mot de passe par défaut avant d’accéder au dashboard.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <div class="mb-3">
                <label for="current_password" class="form-label">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required>
                <div class="form-text">Minimum 8 caractères.</div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Mettre à jour le mot de passe</button>
        </form>
    </div>
</body>
</html>
