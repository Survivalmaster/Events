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
    $hasBannerUrl = false;
    $hasBannerPosX = false;
    $hasBannerPosY = false;
    $hasBannerZoom = false;

    $columnCheck = $pdo->query("SHOW COLUMNS FROM events");
    if ($columnCheck !== false) {
        foreach ($columnCheck->fetchAll() as $column) {
            $field = (string) ($column['Field'] ?? '');
            if ($field === 'banner_url') $hasBannerUrl = true;
            if ($field === 'banner_pos_x') $hasBannerPosX = true;
            if ($field === 'banner_pos_y') $hasBannerPosY = true;
            if ($field === 'banner_zoom') $hasBannerZoom = true;
        }
    }

    $selectBannerUrl = $hasBannerUrl ? 'banner_url' : "'' AS banner_url";
    $selectBannerPosX = $hasBannerPosX ? 'banner_pos_x' : "50 AS banner_pos_x";
    $selectBannerPosY = $hasBannerPosY ? 'banner_pos_y' : "50 AS banner_pos_y";
    $selectBannerZoom = $hasBannerZoom ? 'banner_zoom' : "1.00 AS banner_zoom";

    $stmt = $pdo->query("SELECT id, created_at, status, handler, type, event_date, event_time, name, district, discord, {$selectBannerUrl}, {$selectBannerPosX}, {$selectBannerPosY}, {$selectBannerZoom}, description, property_id, notes FROM events ORDER BY created_at DESC, id DESC");
    $rows = $stmt->fetchAll();

    $events = array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'createdAt' => $row['created_at'],
            'status' => $row['status'],
            'handler' => $row['handler'] ?? '',
            'type' => $row['type'] ?? '',
            'date' => $row['event_date'] ?? '',
            'time' => $row['event_time'] ?? '',
            'name' => $row['name'] ?? '',
            'district' => $row['district'] ?? '',
            'discord' => $row['discord'] ?? '',
            'bannerUrl' => $row['banner_url'] ?? '',
            'banner_url' => $row['banner_url'] ?? '',
            'bannerPosX' => isset($row['banner_pos_x']) ? (int) $row['banner_pos_x'] : 50,
            'banner_pos_x' => isset($row['banner_pos_x']) ? (int) $row['banner_pos_x'] : 50,
            'bannerPosY' => isset($row['banner_pos_y']) ? (int) $row['banner_pos_y'] : 50,
            'banner_pos_y' => isset($row['banner_pos_y']) ? (int) $row['banner_pos_y'] : 50,
            'bannerZoom' => isset($row['banner_zoom']) ? (float) $row['banner_zoom'] : 1.0,
            'banner_zoom' => isset($row['banner_zoom']) ? (float) $row['banner_zoom'] : 1.0,
            'description' => $row['description'] ?? '',
            'propertyId' => $row['property_id'] ?? '',
            'property_id' => $row['property_id'] ?? '',
            'notes' => $row['notes'] ?? '',
        ];
    }, $rows);

    json_response(200, ['events' => $events]);
}

