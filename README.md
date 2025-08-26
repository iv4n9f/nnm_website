# nnm_website

Herramientas básicas para gestión de usuarios y seguridad.

## Características
- Registro de auditoría firmado y exportable (JSON o CSV).
- Control de acceso basado en roles (owner, admin, soporte, user).
- Rate limiting y protección CSRF en formularios.

## Pruebas
```
php -d assert.exception=1 tests/security_test.php
```


## Correo transaccional
- Plantillas HTML en `templates/{es,en}/<tipo>.html`.
- Configuración via variables de entorno: `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `MAIL_FROM`.
- Los envíos se registran en la tabla `mail_logs` para garantizar idempotencia y métricas.

