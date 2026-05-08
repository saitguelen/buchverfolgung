<?php
// Sprachensystem
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erlaubte_sprachen = ['tr', 'de', 'en'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $erlaubte_sprachen)) {
    $_SESSION['lang'] = $_GET['lang'];
}

$aktuelle_sprache = $_SESSION['lang'] ?? 'tr'; // Varsayılan Türkçe

$lang = require __DIR__ . "/../sprachen/{$aktuelle_sprache}.php";

function __($schluessel) {
    global $lang;
    return $lang[$schluessel] ?? $schluessel;
}
?>
