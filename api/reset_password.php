<?php
declare(strict_types=1);
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../helpers.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

if (isset($input['email'])) {
    $email = trim(strtolower($input['email']));
    $st = db()->prepare('SELECT id, username, locale FROM users WHERE email=?');
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + 3600;
        $st = db()->prepare('REPLACE INTO password_resets(user_id, token, expires_at) VALUES(?,?,?)');
        $st->execute([$u['id'], $token, $expires]);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $link = $scheme . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/reset.php?token=' . $token;
        send_mail('reset', $email, ['name'=>$u['username'] ?: $email, 'link'=>$link], $u['locale'] ?? 'es');
    }
    echo json_encode(['ok'=>true]);
    exit;
}

if (isset($input['token'], $input['password'])) {
    $token = $input['token'];
    $pass  = $input['password'];
    $st = db()->prepare('SELECT user_id FROM password_resets WHERE token=? AND expires_at>?');
    $st->execute([$token, time()]);
    $row = $st->fetch();
    if (!$row) {
        http_response_code(400);
        echo json_encode(['error'=>'token inválido']);
        exit;
    }
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $st = db()->prepare('UPDATE users SET password_hash=? WHERE id=?');
    $st->execute([$hash, $row['user_id']]);
    $st = db()->prepare('DELETE FROM password_resets WHERE user_id=?');
    $st->execute([$row['user_id']]);
    echo json_encode(['ok'=>true]);
    exit;
}

http_response_code(400);
echo json_encode(['error'=>'solicitud inválida']);
