<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../helpers.php';

header('Content-Type: application/json');

$u = require_login();
$action = $_GET['action'] ?? '';

switch ($action) {
  case 'export':
    $st = db()->prepare('SELECT id,email,username,locale,role FROM users WHERE id=?');
    $st->execute([$u['id']]);
    $userData = $st->fetch();
    $logsSt = db()->prepare('SELECT action,meta,created_at FROM audit_logs WHERE user_id=?');
    $logsSt->execute([$u['id']]);
    $logs = $logsSt->fetchAll();
    echo json_encode(['user' => $userData, 'audit_logs' => $logs]);
    break;
  case 'erase':
    $st = db()->prepare('DELETE FROM users WHERE id=?');
    $st->execute([$u['id']]);
    audit($u['id'], 'erase_account');
    session_destroy();
    echo json_encode(['ok' => true]);
=======
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

