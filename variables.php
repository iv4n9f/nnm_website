<?php
return [
  // Negocio
  'MAIL' => 'info@northnexusmex.cloud',
  'BASE_CURRENCY' => 'EUR',
  'UNIT' => '€',

  // Stripe
  'STRIPE_SECRET' => getenv('STRIPE_SECRET') ?: '',
  'STRIPE_WEBHOOK_SECRET' => getenv('STRIPE_WEBHOOK_SECRET') ?: '',
  'PRICE_VPN' => getenv('STRIPE_PRICE_VPN') ?: '',
  'PRICE_PASSWORD' => getenv('STRIPE_PRICE_PASSWORD') ?: '',
  'PRICE_STORAGE' => getenv('STRIPE_PRICE_STORAGE') ?: '',
  'PRICE_BUNDLE' => getenv('STRIPE_PRICE_BUNDLE') ?: '',

  // Rutas módulos
  'WG_MODULE' => '/modules/wg-manager.sh',
  'VAULTWARDEN_MODULE' => '/modules/vaultwarden.sh',
  'SEAFILE_MODULE' => '/modules/storage.sh',

  // Paths
  'DATA_DIR' => '/var/nnm',
  // Audit
  'AUDIT_SECRET' => getenv('AUDIT_SECRET') ?: 'dev-secret',
];

