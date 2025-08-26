<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../helpers.php';

header('Content-Type: application/json');

$u = require_login();
$product = $_GET['product'] ?? '';
$allowed = ['vpn','password','storage'];

if (!in_array($product, $allowed, true)) {
  http_response_code(400); echo json_encode(['error'=>'producto inválido']); exit;
}
if (!has_active($u, $product) && $product !== 'vpn') { // deja status vpn visible si quieres
  http_response_code(402); echo json_encode(['error'=>'suscripción requerida']); exit;
}

$map = [
  'vpn' => $CONFIG['WG_MODULE'],
  'password' => $CONFIG['VAULTWARDEN_MODULE'],
  'storage' => $CONFIG['SEAFILE_MODULE'],
];

$cmd = escapeshellcmd($map[$product]) . ' status';
exec($cmd . ' 2>&1', $out, $rc);
echo json_encode([
  'ok' => $rc === 0,
  'code' => $rc,
  'output' => implode("\n", $out),
]);
