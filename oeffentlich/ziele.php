<?php
require_once __DIR__ . '/../einbindungen/datenbank.php';
require_once __DIR__ . '/../einbindungen/authentifizierung.php';
require_once __DIR__ . '/../einbindungen/sprache.php';
require_once __DIR__ . '/../einbindungen/funktionen.php';

require_login();

$user_id = $_SESSION['user_id'];
$week_start = get_week_start();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_goal') {
            $name = trim($_POST['name']);
            $target = (int)$_POST['target'];
            $unit = trim($_POST['unit']);
            
            if ($name && $target > 0 && $unit) {
                $stmt = $pdo->prepare("INSERT INTO goals (user_id, name, target, unit) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $target, $unit]);
                $success = __('save');
            }
        } elseif ($_POST['action'] === 'update_progress') {
            $goal_id = (int)$_POST['goal_id'];
            $current_value = (int)$_POST['current_value'];
            
            // Verify goal belongs to user
            $stmt = $pdo->prepare("SELECT id FROM goals WHERE id = ? AND user_id = ?");
            $stmt->execute([$goal_id, $user_id]);
            if ($stmt->fetch()) {
                update_goal_progress($pdo, $goal_id, $week_start, $current_value);
                $success = __('save');
            }
        } elseif ($_POST['action'] === 'delete_goal') {
            $goal_id = (int)$_POST['goal_id'];
            $stmt = $pdo->prepare("DELETE FROM goals WHERE id = ? AND user_id = ?");
            $stmt->execute([$goal_id, $user_id]);
            $success = __('delete');
        }
    }
}

$goals = get_user_goals($pdo, $user_id);

require __DIR__ . '/../einbindungen/kopf.php';
?>

<h1 class="gradient-text"><?= __('my_goals') ?></h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="dashboard-grid mt-4">
    <div class="card">
        <h2><?= __('add_goal') ?></h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="add_goal">
            
            <div class="form-group">
                <label><?= __('goal_name') ?></label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label><?= __('target_amount') ?></label>
                <input type="number" name="target" min="1" required>
            </div>
            <div class="form-group">
                <label><?= __('unit') ?></label>
                <input type="text" name="unit" required>
            </div>
            <button type="submit" class="btn"><?= __('save') ?></button>
        </form>
    </div>

    <div>
        <?php foreach ($goals as $goal): 
            $current = get_goal_progress($pdo, $goal['id'], $week_start);
            $percent = $goal['target'] > 0 ? min(100, ($current / $goal['target']) * 100) : 0;
        ?>
            <div class="card">
                <div class="flex justify-between items-center mb-4">
                    <h3><?= htmlspecialchars($goal['name']) ?></h3>
                    <form method="POST" onsubmit="return confirm('<?= __('delete') ?>?');">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="delete_goal">
                        <input type="hidden" name="goal_id" value="<?= $goal['id'] ?>">
                        <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;"><?= __('delete') ?></button>
                    </form>
                </div>
                
                <div class="progress-container">
                    <div class="progress-header">
                        <span><?= __('progress') ?></span>
                        <span><?= $current ?> / <?= $goal['target'] ?> <?= htmlspecialchars($goal['unit']) ?></span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= $percent ?>%; <?= $percent == 100 ? 'background: var(--success-color);' : '' ?>"></div>
                    </div>
                </div>
                
                <form method="POST" class="mt-4 flex gap-4 items-center">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="update_progress">
                    <input type="hidden" name="goal_id" value="<?= $goal['id'] ?>">
                    <input type="number" name="current_value" value="<?= $current ?>" min="0" required style="width: 100px;">
                    <button type="submit" class="btn"><?= __('update_progress') ?></button>
                </form>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($goals)): ?>
            <div class="card text-center" style="opacity: 0.6;">
                <p>Noch keine Ziele vorhanden.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../einbindungen/fuss.php'; ?>
