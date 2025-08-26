<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../provision_hooks.php';

$secret = $CONFIG['STRIPE_WEBHOOK_SECRET'];
$payload = file_get_contents('php://input');
$sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (PHP_SAPI !== 'cli' && (!$secret || !$sig)) { http_response_code(400); exit('no signature'); }

function upsert_sub(int $uid, string $product, string $subId, string $status, int $endTs): void {
    $sql = 'INSERT INTO subscriptions(user_id,product,stripe_sub_id,status,current_period_end,updated_at)
            VALUES(?,?,?,?,?,strftime("%s","now"))
            ON CONFLICT(user_id,product) DO UPDATE SET
              status=excluded.status,
              current_period_end=excluded.current_period_end,
              updated_at=strftime("%s","now")';
    $st = db()->prepare($sql);
    $st->execute([$uid,$product,$subId,$status,$endTs]);
}

function handle_stripe_event(array $data): void {
    $type = $data['type'] ?? '';
    $obj  = $data['data']['object'] ?? [];
    switch ($type) {
        case 'customer.subscription.created':
        case 'customer.subscription.updated':
        case 'customer.subscription.deleted':
            $subId  = (string)($obj['id'] ?? '');
            $status = (string)($obj['status'] ?? 'incomplete');
            $ends   = (int)($obj['current_period_end'] ?? 0);
            $custId = (string)($obj['customer'] ?? '');
            $product = $obj['metadata']['product'] ?? 'bundle';
            $st = db()->prepare('SELECT id FROM users WHERE billing_customer_id=?');
            $st->execute([$custId]);
            $uid = (int)($st->fetchColumn() ?: 0);
            if ($uid) {
                upsert_sub($uid, $product, $subId, $status, $ends);
                $active = in_array($status, ['active','trialing'], true);
                provision_service($uid, $product, $active);
            }
            break;
    }
}

if (PHP_SAPI !== 'cli') {
    $data = json_decode($payload, true);
    handle_stripe_event($data);
    http_response_code(200);
    echo '{"ok":true}';
}
