<?php
return [
  // Negocio
  'MAIL' => 'info@northnexusmex.cloud',
  'BASE_CURRENCY' => 'EUR',
  'UNIT' => '€',

  // Stripe
  'STRIPE_SECRET' => getenv('STRIPE_SECRET') ?: '',
  'STRIPE_WEBHOOK_SECRET' => getenv('STRIPE_WEBHOOK_SECRET') ?: '',

  // Rutas módulos
  'WG_MODULE' => '/modules/wg-manager.sh',
  'VAULTWARDEN_MODULE' => '/modules/vaultwarden.sh',
  'SEAFILE_MODULE' => '/modules/storage.sh',

  // Paths
  'DATA_DIR' => '/var/nnm',
];
