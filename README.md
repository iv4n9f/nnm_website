# nnm_website

Herramientas básicas para gestión de usuarios y seguridad.

## Características
- Registro de auditoría firmado y exportable (JSON o CSV).
- Control de acceso basado en roles (owner, admin, soporte, user).
- Rate limiting y protección CSRF en formularios.
- Pagos recurrentes con Stripe para VPN, gestor de contraseñas y almacenamiento.

## Pruebas
```
php -d assert.exception=1 tests/security_test.php
php -d assert.exception=1 tests/mail_test.php
php -d assert.exception=1 tests/stripe_webhook_test.php
php -d assert.exception=1 tests/privacy_test.php
```

## Correo transaccional
- Plantillas HTML en `templates/{es,en}/<tipo>.html`.
- Configuración via variables de entorno: `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `MAIL_FROM`.
- Los envíos se registran en la tabla `mail_logs` para garantizar idempotencia y métricas.

## Suscripciones y Stripe
- Variables de entorno:
  - `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`
  - `STRIPE_PRICE_VPN`, `STRIPE_PRICE_PASSWORD`, `STRIPE_PRICE_STORAGE`, `STRIPE_PRICE_BUNDLE`
- `api/stripe_checkout.php` genera sesiones de pago y `api/stripe_portal.php` abre el portal del cliente.
- Los webhooks (`api/stripe_webhook.php`) actualizan la tabla `subscriptions`
  y disparan aprovisionamiento vía `provision_service`.

## Privacidad de datos
- Endpoint `api/user_privacy.php` permite `action=export` para obtener los datos del usuario y `action=erase` para solicitar el borrado de la cuenta.
- Las páginas públicas de [Privacidad](static/privacy.html), [Términos](static/terms.html) y [Cookies](static/cookies.html) explican el tratamiento de datos.
- El formulario de registro incluye un checkbox obligatorio y se registra el consentimiento en `audit_logs`.

## Despliegue
1. Copiar `.env.example` a `.env` y rellenar las variables necesarias.
2. Ejecutar `./init.sh` para inicializar la base de datos y aplicar migraciones.
=======

## Privacidad de datos
- Endpoint `api/user_privacy.php` permite `action=export` para obtener los datos del usuario y `action=erase` para solicitar el borrado de la cuenta.