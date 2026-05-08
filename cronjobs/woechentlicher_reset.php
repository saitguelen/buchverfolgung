<?php
// Wöchentlicher Reset durch Cronjob (oder Railway Trigger)
// php cronjobs/woechentlicher_reset.php
require_once __DIR__ . '/../einbindungen/datenbank.php';

if (php_sapi_name() !== 'cli') {
    $token = $_GET['token'] ?? '';
    if ($token !== getenv('CRON_TOKEN')) {
        die("Zugriff verweigert");
    }
}

$now = new DateTime();
$day = $now->format('w');
if ($day == 1) {
    $week_start = $now->format('Y-m-d');
} else {
    $week_start = $now->modify('last monday')->format('Y-m-d');
}

$stmt = $pdo->query("SELECT id FROM goals");
$goals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$insert = $pdo->prepare("INSERT INTO progress (goal_id, week_start, current_value) VALUES (?, ?, 0) ON CONFLICT DO NOTHING");

$count = 0;
foreach ($goals as $goal) {
    if ($insert->execute([$goal['id'], $week_start])) {
        $count += $insert->rowCount();
    }
}

echo "Reset für Woche $week_start abgeschlossen. $count neue Einträge initialisiert.\n";
?>
