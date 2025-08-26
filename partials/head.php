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
  <link rel="stylesheet" href="/static/css/material.css">
  <link rel="stylesheet" href="/static/css/styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
  <script type="importmap">{"imports":{"@material/web/":"https://unpkg.com/@material/web@1.4.0/"}}</script>
  <script type="module">
    import "@material/web/button/filled-button.js";
    import "@material/web/button/outlined-button.js";
    import "@material/web/menu/menu.js";
    import "@material/web/menu/menu-item.js";
    import "@material/web/iconbutton/icon-button.js";
    import "@material/web/card/elevated-card.js";
    import "@material/web/divider/divider.js";
  </script>
</head>
<body id="top">
<?php include __DIR__ . '/nav.php'; ?>
