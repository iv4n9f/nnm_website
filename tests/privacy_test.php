<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';

// create user
$db = db();
$db->exec('DELETE FROM users');
$algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
$hash = password_hash('secret123', $algo);
$db->prepare('INSERT INTO users(email,password_hash) VALUES(?,?)')->execute(['tester@example.com',$hash]);
$uid = (int)$db->lastInsertId();
$_SESSION['uid'] = $uid;

// export data
$_GET['action'] = 'export';
ob_start();
include __DIR__.'/../api/user_privacy.php';
$res = json_decode(ob_get_clean(), true);
assert($res['user']['email'] === 'tester@example.com');

// erase account
$_SESSION['uid'] = $uid;
$_GET['action'] = 'erase';
ob_start();
include __DIR__.'/../api/user_privacy.php';
ob_end_clean();
$st = $db->prepare('SELECT COUNT(*) FROM users WHERE id=?');
$st->execute([$uid]);
assert((int)$st->fetchColumn() === 0);

echo "OK\n";
