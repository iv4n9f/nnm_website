<?php
declare(strict_types=1);
require_once __DIR__.'/init.php';
require_once __DIR__.'/helpers.php';

$format = $_GET['format'] ?? 'json';
$st = db()->query('SELECT user_id, action, meta, signature, created_at FROM audit_logs ORDER BY id ASC');
$rows = $st->fetchAll();

if ($format === 'csv') {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="audit.csv"');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['user_id','action','meta','signature','created_at']);
  foreach ($rows as $r) {
    fputcsv($out, [$r['user_id'], $r['action'], $r['meta'], $r['signature'], $r['created_at']]);
  }
  fclose($out);
} else {
  header('Content-Type: application/json');
  echo json_encode($rows, JSON_UNESCAPED_UNICODE);
}

