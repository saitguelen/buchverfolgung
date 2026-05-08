<?php
require_once __DIR__ . '/../einbindungen/datenbank.php';
require_once __DIR__ . '/../einbindungen/authentifizierung.php';
require_once __DIR__ . '/../einbindungen/sprache.php';
require_once __DIR__ . '/../einbindungen/funktionen.php';

require_login();

$week_start = get_week_start();
$stats = get_weekly_stats($pdo, $week_start);

// Vorbereiten der WhatsApp-Nachrichtendaten
$msg_vars = [
    '{champion}' => $stats['champion'],
    '{done_count}' => count($stats['finished']),
    '{cont_count}' => count($stats['continuing'])
];

$wa_motivational = str_replace(array_keys($msg_vars), array_values($msg_vars), __('msg_tpl_motivational'));
$wa_funny = str_replace(array_keys($msg_vars), array_values($msg_vars), __('msg_tpl_funny'));
$wa_simple = str_replace(array_keys($msg_vars), array_values($msg_vars), __('msg_tpl_simple'));

require __DIR__ . '/../einbindungen/kopf.php';
?>

<h1 class="gradient-text"><?= __('dashboard') ?></h1>
<p><?= __('welcome') ?> <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</p>

<div class="dashboard-grid mt-4">
    <div class="card">
        <h2>🏆 <?= __('weekly_champion') ?></h2>
        <h1 style="font-size: 3rem; text-align: center; margin: 2rem 0;"><?= htmlspecialchars($stats['champion']) ?></h1>
        <div class="flex justify-between">
            <div>
                <h3 style="color: var(--success-color)">✓ <?= __('finished') ?> (<?= count($stats['finished']) ?>)</h3>
                <ul style="list-style-type: none; opacity: 0.8;">
                    <?php foreach ($stats['finished'] as $u): ?>
                        <li><?= htmlspecialchars($u) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h3 style="color: var(--text-secondary)">⏳ <?= __('continuing') ?> (<?= count($stats['continuing']) ?>)</h3>
                <ul style="list-style-type: none; opacity: 0.8;">
                    <?php foreach ($stats['continuing'] as $u): ?>
                        <li><?= htmlspecialchars($u) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>📱 <?= __('generate_message') ?></h2>
        <div class="flex gap-4">
            <button class="btn" onclick="generateWhatsAppMessage('motivational')"><?= __('msg_motivational') ?></button>
            <button class="btn" onclick="generateWhatsAppMessage('funny')"><?= __('msg_funny') ?></button>
            <button class="btn" onclick="generateWhatsAppMessage('simple')"><?= __('msg_simple') ?></button>
        </div>
        <div id="wa-msg-box" class="msg-box"></div>
        <button id="copy-btn" class="btn mt-4" style="width: 100%" onclick="copyMessage()"><?= __('copy_clipboard') ?></button>
    </div>
</div>

<h2 class="mt-4 mb-4"><?= __('leaderboard') ?></h2>
<div class="dashboard-grid">
    <?php foreach ($stats['users'] as $uid => $data): if (!$data['has_goals']) continue; ?>
        <div class="card" style="<?= $data['all_done'] ? 'border-color: var(--success-color);' : '' ?>">
            <div class="flex justify-between items-center mb-4">
                <h3><?= htmlspecialchars($data['username']) ?></h3>
                <?php if ($data['all_done']): ?>
                    <span style="color: var(--success-color); font-weight: bold;"><?= __('status_done') ?></span>
                <?php endif; ?>
            </div>
            
            <?php foreach ($data['goals'] as $goal): 
                $percent = $goal['target'] > 0 ? min(100, ($goal['current_value'] / $goal['target']) * 100) : 0;
            ?>
                <div class="progress-container">
                    <div class="progress-header">
                        <span><?= htmlspecialchars($goal['goal_name']) ?></span>
                        <span><?= $goal['current_value'] ?> / <?= $goal['target'] ?> <?= htmlspecialchars($goal['unit']) ?></span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= $percent ?>%; <?= $percent == 100 ? 'background: var(--success-color);' : '' ?>"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
window.waMessageData = {
    motivational: <?= json_encode($wa_motivational) ?>,
    funny: <?= json_encode($wa_funny) ?>,
    simple: <?= json_encode($wa_simple) ?>
};
window.waCopiedText = <?= json_encode(__('copied')) ?>;
</script>

<?php require __DIR__ . '/../einbindungen/fuss.php'; ?>
