<?php
declare(strict_types=1);
session_start();

// Carga variables de entorno desde el fichero .env si no están presentes
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
  foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
      continue;
    }
    [$name, $value] = array_map('trim', explode('=', $line, 2));
    $value = trim($value, "'\"");
    if (getenv($name) === false) {
      putenv("$name=$value");
      $_ENV[$name] = $value;
    }
  }
}

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
