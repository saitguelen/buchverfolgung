<?php
// Allgemeine Hilfsfunktionen

function get_week_start() {
    $now = new DateTime();
    $day = $now->format('w');
    if ($day == 1) {
        return $now->format('Y-m-d');
    }
    return $now->modify('last monday')->format('Y-m-d');
}

function get_all_users($pdo) {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY username ASC");
    return $stmt->fetchAll();
}

function get_user_goals($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM goals WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function get_goal_progress($pdo, $goal_id, $week_start) {
    $stmt = $pdo->prepare("SELECT current_value FROM progress WHERE goal_id = ? AND week_start = ?");
    $stmt->execute([$goal_id, $week_start]);
    return $stmt->fetchColumn() ?: 0;
}

function update_goal_progress($pdo, $goal_id, $week_start, $current_value) {
    $stmt = $pdo->prepare("
        INSERT INTO progress (goal_id, week_start, current_value) 
        VALUES (?, ?, ?) 
        ON CONFLICT (goal_id, week_start) 
        DO UPDATE SET current_value = EXCLUDED.current_value, updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$goal_id, $week_start, $current_value]);
}

function get_weekly_stats($pdo, $week_start) {
    $stmt = $pdo->prepare("
        SELECT 
            u.id as user_id, 
            u.username,
            g.id as goal_id,
            g.name as goal_name,
            g.target,
            g.unit,
            COALESCE(p.current_value, 0) as current_value
        FROM users u
        LEFT JOIN goals g ON u.id = g.user_id
        LEFT JOIN progress p ON g.id = p.goal_id AND p.week_start = ?
        ORDER BY u.username, g.id
    ");
    $stmt->execute([$week_start]);
    $results = $stmt->fetchAll();
    
    $stats = [];
    foreach ($results as $row) {
        $uid = $row['user_id'];
        if (!isset($stats[$uid])) {
            $stats[$uid] = [
                'username' => $row['username'],
                'goals' => [],
                'all_done' => true,
                'has_goals' => false
            ];
        }
        if ($row['goal_id']) {
            $stats[$uid]['has_goals'] = true;
            $is_done = $row['current_value'] >= $row['target'];
            if (!$is_done) {
                $stats[$uid]['all_done'] = false;
            }
            $stats[$uid]['goals'][] = $row;
        } else {
            $stats[$uid]['all_done'] = false;
        }
    }
    
    $finished = [];
    $continuing = [];
    $champion = null;
    $max_completion_ratio = -1;
    
    foreach ($stats as $uid => $data) {
        if (!$data['has_goals']) continue;
        
        $total_target = 0;
        $total_current = 0;
        
        foreach ($data['goals'] as $g) {
            $total_target += $g['target'];
            $total_current += min($g['current_value'], $g['target']);
        }
        
        $ratio = $total_target > 0 ? ($total_current / $total_target) : 0;
        
        if ($data['all_done']) {
            $finished[] = $data['username'];
        } else {
            $continuing[] = $data['username'];
        }
        
        if ($data['all_done'] && $ratio > $max_completion_ratio) {
            $max_completion_ratio = $ratio;
            $champion = $data['username'];
        }
    }
    
    if (!$champion && count($stats) > 0) {
        $highest = 0;
        foreach ($stats as $uid => $data) {
             if (!$data['has_goals']) continue;
             $total_target = 0;
             $total_current = 0;
             foreach ($data['goals'] as $g) {
                $total_target += $g['target'];
                $total_current += min($g['current_value'], $g['target']);
             }
             $ratio = $total_target > 0 ? ($total_current / $total_target) : 0;
             if ($ratio > $highest && $ratio > 0) {
                 $highest = $ratio;
                 $champion = $data['username'];
             }
        }
    }
    
    return [
        'users' => $stats,
        'finished' => $finished,
        'continuing' => $continuing,
        'champion' => $champion ?: '-'
    ];
}
?>
