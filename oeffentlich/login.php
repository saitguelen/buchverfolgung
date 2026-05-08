<?php
require_once __DIR__ . '/../einbindungen/datenbank.php';
require_once __DIR__ . '/../einbindungen/authentifizierung.php';
require_once __DIR__ . '/../einbindungen/sprache.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    
    if (!check_rate_limit()) {
        $error = __('rate_limit_exceeded');
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            reset_login_attempts();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $username;
            session_regenerate_id(true);
            header('Location: dashboard.php');
            exit;
        } else {
            record_login_attempt();
            $error = __('login_failed');
        }
    }
}
require __DIR__ . '/../einbindungen/kopf.php';
?>
<div class="auth-container card">
    <h1 class="text-center gradient-text"><?= __('login') ?></h1>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="form-group">
            <label><?= __('username') ?></label>
            <input type="text" name="username" required autofocus>
        </div>
        <div class="form-group">
            <label><?= __('password') ?></label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn" style="width: 100%"><?= __('login') ?></button>
    </form>
    <div class="text-center mt-4">
        <a href="registrieren.php" style="color: var(--text-secondary);"><?= __('no_account') ?></a>
    </div>
</div>
<?php require __DIR__ . '/../einbindungen/fuss.php'; ?>
