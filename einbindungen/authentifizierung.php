<?php
// Session sicher starten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Fixation Schutz
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// CSRF Token generieren
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF Token prüfen
function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die("Ungültiges CSRF-Token.");
        }
    }
}

// Rate Limiting für Login
function check_rate_limit() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_last_attempt'] = time();
    }
    
    if ($_SESSION['login_attempts'] >= 5) {
        if (time() - $_SESSION['login_last_attempt'] < 900) { // 15 Minuten
            return false; // Gesperrt
        } else {
            $_SESSION['login_attempts'] = 0; // Zurücksetzen
        }
    }
    return true;
}

function record_login_attempt() {
    $_SESSION['login_attempts']++;
    $_SESSION['login_last_attempt'] = time();
}

function reset_login_attempts() {
    $_SESSION['login_attempts'] = 0;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        die("Zugriff verweigert. Nur für Administratoren.");
    }
}
?>
