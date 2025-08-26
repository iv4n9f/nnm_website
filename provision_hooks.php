<?php
declare(strict_types=1);
require_once __DIR__.'/init.php';
require_once __DIR__.'/mail.php';
require_once __DIR__.'/helpers.php';

/**
 * Provisiona o desprovisiona servicios según el estado de la suscripción.
 * En producción reemplaza estas acciones con llamadas a tus módulos reales.
 */
function provision_service(int $uid, string $product, bool $active): void {
    $st = db()->prepare('SELECT email, locale FROM users WHERE id=?');
    $st->execute([$uid]);
    $u = $st->fetch();
    if (!$u) return;
    $lang = $u['locale'] ?? 'es';
    if ($active) {
        $tplMap = [
            'vpn' => 'wireguard',
            'password' => 'vaultwarden',
            'storage' => 'seafile',
            'bundle' => 'welcome'
        ];
        if (isset($tplMap[$product])) {
            send_mail($tplMap[$product], $u['email'], ['subject'=>'Servicio activado'], $lang);
        }
        audit($uid, 'provision:'.$product, ['active'=>true]);
    } else {
        audit($uid, 'provision:'.$product, ['active'=>false]);
    }
}
