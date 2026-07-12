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
    $hasWeight = false;
    $hasCreatedAt = false;
    $hasLabel = false;

    $columnCheck = $pdo->query("SHOW COLUMNS FROM environmental_events");
    if ($columnCheck !== false) {
        foreach ($columnCheck->fetchAll() as $column) {
            $field = (string) ($column['Field'] ?? '');
            if ($field === 'banner_url') $hasBannerUrl = true;
            if ($field === 'banner_pos_x') $hasBannerPosX = true;
            if ($field === 'banner_pos_y') $hasBannerPosY = true;
            if ($field === 'banner_zoom') $hasBannerZoom = true;
            if ($field === 'weight') $hasWeight = true;
            if ($field === 'created_at') $hasCreatedAt = true;
            if ($field === 'label') $hasLabel = true;
        }
    }

    $selectBannerUrl = $hasBannerUrl ? 'banner_url' : "'' AS banner_url";
    $selectBannerPosX = $hasBannerPosX ? 'banner_pos_x' : "50 AS banner_pos_x";
    $selectBannerPosY = $hasBannerPosY ? 'banner_pos_y' : "50 AS banner_pos_y";
    $selectBannerZoom = $hasBannerZoom ? 'banner_zoom' : "1.00 AS banner_zoom";
    $selectWeight = $hasWeight ? 'weight' : '5 AS weight';
    $selectCreatedAt = $hasCreatedAt ? 'created_at' : 'NULL AS created_at';
    $selectLabel = $hasLabel ? 'label' : "'' AS label";
    $orderBy = $hasCreatedAt ? 'created_at DESC, id DESC' : 'id DESC';

    $stmt = $pdo->query("SELECT id, {$selectCreatedAt}, event_id, faction_flags, {$selectWeight}, type, name, district, {$selectBannerUrl}, {$selectBannerPosX}, {$selectBannerPosY}, {$selectBannerZoom}, {$selectLabel} FROM environmental_events ORDER BY {$orderBy}");
    $rows = $stmt->fetchAll();

    $events = array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'createdAt' => $row['created_at'],
            'eventId' => $row['event_id'] ?? '',
            'event_id' => $row['event_id'] ?? '',
            'factionFlags' => $row['faction_flags'] ?? '',
            'faction_flags' => $row['faction_flags'] ?? '',
            'weight' => isset($row['weight']) ? (int) $row['weight'] : 5,
            'type' => $row['type'] ?? '',
            'name' => $row['name'] ?? '',
            'district' => $row['district'] ?? '',
            'bannerUrl' => $row['banner_url'] ?? '',
            'banner_url' => $row['banner_url'] ?? '',
            'bannerPosX' => isset($row['banner_pos_x']) ? (int) $row['banner_pos_x'] : 50,
            'banner_pos_x' => isset($row['banner_pos_x']) ? (int) $row['banner_pos_x'] : 50,
            'bannerPosY' => isset($row['banner_pos_y']) ? (int) $row['banner_pos_y'] : 50,
            'banner_pos_y' => isset($row['banner_pos_y']) ? (int) $row['banner_pos_y'] : 50,
            'bannerZoom' => isset($row['banner_zoom']) ? (float) $row['banner_zoom'] : 1.0,
            'banner_zoom' => isset($row['banner_zoom']) ? (float) $row['banner_zoom'] : 1.0,
            'label' => $row['label'] ?? '',
        ];
    }, $rows);

    json_response(200, ['events' => $events]);
}

