<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

$db = nnm_db();
$db->exec('PRAGMA foreign_keys = ON;');

$db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL UNIQUE,
  email TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  locale TEXT NOT NULL DEFAULT 'es',
  currency TEXT NOT NULL DEFAULT 'EUR',
  billing_provider TEXT,                 -- 'stripe'|'paypal'|'bizum'|NULL
  billing_customer_id TEXT,              -- id cliente en pasarela
  created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ','now')),
  verified_at TEXT,                      -- verificación email (futuro)
  role TEXT NOT NULL DEFAULT 'user'      -- 'user'|'admin'
);

CREATE TABLE IF NOT EXISTS subscriptions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  service TEXT NOT NULL,                 -- 'vpn'|'password_mngr'|'storage'
  plan TEXT NOT NULL DEFAULT 'basic',    -- 'basic'|'bundle'|etc
  active INTEGER NOT NULL DEFAULT 0,     -- 0/1
  price_cents INTEGER NOT NULL DEFAULT 0,
  currency TEXT NOT NULL DEFAULT 'EUR',
  renew_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%d','now','+1 month')),
  node TEXT,                             -- ej. 'uk01'|'de01' para VPN o cluster
  external_id TEXT,                      -- id del recurso aprovisionado
  created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ','now')),
  canceled_at TEXT
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_subs_unique
  ON subscriptions(user_id, service)
  WHERE canceled_at IS NULL;

CREATE TABLE IF NOT EXISTS audit_logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
  action TEXT NOT NULL,                  -- 'login'|'enable_vpn'|'disable_vpn'...
  meta TEXT,                             -- JSON ligero
  created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ','now'))
);
SQL);

echo "OK\n";
