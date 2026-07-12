<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/db.php';
    $pdo = get_pdo();
    ensure_schema($pdo);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM app_settings WHERE setting_key IN ('username', 'handler')");
    $rows = $stmt->fetchAll();

    $settings = [
        'username' => '',
        'handler' => '',
    ];

    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'] ?? '';
    }

    json_response(200, ['settings' => $settings]);
}

if ($method === 'POST') {
    $body = read_json_body();

    $key = isset($body['key']) ? trim((string) $body['key']) : '';
    $value = isset($body['value']) ? trim((string) $body['value']) : '';

    if (!in_array($key, ['username', 'handler'], true)) {
        json_response(422, ['error' => 'Invalid setting key']);
    }

    $stmt = $pdo->prepare('INSERT INTO app_settings (setting_key, setting_value) VALUES (:setting_key, :setting_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP');
    $stmt->execute([
        ':setting_key' => $key,
        ':setting_value' => $value,
    ]);

    json_response(200, ['ok' => true]);
}

json_response(405, ['error' => 'Method not allowed']);
