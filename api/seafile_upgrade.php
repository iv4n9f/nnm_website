<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
$u = require_login();
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$size = (int)($input['size'] ?? 0);
// TODO: handle upgrade request
header('Content-Type: application/json');
echo json_encode(['ok'=>true,'size'=>$size]);
