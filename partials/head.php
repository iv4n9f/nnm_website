<?php
if (!function_exists('e')) {
  function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}
$lang = $lang ?? 'es';
$title = $title ?? 'NNM Secure';
$description = $description ?? 'NNM Secure – Privacidad simple y servicios cifrados.';
$host = $_SERVER['HTTP_HOST'] ?? 'nnm.example';
$canonical = $canonical ?? ('https://' . $host . ($_SERVER['REQUEST_URI'] ?? '/'));
?>
<!doctype html>
<html lang="<?= e($lang) ?>" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= e($description) ?>">
  <link rel="canonical" href="<?= e($canonical) ?>">
  <meta property="og:title" content="<?= e($title) ?>">
  <meta property="og:description" content="<?= e($description) ?>">
  <title><?= e($title) ?></title>
  <meta name="color-scheme" content="light dark">
  <link rel="icon" href="/static/rsc/nnm-logo.ico">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/static/css/styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body id="top" class="fade-in">
<div id="loader" class="loader"><img src="/static/rsc/nnm-logo.png" class="logo-dark" alt="Cargando"></div>
<?php include __DIR__ . '/nav.php'; ?>
