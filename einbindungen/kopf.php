<?php
require_once __DIR__ . '/authentifizierung.php';
require_once __DIR__ . '/sprache.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($aktuelle_sprache) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buch Verfolgung</title>
    <link rel="stylesheet" href="stil.css">
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">📚 BuchClub</a>
        <nav>
            <?php if (is_logged_in()): ?>
                <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><?= __('dashboard') ?></a>
                <a href="ziele.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ziele.php' ? 'active' : '' ?>"><?= __('my_goals') ?></a>
                <?php if (is_admin()): ?>
                    <a href="admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : '' ?>"><?= __('admin_panel') ?></a>
                <?php endif; ?>
                <a href="abmelden.php"><?= __('logout') ?></a>
            <?php else: ?>
                <a href="login.php" class="<?= basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : '' ?>"><?= __('login') ?></a>
                <a href="registrieren.php" class="<?= basename($_SERVER['PHP_SELF']) == 'registrieren.php' ? 'active' : '' ?>"><?= __('register') ?></a>
            <?php endif; ?>
        </nav>
        <div class="lang-selector">
            <form action="" method="GET">
                <?php foreach ($_GET as $key => $val): if ($key != 'lang'): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>">
                <?php endif; endforeach; ?>
                <button type="submit" name="lang" value="tr" class="<?= $aktuelle_sprache == 'tr' ? 'active' : '' ?>">🇹🇷</button>
                <button type="submit" name="lang" value="de" class="<?= $aktuelle_sprache == 'de' ? 'active' : '' ?>">🇩🇪</button>
                <button type="submit" name="lang" value="en" class="<?= $aktuelle_sprache == 'en' ? 'active' : '' ?>">🇬🇧</button>
            </form>
        </div>
    </header>
    <main>
