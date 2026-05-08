<?php
require_once __DIR__ . '/../einbindungen/datenbank.php';
require_once __DIR__ . '/../einbindungen/authentifizierung.php';
require_once __DIR__ . '/../einbindungen/sprache.php';
require_once __DIR__ . '/../einbindungen/funktionen.php';

require_admin();

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete_user') {
            $user_id = (int)$_POST['user_id'];
            if ($user_id !== $_SESSION['user_id']) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $success = __('delete');
            }
        } elseif ($_POST['action'] === 'add_goal') {
            $user_id = (int)$_POST['user_id'];
            $name = trim($_POST['name']);
            $target = (int)$_POST['target'];
            $unit = trim($_POST['unit']);
            
            if ($user_id && $name && $target > 0 && $unit) {
                $stmt = $pdo->prepare("INSERT INTO goals (user_id, name, target, unit) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $target, $unit]);
                $success = __('save');
            }
        } elseif ($_POST['action'] === 'delete_goal') {
            $goal_id = (int)$_POST['goal_id'];
            $stmt = $pdo->prepare("DELETE FROM goals WHERE id = ?");
            $stmt->execute([$goal_id]);
            $success = __('delete');
        } elseif ($_POST['action'] === 'reset_weekly') {
            $week_start = get_week_start();
            $stmt = $pdo->prepare("UPDATE progress SET current_value = 0 WHERE week_start = ?");
            $stmt->execute([$week_start]);
            $success = __('reset_weekly') . ' - ' . __('status_done');
        }
    }
}

$users = get_all_users($pdo);
$week_start = get_week_start();
$stats = get_weekly_stats($pdo, $week_start);

require __DIR__ . '/../einbindungen/kopf.php';
?>

<h1 class="gradient-text"><?= __('admin_panel') ?></h1>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="dashboard-grid mt-4">
    <div class="card">
        <h2><?= __('members') ?></h2>
        <table>
            <thead>
                <tr>
                    <th><?= __('username') ?></th>
                    <th>Rolle</th>
                    <th><?= __('delete') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                <form method="POST" onsubmit="return confirm('<?= __('delete') ?>?');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"><?= __('delete') ?></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2><?= __('add_goal') ?> (für andere)</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="add_goal">
            
            <div class="form-group">
                <label>Benutzer</label>
                <select name="user_id" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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
</div>

<div class="card mt-4">
    <h2><?= __('detailed_report') ?></h2>
    <table>
        <thead>
            <tr>
                <th><?= __('username') ?></th>
                <th>Ziel</th>
                <th><?= __('progress') ?></th>
                <th><?= __('delete') ?> (Ziel)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats['users'] as $uid => $data): ?>
                <?php foreach ($data['goals'] as $goal): ?>
                    <tr>
                        <td><?= htmlspecialchars($data['username']) ?></td>
                        <td><?= htmlspecialchars($goal['goal_name']) ?> (<?= $goal['target'] ?> <?= htmlspecialchars($goal['unit']) ?>)</td>
                        <td><?= $goal['current_value'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('<?= __('delete') ?>?');">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="delete_goal">
                                <input type="hidden" name="goal_id" value="<?= $goal['goal_id'] ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"><?= __('delete') ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="mt-4" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
        <form method="POST" onsubmit="return confirm('Wirklich alles auf 0 setzen?');">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="reset_weekly">
            <button type="submit" class="btn btn-danger"><?= __('reset_weekly') ?></button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../einbindungen/fuss.php'; ?>
