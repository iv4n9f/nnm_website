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

function user_has_role(array $u, string $role): bool {
  return ($u['role'] ?? 'user') === $role;
}

function require_role(array $u, string ...$roles): void {
  if (!in_array($u['role'] ?? 'user', $roles, true)) {
    http_response_code(403);
    exit('no autorizado');
  }
}

function audit(int $uid, string $action, array $payload = []): void {
  global $CONFIG;
  $meta = json_encode($payload, JSON_UNESCAPED_UNICODE);
  $ts = time();
  $sig = hash_hmac('sha256', $uid . '|' . $action . '|' . $meta . '|' . $ts, $CONFIG['AUDIT_SECRET']);
  $st = db()->prepare('INSERT INTO audit_logs(user_id,action,meta,signature,created_at) VALUES(?,?,?,?,datetime("now"))');
  $st->execute([$uid, $action, $meta, $sig]);
}

function rate_limit(string $key, int $limit, int $period): bool {
  $now = time();
  $st = db()->prepare('SELECT tokens, reset_at FROM rate_limits WHERE key=?');
  $st->execute([$key]);
  $row = $st->fetch();
  if (!$row || (int)$row['reset_at'] <= $now) {
    $tokens = $limit - 1;
    $reset = $now + $period;
    $st = db()->prepare('REPLACE INTO rate_limits(key,tokens,reset_at) VALUES(?,?,?)');
    $st->execute([$key, $tokens, $reset]);
    return true;
  }
  if ((int)$row['tokens'] <= 0) {
    return false;
  }
  $st = db()->prepare('UPDATE rate_limits SET tokens=tokens-1 WHERE key=?');
  $st->execute([$key]);
  return true;
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
