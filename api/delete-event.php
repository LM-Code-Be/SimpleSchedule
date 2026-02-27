<?php

declare(strict_types=1);

use App\Application\Service\EventService;

$container = require __DIR__ . '/../bootstrap/app.php';
$eventService = $container->get(EventService::class);

header('Content-Type: application/json; charset=utf-8');

$id = isset($_POST['id']) ? (int) $_POST['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Missing id'], JSON_THROW_ON_ERROR);
    exit;
}

$eventService->delete($id);
echo json_encode(['success' => true], JSON_THROW_ON_ERROR);
