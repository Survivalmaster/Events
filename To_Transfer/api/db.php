<?php
declare(strict_types=1);

$dbHost = 'localhost';
$dbName = 'EventsTeam';
$dbUser = 'EventsAdmin';
$dbPass = 'BloYq^nr5j4W@ld2';
$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    throw new PDOException('Database connection failed.', 0, $e);
}

function json_response(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        json_response(400, ['error' => 'Invalid JSON payload']);
    }

    return $decoded;
}

function get_pdo(): PDO
{
    global $pdo;
    return $pdo;
}

function ensure_schema(PDO $pdo): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status VARCHAR(20) NOT NULL DEFAULT 'NEW',
            handler VARCHAR(100) NOT NULL DEFAULT '',
            type VARCHAR(20) NOT NULL DEFAULT '',
            event_date DATE NOT NULL,
            event_time TIME NOT NULL,
            name VARCHAR(255) NOT NULL,
            district VARCHAR(255) NOT NULL DEFAULT '',
            discord VARCHAR(500) NOT NULL DEFAULT '',
            banner_url VARCHAR(1000) NOT NULL DEFAULT '',
            banner_pos_x TINYINT UNSIGNED NOT NULL DEFAULT 50,
            banner_pos_y TINYINT UNSIGNED NOT NULL DEFAULT 50,
            banner_zoom DECIMAL(4,2) NOT NULL DEFAULT 1.00,
            description TEXT NOT NULL,
            property_id VARCHAR(50) NOT NULL,
            notes TEXT NOT NULL,
            PRIMARY KEY (id),
            KEY idx_event_date (event_date),
            KEY idx_status (status)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS app_settings (
            setting_key VARCHAR(50) NOT NULL,
            setting_value TEXT NOT NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (setting_key)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS environmental_events (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            event_id VARCHAR(50) NOT NULL DEFAULT '',
            weight TINYINT UNSIGNED NOT NULL DEFAULT 5,
            faction_flags VARCHAR(255) NOT NULL DEFAULT '',
            type VARCHAR(50) NOT NULL DEFAULT '',
            name VARCHAR(255) NOT NULL,
            district VARCHAR(255) NOT NULL DEFAULT '',
            banner_url VARCHAR(1000) NOT NULL DEFAULT '',
            banner_pos_x TINYINT UNSIGNED NOT NULL DEFAULT 50,
            banner_pos_y TINYINT UNSIGNED NOT NULL DEFAULT 50,
            banner_zoom DECIMAL(4,2) NOT NULL DEFAULT 1.00,
            label TEXT NULL,
            PRIMARY KEY (id),
            KEY idx_env_name (name),
            KEY idx_env_weight (weight)
        )
    ");

    ensure_column($pdo, 'events', 'banner_url', "VARCHAR(1000) NOT NULL DEFAULT ''");
    ensure_column($pdo, 'events', 'banner_pos_x', 'TINYINT UNSIGNED NOT NULL DEFAULT 50');
    ensure_column($pdo, 'events', 'banner_pos_y', 'TINYINT UNSIGNED NOT NULL DEFAULT 50');
    ensure_column($pdo, 'events', 'banner_zoom', 'DECIMAL(4,2) NOT NULL DEFAULT 1.00');

    ensure_column($pdo, 'environmental_events', 'event_id', "VARCHAR(50) NOT NULL DEFAULT ''");
    ensure_column($pdo, 'environmental_events', 'faction_flags', "VARCHAR(255) NOT NULL DEFAULT ''");
    ensure_column($pdo, 'environmental_events', 'weight', 'TINYINT UNSIGNED NOT NULL DEFAULT 5');
    ensure_column($pdo, 'environmental_events', 'label', "TEXT NULL");
    ensure_column($pdo, 'environmental_events', 'banner_url', "VARCHAR(1000) NOT NULL DEFAULT ''");
    ensure_column($pdo, 'environmental_events', 'banner_pos_x', 'TINYINT UNSIGNED NOT NULL DEFAULT 50');
    ensure_column($pdo, 'environmental_events', 'banner_pos_y', 'TINYINT UNSIGNED NOT NULL DEFAULT 50');
    ensure_column($pdo, 'environmental_events', 'banner_zoom', 'DECIMAL(4,2) NOT NULL DEFAULT 1.00');

    $initialized = true;
}

function ensure_column(PDO $pdo, string $tableName, string $columnName, string $definition): void
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*) AS col_count
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :table_name
          AND COLUMN_NAME = :column_name
    ');
    $stmt->execute([
        ':table_name' => $tableName,
        ':column_name' => $columnName,
    ]);

    $exists = (int) ($stmt->fetch()['col_count'] ?? 0) > 0;
    if ($exists) {
        return;
    }

    $pdo->exec("ALTER TABLE {$tableName} ADD COLUMN {$columnName} {$definition}");
}
