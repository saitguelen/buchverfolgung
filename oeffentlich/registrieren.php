<?php
require_once __DIR__ . '/../einbindungen/datenbank.php';
require_once __DIR__ . '/../einbindungen/authentifizierung.php';
require_once __DIR__ . '/../einbindungen/sprache.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_repeat = $_POST['password_repeat'] ?? '';
    $invite_code = $_POST['invite_code'] ?? '';
    
    $expected_code = getenv('INVITE_CODE') ?: 'bookclub2026';
    
    if ($invite_code !== $expected_code) {
        $error = __('invalid_invite_code');
    } elseif (strlen($password) < 6) {
        $error = __('password_too_short');
    } elseif ($password !== $password_repeat) {
        $error = __('passwords_not_match');
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = __('username_taken');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'member')");
            if ($stmt->execute([$username, $hash])) {
                $success = __('register_success');
            }
        }
    }
}
require __DIR__ . '/../einbindungen/kopf.php';
?>
<div class="auth-container card">
    <h1 class="text-center gradient-text"><?= __('register') ?></h1>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php else: ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-group">
                <label><?= __('username') ?></label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label><?= __('password') ?></label>
                <input type="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label><?= __('password_repeat') ?></label>
                <input type="password" name="password_repeat" required minlength="6">
            </div>
            <div class="form-group">
                <label><?= __('invite_code') ?></label>
                <input type="text" name="invite_code" required>
            </div>
            <button type="submit" class="btn" style="width: 100%"><?= __('register') ?></button>
        </form>
    <?php endif; ?>
    <div class="text-center mt-4">
        <a href="login.php" style="color: var(--text-secondary);"><?= __('has_account') ?></a>
    </div>
</div>
<?php require __DIR__ . '/../einbindungen/fuss.php'; ?>
