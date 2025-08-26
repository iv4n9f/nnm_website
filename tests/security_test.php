<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../helpers.php';

// insert test user
$db = db();
$db->exec("INSERT INTO users (username, role) VALUES ('tester', 'admin')");
$u = $db->query("SELECT * FROM users WHERE username='tester'")->fetch();

assert(user_has_role($u, 'admin') === true);
assert(user_has_role($u, 'user') === false);
require_role($u, 'admin');

// audit logging
audit((int)$u['id'], 'test', ['ok'=>1]);
$log = $db->query('SELECT * FROM audit_logs')->fetch();
assert($log['action'] === 'test');
assert(strlen($log['signature']) === 64);

// rate limiting
assert(rate_limit('login', 2, 60) === true);
assert(rate_limit('login', 2, 60) === true);
assert(rate_limit('login', 2, 60) === false);

echo "OK\n";

