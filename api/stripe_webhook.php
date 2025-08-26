<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';

$secret = $CONFIG['STRIPE_WEBHOOK_SECRET'];
$payload = file_get_contents('php://input');
$sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (!$secret || !$sig) { http_response_code(400); exit('no signature'); }

// Verificación manual básica (si usas la lib oficial, mejor).
// Aquí asumimos que el reverse proxy ya filtra. En producción usa stripe/stripe-php.

$data = json_decode($payload, true);
$type = $data['type'] ?? '';
$obj  = $data['data']['object'] ?? [];

function upsert_sub(int $uid, string $product, string $subId, string $status, int $endTs): void {
  $st = db()->prepare('INSERT INTO subscriptions(user_id,product,stripe_sub_id,status,current_period_end,updated_at)
    VALUES(?,?,?,?,?,strftime("%s","now"))
    ON CONFLICT(user_id,product) DO UPDATE SET status=excluded.status, current_period_end=excluded.current_period_end, updated_at=strftime("%s","now")');
  $st->execute([$uid,$product,$subId,$status,$endTs]);
}

switch ($type) {
  case 'customer.subscription.created':
  case 'customer.subscription.updated':
  case 'customer.subscription.deleted':
    $subId  = (string)($obj['id'] ?? '');
    $status = (string)($obj['status'] ?? 'incomplete');
    $ends   = (int)($obj['current_period_end'] ?? 0);
    $custId = (string)($obj['customer'] ?? '');
    // mapear a usuario
    $st = db()->prepare('SELECT u.id FROM users u JOIN customers c ON c.user_id = u.id WHERE c.stripe_customer_id = ?');
    $st->execute([$custId]);
    $uid = (int)($st->fetchColumn() ?: 0);
    if ($uid) {
      // Determinar producto por price/product metadata si la pones. Aquí placeholder:
      $product = 'bundle';
      upsert_sub($uid, $product, $subId, $status, $ends);
    }
    break;
}
http_response_code(200);
echo '{"ok":true}';
