<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../helpers.php';

header('Content-Type: application/json');

$u = require_login();
$action = $_GET['action'] ?? '';

switch ($action) {
  case 'export':
    // TODO: gather and return all user-related data
    echo json_encode(['user' => $u]);
    break;
  case 'erase':
    // TODO: schedule data deletion and revoke active sessions
    http_response_code(202);
    echo json_encode(['ok' => true, 'scheduled' => true]);
    break;
  default:
    http_response_code(400);
    echo json_encode(['error' => 'acción inválida']);
}

