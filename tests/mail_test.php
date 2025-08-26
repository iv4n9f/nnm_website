<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../mail.php';

$messageId = bin2hex(random_bytes(4));
assert(send_mail('welcome', 'test@example.com', ['name'=>'Tester','message_id'=>$messageId]) === true);
$st = db()->prepare('SELECT * FROM mail_logs WHERE message_id=?');
$st->execute([$messageId]);
$log = $st->fetch();
assert($log['status'] === 'sent');
assert((int)$log['attempts'] === 1);
// second call should not create new attempt
assert(send_mail('welcome', 'test@example.com', ['name'=>'Tester','message_id'=>$messageId]) === true);
$st->execute([$messageId]);
$log2 = $st->fetch();
assert((int)$log2['attempts'] === 1);

echo "OK\n";
