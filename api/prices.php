<?php
// api/prices.php - devuelve los precios actuales de Stripe
declare(strict_types=1);

require_once __DIR__ . '/../init.php';

header('Content-Type: application/json; charset=utf-8');

$cfg = include __DIR__ . '/../variables.php';

$ids = [
    'PRICE_VPN'     => $cfg['PRICE_VPN'] ?? '',
    'PRICE_PASSWORD'=> $cfg['PRICE_PASSWORD'] ?? '',
    'PRICE_STORAGE' => $cfg['PRICE_STORAGE'] ?? '',
    'PRICE_BUNDLE'  => $cfg['PRICE_BUNDLE'] ?? '',
];

$out = [];

foreach ($ids as $key => $id) {
    if (!$id) {
        continue;
    }
    $ch = curl_init("https://api.stripe.com/v1/prices/" . urlencode($id));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => ($cfg['STRIPE_SECRET'] ?? '') . ':',
        CURLOPT_TIMEOUT        => 10,
    ]);
    $resp = curl_exec($ch);
    $data = $resp ? json_decode($resp, true) : null;
    curl_close($ch);

    if (isset($data['unit_amount'], $data['currency'])) {
        $out[$key] = [
            'amount'   => ((int)$data['unit_amount']) / 100,
            'currency' => strtoupper((string)$data['currency']),
        ];
    }
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
