<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../helpers.php';

header('Content-Type: application/json');
$u = require_login();
// En producción usar Stripe Billing Portal.
$url = 'https://billing.stripe.test/portal/' . ($u['billing_customer_id'] ?: 'new');
echo json_encode(['url'=>$url]);
