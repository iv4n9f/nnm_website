<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
$u = require_login();
// TODO: send seafile invite email
header('Content-Type: application/json');
echo json_encode(['ok'=>true]);
