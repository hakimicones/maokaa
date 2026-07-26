<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/models/User.php';

requirePasswordChange();
requireRole('admin');

$UserModel = new User($pdo);
$UserId    = (int)($_GET['id'] ?? 0);
$User      = $UserModel->getById($UserId);

if (!$User) {
    http_response_code(404);
    echo '<h1>Utilisateur introuvable</h1>';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'fullname' => trim($_POST['fullname'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'active'   => isset($_POST['active']) ? 1 : 0,
        ];

        // Nouveau mot de passe seulement si renseigné
        if (!empty(trim($_POST['password'] ?? ''))) {
            $data['password'] = trim($_POST['password']);
        }

        $errors = validateForm($data, [
            'username' => 'required|min:2',
        ]);

        if (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors['password'] = 'Minimum 8 caractères requis.';
        }

        if (!empty($errors)) {
            $error = reset($errors);
        } else {
            if ($UserModel->update($UserId, $data)) {
                setFlash('success', 'Utilisateur mis à jour avec succès');
                header('Location: ' . BASE_URL . 'admin/dashboard.php?section=users');
                exit;
            }
            $error = 'Erreur lors de la mise à jour de l\'utilisateur';
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
    <title>Modifier un utilisateur - VEP Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }
        .form-card { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,.06); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Modifier un utilisateur</h2>
            <p class="text-muted mb-0">Mettez à jour les informations de l'utilisateur.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php?section=users" class="btn btn-outline-secondary">Retour</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom d'utilisateur *</label>
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($User['username']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($User['fullname'] ?? ''); ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($User['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Nouveau mot de passe <small class="text-muted">(laisser vide pour ne pas changer)</small></label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="active" name="active" <?php echo !empty($User['active']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="active">Actif</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>
</body>
</html>