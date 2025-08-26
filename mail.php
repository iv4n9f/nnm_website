<?php
declare(strict_types=1);

/**
 * Simple transactional mailer with template rendering and idempotent logging.
 * In production, replace the stub sending logic with an SMTP library
 * such as PHPMailer to honour SMTP_* environment variables.
 */
function send_mail(string $type, string $to, array $vars = [], string $lang = 'es'): bool {
    $messageId = $vars['message_id'] ?? sha1($type.'|'.$to.'|'.json_encode($vars));
    $db = db();
    $st = $db->prepare('SELECT status, attempts FROM mail_logs WHERE message_id=?');
    $st->execute([$messageId]);
    $row = $st->fetch();
    if ($row && $row['status'] === 'sent') {
        return true; // idempotent: already sent
    }
    $template = __DIR__."/templates/{$lang}/{$type}.html";
    if (!is_file($template)) {
        throw new RuntimeException('template not found');
    }
    $body = file_get_contents($template);
    foreach ($vars as $k => $v) {
        if (is_scalar($v)) {
            $body = str_replace('{{'.$k.'}}', htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'), $body);
        }
    }
    $subject = $vars['subject'] ?? 'NNM';
    $logo = getenv('MAIL_LOGO_URL') ?: 'https://nnm.example/static/rsc/nnm-logo.png';
    $body = "<!doctype html><html lang=\"{$lang}\"><head><meta charset=\"utf-8\"><meta name=\"color-scheme\" content=\"light dark\"><title>".htmlspecialchars($subject, ENT_QUOTES, 'UTF-8')."</title><style>body{font-family:Arial,sans-serif;background:#fff;color:#000;}@media (prefers-color-scheme:dark){body{background:#111;color:#eee;}}</style></head><body><div style=\"text-align:center;margin-bottom:16px\"><img src=\"{$logo}\" alt=\"NNM Secure\" style=\"height:48px\"></div>{$body}</body></html>";
    $mailFrom = getenv('MAIL_FROM') ?: 'NNM Secure <info@northnexusmex.cloud>';
    $smtpHost = getenv('SMTP_HOST') ?: '';
    $smtpPort = getenv('SMTP_PORT') ?: '';
    $smtpUser = getenv('SMTP_USER') ?: '';
    $smtpPass = getenv('SMTP_PASS') ?: '';

    // Stub send: replace with real SMTP implementation.
    $sent = true;

    $attempts = $row ? ((int)$row['attempts'] + 1) : 1;
    $status = $sent ? 'sent' : 'failed';
    $lastError = $sent ? null : 'send failed';
    $sentAt = $sent ? gmdate('Y-m-d\TH:i:s\Z') : null;
    $st = $db->prepare('REPLACE INTO mail_logs(message_id,type,recipient,status,attempts,last_error,sent_at) VALUES(?,?,?,?,?,?,?)');
    $st->execute([$messageId,$type,$to,$status,$attempts,$lastError,$sentAt]);
    return $sent;
}
