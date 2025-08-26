<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
$cfg = include __DIR__ . '/../variables.php';

$stripe = new \Stripe\StripeClient($cfg['STRIPE_SECRET']);

// Cambia por tu product id
$productId = 'prod_SwFknkGqycuEAL';

$res = $stripe->prices->all([
    'product' => $productId,
    'active'  => true,
    'limit'   => 20,
    'expand'  => ['data.currency_options'],
]);

// elige el price “mejor”: mensual, más reciente
$chosen = null;
foreach ($res->data as $p) {
    if ($p->type === 'recurring' && $p->recurring?->interval === 'month') {
        if ($chosen === null || $p->created > $chosen->created) {
            $chosen = $p;
        }
    }
}

// fallback si no hay mensual
$chosen = $chosen ?: ($res->data[0] ?? null);

$out = [];
if ($chosen) {
    // si quieres forzar EUR cuando existan currency_options:
    $amount = $chosen->unit_amount;
    $currency = strtoupper($chosen->currency);
    if (isset($chosen->currency_options['EUR']?->unit_amount)) {
        $amount = $chosen->currency_options['EUR']->unit_amount;
        $currency = 'EUR';
    }

    $out = [
        'price_id'  => $chosen->id,
        'amount'    => $amount / 100,
        'currency'  => $currency,
        'interval'  => $chosen->recurring->interval ?? null,
        'interval_count' => $chosen->recurring->interval_count ?? null,
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_UNESCAPED_UNICODE);
