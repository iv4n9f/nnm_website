#!/usr/bin/env bash
set -euo pipefail

# ===== Config =====
DOMAIN="northnexusmex.cloud"
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
WEB_ROOT="/srv/www/$DOMAIN"
SERVER_NAME="nnm"
WWW_USER="www-data"
WWW_GROUP="www-data"
PV="$(php -r 'echo PHP_MAJOR_VERSION.\".\".PHP_MINOR_VERSION;' 2>/dev/null || echo 8.3)"
LE_EMAIL="${LE_EMAIL:-${LE_MAIL:-admin@$DOMAIN}}"

log(){ printf "[init] %s\n" "$*"; }
need_root(){ [ "$(id -u)" -eq 0 ] || { echo "Ejecuta con sudo."; exit 1; }; }

need_root
command -v apt-get >/dev/null || { echo "Debian/Ubuntu requerido."; exit 1; }

log "Paquetes base"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get install -y nginx "php$PV-fpm" "php$PV-cli" "php$PV-sqlite3" \
  "php$PV-curl" "php$PV-mbstring" "php$PV-xml" "php$PV-zip" certbot acl curl git unzip ca-certificates

php -m | grep -qi pdo_sqlite || { echo "Falta php$PV-sqlite3"; exit 1; }

# ===== Webroot y permisos =====
log "Preparando webroot $WEB_ROOT"
mkdir -p "$WEB_ROOT"
rsync -a --delete "$PROJECT_DIR"/ "$WEB_ROOT"/
chown -R "$WWW_USER:$WWW_GROUP" "$WEB_ROOT"
find "$WEB_ROOT" -type d -exec chmod 755 {} \;
find "$WEB_ROOT" -type f -exec chmod 644 {} \;
chmod 755 /srv /srv/www 2>/dev/null || true

# ===== Composer =====
if ! command -v composer >/dev/null 2>&1; then
  log "Instalando Composer"
  apt-get install -y composer || true
fi
if ! command -v composer >/dev/null 2>&1; then
  log "Instalador oficial de Composer"
  php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
  php composer-setup.php --install-dir=/usr/local/bin --filename=composer
  rm -f composer-setup.php
fi
command -v composer >/dev/null 2>&1 || { echo "Composer no disponible"; exit 1; }

# ===== Dependencias PHP del proyecto (Stripe SDK) =====
log "Instalando dependencias PHP en $WEB_ROOT"
cd "$WEB_ROOT"

# Inicializa composer.json si no existe
if [ ! -f composer.json ]; then
  sudo -u "$WWW_USER" composer init -n --name nnm/site
fi

# Añade stripe/stripe-php
if ! grep -q '"stripe/stripe-php"' composer.json 2>/dev/null; then
  sudo -u "$WWW_USER" composer require stripe/stripe-php:^14 --prefer-dist
fi

# Optimiza autoload en entorno prod
sudo -u "$WWW_USER" composer install --no-dev --optimize-autoloader

# Asegura permisos de vendor
chown -R "$WWW_USER:$WWW_GROUP" "$WEB_ROOT/vendor" || true
find "$WEB_ROOT/vendor" -type d -exec chmod 755 {} \; 2>/dev/null || true
find "$WEB_ROOT/vendor" -type f -exec chmod 644 {} \; 2>/dev/null || true

# ===== Detectar si www existe en DNS =====
HAS_WWW=0
if getent ahostsv4 "www.$DOMAIN" >/dev/null 2>&1; then HAS_WWW=1; fi

# ===== Nginx: fase HTTP sin SSL =====
HTTP_CONF="/etc/nginx/sites-available/${SERVER_NAME}.conf"
HTTP_LINK="/etc/nginx/sites-enabled/${SERVER_NAME}.conf"

log "Escribiendo vhost HTTP inicial"
cat > "$HTTP_CONF" <<NGX
server {
  listen 80;
  listen [::]:80;
  server_name $DOMAIN${HAS_WWW:+ www.$DOMAIN};

  root $WEB_ROOT;
  index index.php index.html;

  # ACME challenge público
  location ^~ /.well-known/acme-challenge/ {
    root $WEB_ROOT;
    default_type "text/plain";
    allow all;
  }

  # PHP (temporal en HTTP)
  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php$PV-fpm.sock;
    fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
  }

  location / {
    try_files \$uri \$uri/ /index.php?\$args;
  }

  location ~* \.(?:env|ini|log|sql|sqlite|bak)$ { deny all; }
}
NGX

