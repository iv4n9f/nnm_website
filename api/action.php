<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../helpers.php';

header('Content-Type: application/json');

$u = require_login();
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$product = $input['product'] ?? '';
$action  = $input['action'] ?? '';
$extra   = $input['extra'] ?? [];

$allowedP = ['vpn','password','storage'];
$allowedA = ['start','stop','restart','status','provision','admin-url'];

if (!in_array($product, $allowedP, true) || !in_array($action, $allowedA, true)) {
  http_response_code(400); echo json_encode(['error'=>'parámetros inválidos']); exit;
}
if (!has_active($u, $product) && !in_array($action, ['status','admin-url'], true)) {
  http_response_code(402); echo json_encode(['error'=>'suscripción requerida']); exit;
}

$map = [
  'vpn' => $CONFIG['WG_MODULE'],
  'password' => $CONFIG['VAULTWARDEN_MODULE'],
  'storage' => $CONFIG['SEAFILE_MODULE'],
];
$bin = escapeshellcmd($map[$product]);

switch ($action) {
  case 'start':
  case 'stop':
  case 'restart':
  case 'status':
    $cmd = "$bin $action";
    break;
  case 'provision':
    if ($product==='vpn') {
      $client = preg_replace('/[^a-zA-Z0-9._-]/','', (string)($extra['client'] ?? 'cli_'.$u['id']));
      $cmd = "$bin add-client ".escapeshellarg($client);
    } else {
      http_response_code(400); echo json_encode(['error'=>'no soportado']); exit;
    }
    break;
  case 'admin-url':
    // los módulos deben responder admin-url en stdout
    $cmd = "$bin admin-url";
    break;
}

exec($cmd . ' 2>&1', $out, $rc);
audit((int)$u['id'], $product.':'.$action, ['rc'=>$rc,'out'=>$out,'extra'=>$extra]);

echo json_encode([
  'ok' => $rc === 0,
  'code' => $rc,
  'output' => implode("\n", $out),
]);
