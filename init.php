<?php
declare(strict_types=1);
session_start();

$CONFIG = include __DIR__ . '/variables.php';

function db(): PDO {
  static $pdo;
  if (!$pdo) {
    $path = __DIR__ . '/db/nnm.sqlite';
    $pdo = new PDO('sqlite:' . $path, null, null, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }
  return $pdo;
}

// bootstrap schema si falta
$schema = __DIR__ . '/db/schema.sql';
if (file_exists($schema)) {
  db()->exec(file_get_contents($schema));
}

function user(): ?array {
  if (empty($_SESSION['uid'])) return null;
  $st = db()->prepare('SELECT * FROM users WHERE id = ?');
  $st->execute([$_SESSION['uid']]);
  return $st->fetch() ?: null;
}

function require_login(): array {
  $u = user();
  if (!$u) {
    header('Location: /login.php'); exit;
  }
  return $u;
}

function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