if ($method === 'POST') {
    $body = read_json_body();
    $hasLabel = false;
    $columnCheck = $pdo->query("SHOW COLUMNS FROM environmental_events");
    if ($columnCheck !== false) {
        foreach ($columnCheck->fetchAll() as $column) {
            if ((string) ($column['Field'] ?? '') === 'label') {
                $hasLabel = true;
                break;
            }
        }
    }

    $id = isset($body['id']) ? (int) $body['id'] : null;
    $eventId = trim((string) ($body['eventId'] ?? ($body['event_id'] ?? '')));
    $factionFlags = trim((string) ($body['factionFlags'] ?? ($body['faction_flags'] ?? '')));
    $weight = (int) ($body['weight'] ?? 5);
    $type = trim((string) ($body['type'] ?? ''));
    $name = trim((string) ($body['name'] ?? ''));
    $district = trim((string) ($body['district'] ?? ''));
    $bannerUrlProvided = array_key_exists('bannerUrl', $body) || array_key_exists('banner_url', $body);
    $bannerPosXProvided = array_key_exists('bannerPosX', $body) || array_key_exists('banner_pos_x', $body);
    $bannerPosYProvided = array_key_exists('bannerPosY', $body) || array_key_exists('banner_pos_y', $body);
    $bannerZoomProvided = array_key_exists('bannerZoom', $body) || array_key_exists('banner_zoom', $body);
    $bannerUrl = trim((string) ($body['bannerUrl'] ?? ($body['banner_url'] ?? '')));
    $bannerPosX = (int) ($body['bannerPosX'] ?? ($body['banner_pos_x'] ?? 50));
    $bannerPosY = (int) ($body['bannerPosY'] ?? ($body['banner_pos_y'] ?? 50));
    $bannerZoom = (float) ($body['bannerZoom'] ?? ($body['banner_zoom'] ?? 1.0));
    $label = trim((string) ($body['label'] ?? ($body['description'] ?? '')));

    if ($bannerPosX < 0) $bannerPosX = 0;
    if ($bannerPosX > 100) $bannerPosX = 100;
    if ($bannerPosY < 0) $bannerPosY = 0;
    if ($bannerPosY > 100) $bannerPosY = 100;
    if ($bannerZoom < 0.5) $bannerZoom = 0.5;
    if ($bannerZoom > 3.0) $bannerZoom = 3.0;
    if ($weight < 1) $weight = 1;
    if ($weight > 10) $weight = 10;

    if ($name === '') {
        json_response(422, ['error' => 'Missing required fields']);
    }

    if ($id !== null && $id > 0) {
        if (!$bannerUrlProvided || !$bannerPosXProvided || !$bannerPosYProvided || !$bannerZoomProvided) {
            $existingBannerStmt = $pdo->prepare('SELECT banner_url, banner_pos_x, banner_pos_y, banner_zoom FROM environmental_events WHERE id = :id LIMIT 1');
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

        if ($hasLabel) {
            $stmt = $pdo->prepare('UPDATE environmental_events SET event_id = :event_id, faction_flags = :faction_flags, weight = :weight, type = :type, name = :name, district = :district, banner_url = :banner_url, banner_pos_x = :banner_pos_x, banner_pos_y = :banner_pos_y, banner_zoom = :banner_zoom, label = :label WHERE id = :id');
            $stmt->execute([
                ':event_id' => $eventId,
                ':faction_flags' => $factionFlags,
                ':weight' => $weight,
                ':type' => $type,
                ':name' => $name,
                ':district' => $district,
                ':banner_url' => $bannerUrl,
                ':banner_pos_x' => $bannerPosX,
                ':banner_pos_y' => $bannerPosY,
                ':banner_zoom' => $bannerZoom,
                ':label' => $label,
                ':id' => $id,
            ]);
        } else {
            $stmt = $pdo->prepare('UPDATE environmental_events SET event_id = :event_id, faction_flags = :faction_flags, weight = :weight, type = :type, name = :name, district = :district, banner_url = :banner_url, banner_pos_x = :banner_pos_x, banner_pos_y = :banner_pos_y, banner_zoom = :banner_zoom WHERE id = :id');
            $stmt->execute([
                ':event_id' => $eventId,
                ':faction_flags' => $factionFlags,
                ':weight' => $weight,
                ':type' => $type,
                ':name' => $name,
                ':district' => $district,
                ':banner_url' => $bannerUrl,
                ':banner_pos_x' => $bannerPosX,
                ':banner_pos_y' => $bannerPosY,
                ':banner_zoom' => $bannerZoom,
                ':id' => $id,
            ]);
        }

        if ($stmt->rowCount() === 0) {
            $existsStmt = $pdo->prepare('SELECT id FROM environmental_events WHERE id = :id LIMIT 1');
            $existsStmt->execute([':id' => $id]);
            if (!$existsStmt->fetch()) {
                json_response(404, ['error' => 'Event not found']);
            }
        }

        json_response(200, ['ok' => true, 'id' => $id]);
    }

    $hasCreatedAt = false;
    $columnCheck = $pdo->query("SHOW COLUMNS FROM environmental_events");
    if ($columnCheck !== false) {
        foreach ($columnCheck->fetchAll() as $column) {
            if ((string) ($column['Field'] ?? '') === 'created_at') {
                $hasCreatedAt = true;
                break;
            }
        }
    }

    if ($hasCreatedAt && $hasLabel) {
        $stmt = $pdo->prepare('INSERT INTO environmental_events (created_at, event_id, faction_flags, weight, type, name, district, banner_url, banner_pos_x, banner_pos_y, banner_zoom, label) VALUES (CURRENT_TIMESTAMP, :event_id, :faction_flags, :weight, :type, :name, :district, :banner_url, :banner_pos_x, :banner_pos_y, :banner_zoom, :label)');
        $stmt->execute([
            ':event_id' => $eventId,
            ':faction_flags' => $factionFlags,
            ':weight' => $weight,
            ':type' => $type,
            ':name' => $name,
            ':district' => $district,
            ':banner_url' => $bannerUrl,
            ':banner_pos_x' => $bannerPosX,
            ':banner_pos_y' => $bannerPosY,
            ':banner_zoom' => $bannerZoom,
            ':label' => $label,
        ]);
    } elseif ($hasCreatedAt) {
        $stmt = $pdo->prepare('INSERT INTO environmental_events (created_at, event_id, faction_flags, weight, type, name, district, banner_url, banner_pos_x, banner_pos_y, banner_zoom) VALUES (CURRENT_TIMESTAMP, :event_id, :faction_flags, :weight, :type, :name, :district, :banner_url, :banner_pos_x, :banner_pos_y, :banner_zoom)');
        $stmt->execute([
            ':event_id' => $eventId,
            ':faction_flags' => $factionFlags,
            ':weight' => $weight,
            ':type' => $type,
            ':name' => $name,
            ':district' => $district,
            ':banner_url' => $bannerUrl,
            ':banner_pos_x' => $bannerPosX,
            ':banner_pos_y' => $bannerPosY,
            ':banner_zoom' => $bannerZoom,
        ]);
    } elseif ($hasLabel) {
        $stmt = $pdo->prepare('INSERT INTO environmental_events (event_id, faction_flags, weight, type, name, district, banner_url, banner_pos_x, banner_pos_y, banner_zoom, label) VALUES (:event_id, :faction_flags, :weight, :type, :name, :district, :banner_url, :banner_pos_x, :banner_pos_y, :banner_zoom, :label)');
        $stmt->execute([
            ':event_id' => $eventId,
            ':faction_flags' => $factionFlags,
            ':weight' => $weight,
            ':type' => $type,
            ':name' => $name,
            ':district' => $district,
            ':banner_url' => $bannerUrl,
            ':banner_pos_x' => $bannerPosX,
            ':banner_pos_y' => $bannerPosY,
            ':banner_zoom' => $bannerZoom,
            ':label' => $label,
        ]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO environmental_events (event_id, faction_flags, weight, type, name, district, banner_url, banner_pos_x, banner_pos_y, banner_zoom) VALUES (:event_id, :faction_flags, :weight, :type, :name, :district, :banner_url, :banner_pos_x, :banner_pos_y, :banner_zoom)');
        $stmt->execute([
            ':event_id' => $eventId,
            ':faction_flags' => $factionFlags,
            ':weight' => $weight,
            ':type' => $type,
            ':name' => $name,
            ':district' => $district,
            ':banner_url' => $bannerUrl,
            ':banner_pos_x' => $bannerPosX,
            ':banner_pos_y' => $bannerPosY,
            ':banner_zoom' => $bannerZoom,
        ]);
    }

    json_response(201, ['ok' => true, 'id' => (int) $pdo->lastInsertId()]);
}

if ($method === 'DELETE') {
    $idParam = $_GET['id'] ?? '';
    $id = (int) $idParam;

    if ($id <= 0) {
        json_response(422, ['error' => 'Valid id is required']);
    }

    $stmt = $pdo->prepare('DELETE FROM environmental_events WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        json_response(404, ['error' => 'Event not found']);
    }

    json_response(200, ['ok' => true]);
}

json_response(405, ['error' => 'Method not allowed']);
