<?php

declare(strict_types=1);

use App\Application\Service\EventService;

$container = require __DIR__ . '/../bootstrap/app.php';
$eventService = $container->get(EventService::class);

header('Content-Type: application/json; charset=utf-8');

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$id = (int) ($payload['id'] ?? 0);
$isDone = (bool) (int) ($payload['is_done'] ?? 0);

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Missing id'], JSON_THROW_ON_ERROR);
    exit;
}

$eventService->toggleTask($id, $isDone);
echo json_encode(['success' => true], JSON_THROW_ON_ERROR);
