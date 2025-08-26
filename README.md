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

