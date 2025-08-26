<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../api/stripe_webhook.php';

$db = db();
$db->exec("DELETE FROM users");
$db->exec("DELETE FROM subscriptions");
$db->exec("DELETE FROM mail_logs");

$db->exec("INSERT INTO users (email,password_hash,billing_customer_id) VALUES ('user@example.com','x','cus_test')");
$uid = (int)$db->lastInsertId();

$event = [
  'type' => 'customer.subscription.updated',
  'data' => ['object' => [
    'id' => 'sub_123',
    'status' => 'active',
    'current_period_end' => time()+3600,
    'customer' => 'cus_test',
    'metadata' => ['product'=>'vpn']
  ]]
];
handle_stripe_event($event);

$st = $db->query('SELECT status FROM subscriptions WHERE user_id='.$uid.' AND product="vpn"');
$row = $st->fetch();
assert($row['status']==='active');

$log = $db->query('SELECT type FROM mail_logs WHERE recipient="user@example.com"')->fetch();
assert($log['type']==='wireguard');

echo "OK\n";