ln -sf "$HTTP_CONF" "$HTTP_LINK"
nginx -t
systemctl enable --now nginx
systemctl reload nginx
systemctl enable --now "php$PV-fpm"

# UFW opcional
if command -v ufw >/dev/null 2>&1; then ufw allow 80,443/tcp || true; fi

# ===== Validación previa del challenge =====
log "Validando acceso HTTP al challenge"
mkdir -p "$WEB_ROOT/.well-known/acme-challenge"
echo ok > "$WEB_ROOT/.well-known/acme-challenge/ping"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://$DOMAIN/.well-known/acme-challenge/ping" || true)
[ "$HTTP_CODE" = "200" ] || { echo "HTTP-01 inaccesible (código $HTTP_CODE). Revisa DNS/puerto 80/proxy."; exit 1; }
if [ "$HAS_WWW" -eq 1 ]; then
  HTTP_CODE_W=$(curl -s -o /dev/null -w "%{http_code}" "http://www.$DOMAIN/.well-known/acme-challenge/ping" || true)
  [ "$HTTP_CODE_W" = "200" ] || { echo "HTTP-01 www falló (código $HTTP_CODE_W). Crea A www o se omitirá."; HAS_WWW=0; }
fi

# ===== Certificado de producción =====
log "Emitiendo certificado Let’s Encrypt"
CERT_DOMS=(-d "$DOMAIN"); [ "$HAS_WWW" -eq 1 ] && CERT_DOMS+=(-d "www.$DOMAIN")
certbot certonly --webroot -w "$WEB_ROOT" "${CERT_DOMS[@]}" -m "$LE_EMAIL" --agree-tos --no-eff-email

CERT_DIR="/etc/letsencrypt/live/$DOMAIN"
FULLCHAIN="$CERT_DIR/fullchain.pem"
PRIVKEY="$CERT_DIR/privkey.pem"
[ -s "$FULLCHAIN" ] && [ -s "$PRIVKEY" ] || { echo "Cert no encontrado."; exit 1; }

# ===== Nginx: vhost HTTPS =====
log "Escribiendo vhost HTTPS definitivo"
cat > "$HTTP_CONF" <<NGX
# Redirección 80 -> 443
server {
  listen 80;
  listen [::]:80;
  server_name $DOMAIN${HAS_WWW:+ www.$DOMAIN};

  location ^~ /.well-known/acme-challenge/ {
    root $WEB_ROOT;
    default_type "text/plain";
    allow all;
  }
  return 301 https://\$host\$request_uri;
}

# HTTPS
server {
  listen 443 ssl http2;
  listen [::]:443 ssl http2;
  server_name $DOMAIN${HAS_WWW:+ www.$DOMAIN};

  root $WEB_ROOT;
  index index.php index.html;

  ssl_certificate     $FULLCHAIN;
  ssl_certificate_key $PRIVKEY;

  add_header X-Content-Type-Options nosniff always;
  add_header X-Frame-Options SAMEORIGIN always;
  add_header Referrer-Policy strict-origin-when-cross-origin always;
  add_header X-XSS-Protection "1; mode=block" always;
  add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;

  location ~* \.(?:jpg|jpeg|png|gif|ico|webp|svg|css|js|woff2?|ttf|eot)$ {
    access_log off;
    expires 30d;
    try_files \$uri =404;
  }

  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php$PV-fpm.sock;
    fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
  }

  location / {
    try_files \$uri \$uri/ /index.php?\$args;
  }

  location ~* \.(?:env|ini|log|sql|sqlite|bak)$ { deny all; }
}
NGX

nginx -t
systemctl reload nginx

# ===== Migraciones DB =====
log "Migrando DB"
sudo -u "$WWW_USER" php "$WEB_ROOT/migrate.php" || php "$WEB_ROOT/migrate.php"

log "OK https://$DOMAIN listo"
