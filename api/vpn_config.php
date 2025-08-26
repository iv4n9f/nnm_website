<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
$u = require_login();
$server = $_GET['server'] ?? 'uk';
$map = ['uk'=>'nnmsrvuk01','de'=>'nnmsrvde01'];
if(!isset($map[$server])){
  http_response_code(400);
  echo 'Servidor inválido';
  exit;
}
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="'.$map[$server].'.conf"');
echo "# WireGuard config\nEndpoint = {$map[$server]}\n";