if ($method === 'POST') {
    $body = read_json_body();

    $id = isset($body['id']) ? (int) $body['id'] : null;
    $createdAt = isset($body['createdAt']) ? trim((string) $body['createdAt']) : null;
    $status = isset($body['status']) && trim((string) $body['status']) !== ''
        ? trim((string) $body['status'])
        : 'NEW';

    $handler = trim((string) ($body['handler'] ?? ''));
    $type = trim((string) ($body['type'] ?? ''));
    $date = trim((string) ($body['date'] ?? ''));
    $time = trim((string) ($body['time'] ?? ''));
    $name = trim((string) ($body['name'] ?? ''));
    $district = trim((string) ($body['district'] ?? ''));
    $discord = trim((string) ($body['discord'] ?? ''));
    $bannerUrlProvided = array_key_exists('bannerUrl', $body) || array_key_exists('banner_url', $body);
    $bannerPosXProvided = array_key_exists('bannerPosX', $body) || array_key_exists('banner_pos_x', $body);
    $bannerPosYProvided = array_key_exists('bannerPosY', $body) || array_key_exists('banner_pos_y', $body);
    $bannerZoomProvided = array_key_exists('bannerZoom', $body) || array_key_exists('banner_zoom', $body);
    $bannerUrl = trim((string) ($body['bannerUrl'] ?? ($body['banner_url'] ?? '')));
    $bannerPosX = (int) ($body['bannerPosX'] ?? ($body['banner_pos_x'] ?? 50));
    $bannerPosY = (int) ($body['bannerPosY'] ?? ($body['banner_pos_y'] ?? 50));
    $bannerZoom = (float) ($body['bannerZoom'] ?? ($body['banner_zoom'] ?? 1.0));
    $description = trim((string) ($body['description'] ?? ''));
    $propertyId = trim((string) ($body['propertyId'] ?? ($body['property_id'] ?? '')));
    $notes = trim((string) ($body['notes'] ?? ''));
    if ($bannerPosX < 0) $bannerPosX = 0;
    if ($bannerPosX > 100) $bannerPosX = 100;
    if ($bannerPosY < 0) $bannerPosY = 0;
    if ($bannerPosY > 100) $bannerPosY = 100;
    if ($bannerZoom < 0.5) $bannerZoom = 0.5;
    if ($bannerZoom > 3.0) $bannerZoom = 3.0;

    if ($name === '' || $date === '' || $time === '' || $propertyId === '') {
        json_response(422, ['error' => 'Missing required fields']);
    }

    if ($id !== null && $id > 0) {
        if (!$bannerUrlProvided || !$bannerPosXProvided || !$bannerPosYProvided || !$bannerZoomProvided) {
            $existingBannerStmt = $pdo->prepare('SELECT banner_url, banner_pos_x, banner_pos_y, banner_zoom FROM events WHERE id = :id LIMIT 1');
            $existingBannerStmt->execute([':id' => $id]);
            $existingBanner = $existingBannerStmt->fetch();
            if (!$existingBanner) {
                json_response(404, ['error' => 'Event not found']);
            }

            if (!$bannerUrlProvided) $bannerUrl = (string) ($existingBanner['banner_url'] ?? '');
            if (!$bannerPosXProvided) $bannerPosX = (int) ($existingBanner['banner_pos_x'] ?? 50);
            if (!$bannerPosYProvided) $bannerPosY = (int) ($existingBanner['banner_pos_y'] ?? 50);
            if (!$bannerZoomProvided) $bannerZoom = (float) ($existingBanner['banner_zoom'] ?? 1.0);
        }

        $stmt = $pdo->prepare('UPDATE events SET status = :status, handler = :handler, type = :type, event_date = :event_date, event_time = :event_time, name = :name, district = :district, discord = :discord, banner_url = :banner_url, banner_pos_x = :banner_pos_x, banner_pos_y = :banner_pos_y, banner_zoom = :banner_zoom, description = :description, property_id = :property_id, notes = :notes, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            ':status' => $status,
            ':handler' => $handler,
            ':type' => $type,
            ':event_date' => $date,
            ':event_time' => $time,
            ':name' => $name,
            ':district' => $district,
            ':discord' => $discord,
            ':banner_url' => $bannerUrl,
            ':banner_pos_x' => $bannerPosX,
            ':banner_pos_y' => $bannerPosY,
            ':banner_zoom' => $bannerZoom,
            ':description' => $description,
            ':property_id' => $propertyId,
            ':notes' => $notes,
            ':id' => $id,
        ]);

        if ($stmt->rowCount() === 0) {
            $existsStmt = $pdo->prepare('SELECT id FROM events WHERE id = :id LIMIT 1');
            $existsStmt->execute([':id' => $id]);
            if (!$existsStmt->fetch()) {
                json_response(404, ['error' => 'Event not found']);
            }
        }

        json_response(200, ['ok' => true, 'id' => $id]);
    }

    $stmt = $pdo->prepare('INSERT INTO events (created_at, status, handler, type, event_date, event_time, name, district, discord, banner_url, banner_pos_x, banner_pos_y, banner_zoom, description, property_id, notes) VALUES (:created_at, :status, :handler, :type, :event_date, :event_time, :name, :district, :discord, :banner_url, :banner_pos_x, :banner_pos_y, :banner_zoom, :description, :property_id, :notes)');
    $stmt->execute([
        ':created_at' => $createdAt !== null && $createdAt !== '' ? $createdAt : gmdate('Y-m-d H:i:s'),
        ':status' => $status,
        ':handler' => $handler,
        ':type' => $type,
        ':event_date' => $date,
        ':event_time' => $time,
        ':name' => $name,
        ':district' => $district,
        ':discord' => $discord,
        ':banner_url' => $bannerUrl,
        ':banner_pos_x' => $bannerPosX,
        ':banner_pos_y' => $bannerPosY,
        ':banner_zoom' => $bannerZoom,
        ':description' => $description,
        ':property_id' => $propertyId,
        ':notes' => $notes,
    ]);

    json_response(201, ['ok' => true, 'id' => (int) $pdo->lastInsertId()]);
}

if ($method === 'DELETE') {
    $idParam = $_GET['id'] ?? '';
    $id = (int) $idParam;

    if ($id <= 0) {
        json_response(422, ['error' => 'Valid id is required']);
    }

    $stmt = $pdo->prepare('DELETE FROM events WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        json_response(404, ['error' => 'Event not found']);
    }

    json_response(200, ['ok' => true]);
}

json_response(405, ['error' => 'Method not allowed']);
