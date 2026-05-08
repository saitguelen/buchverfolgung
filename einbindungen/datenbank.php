<?php
// Datenbankverbindung herstellen und Tabellen initialisieren
$db_url_env = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;
if ($db_url_env) {
    $url = parse_url($db_url_env);
    $host = $url['host'];
    $port = $url['port'] ?? 5432;
    $dbname = ltrim($url['path'], '/');
    $user = $url['user'];
    $pass = $url['pass'];
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
} else {
    // Fallback für lokale Entwicklung
    $dsn = "pgsql:host=localhost;port=5432;dbname=buchverfolgung";
    $user = "postgres";
    $pass = "postgres";
}

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Datenbankverbindungsfehler: " . $e->getMessage());
}

// Tabellen automatisch erstellen
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS goals (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    target INTEGER NOT NULL,
    unit VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS progress (
    id SERIAL PRIMARY KEY,
    goal_id INTEGER REFERENCES goals(id) ON DELETE CASCADE,
    week_start DATE NOT NULL,
    current_value INTEGER DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(goal_id, week_start)
);
";
$pdo->exec($sql);

// Ersten Admin erstellen, falls keine Benutzer existieren
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
if ($stmt->fetchColumn() == 0) {
    $admin_password = getenv('ADMIN_PASSWORD') ?: 'admin123';
    $hash = password_hash($admin_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES ('admin', ?, 'admin')");
    $stmt->execute([$hash]);
}
?>
