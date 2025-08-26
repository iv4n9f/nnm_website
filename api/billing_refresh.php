<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../helpers.php';

// Este endpoint podría consultar Stripe si quieres “pull”.
// Para simplicidad, devuelve lo que haya en SQLite.
header('Content-Type: application/json');
$u = require_login();
echo json_encode(get_subs($u));
