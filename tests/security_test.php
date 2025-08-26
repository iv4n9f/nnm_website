<?php
declare(strict_types=1);

require_once __DIR__.'/../init.php';
require_once __DIR__.'/../helpers.php';

$db = db();
$db->prepare('DELETE FROM rate_limits WHERE key=?')->execute(['login']);

function create_test_user(PDO $db): array {
  $db->prepare('DELETE FROM users WHERE username=?')->execute(['tester']);
  $st = $db->prepare('INSERT INTO users (username, role) VALUES (?, ?)');
  $st->execute(['tester', 'admin']);
  return $db->query("SELECT * FROM users WHERE username='tester'")->fetch();
}

function cleanup(PDO $db, int $uid): void {
  // audit logs are append-only, so we only remove rate limit and user entries
  $db->prepare('DELETE FROM rate_limits WHERE key=?')->execute(['login']);
  $db->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
}

$user = create_test_user($db);

assert(user_has_role($user, 'admin') === true, 'Admin role must be present');
assert(user_has_role($user, 'user') === false, 'User role should not be present');
require_role($user, 'admin');

$auditId = audit((int)$user['id'], 'test', ['ok' => 1]);
$st = $db->prepare('SELECT * FROM audit_logs WHERE id=?');
$st->execute([$auditId]);
$log = $st->fetch();
assert($log['action'] === 'test', 'Audit action mismatch');
assert(strlen($log['signature']) === 64, 'Audit signature length mismatch');

assert(rate_limit('login', 2, 60) === true, 'First attempt should pass');
assert(rate_limit('login', 2, 60) === true, 'Second attempt should pass');
assert(rate_limit('login', 2, 60) === false, 'Third attempt should be limited');

cleanup($db, (int)$user['id']);
echo "OK\n";

