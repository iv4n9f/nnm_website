<?php
declare(strict_types=1);

function has_active(array $u, string $product): bool {
  $st = db()->prepare('SELECT 1 FROM subscriptions WHERE user_id=? AND product=? AND status IN ("active","trialing") AND current_period_end > strftime("%s","now")');
  $st->execute([$u['id'], $product]);
  return (bool)$st->fetchColumn();
}

function get_subs(array $u): array {
  $st = db()->prepare('SELECT product,status,current_period_end FROM subscriptions WHERE user_id=?');
  $st->execute([$u['id']]);
  $rows = $st->fetchAll();
  $out = [];
  foreach ($rows as $r) {
    $out[$r['product']] = [
      'status' => $r['status'],
      'until' => (int)$r['current_period_end'],
      'active' => in_array($r['status'], ['active','trialing'], true) && (int)$r['current_period_end'] > time(),
    ];
  }
  return $out;
}

function audit(int $uid, string $action, array $payload=[]): void {
  $st = db()->prepare('INSERT INTO ops_audit(user_id,action,payload) VALUES(?,?,?)');
  $st->execute([$uid, $action, json_encode($payload, JSON_UNESCAPED_UNICODE)]);
}

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function csrf_field(): string {
  return '<input type="hidden" name="_token" value="'.e(csrf_token()).'">';
}

function csrf_verify(): void {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = $_POST['_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
      http_response_code(419); // 419 Authentication Timeout
      exit('CSRF token inválido');
    }
  }
}