<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../helpers.php';

header('Content-Type: application/json');
$u = require_login();
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$product = $input['product'] ?? '';
$priceMap = [
  'vpn' => $CONFIG['PRICE_VPN'],
  'password' => $CONFIG['PRICE_PASSWORD'],
  'storage' => $CONFIG['PRICE_STORAGE'],
  'bundle' => $CONFIG['PRICE_BUNDLE'],
];
if (!isset($priceMap[$product])) {
  http_response_code(400); echo json_encode(['error'=>'producto inválido']); exit;
}
// En producción crear sesión de Checkout de Stripe aquí.
$sessionId = 'cs_test_' . bin2hex(random_bytes(8));
$url = 'https://checkout.stripe.test/session/' . $sessionId;

echo json_encode(['url'=>$url]);
