#!/usr/bin/env bash
set -euo pipefail

# =================== Ajustes por defecto ===================
DOMAIN="${DOMAIN:-password.northnexusmex.cloud}"
BASE_DIR="${BASE_DIR:-/srv/vaultwarden}"
ENV_FILE="$BASE_DIR/.env"
YML_FILE="$BASE_DIR/docker-compose.yml"
SITE_FILE="/etc/nginx/sites-available/vaultwarden.conf"
LOCK="/run/vaultwarden.lock"

# OVH Zimbra MX Plan SMTP defaults
REGION="${REGION:-eu}"   # eu | na
DEFAULT_SMTP_HOST_EU="smtp.mail.ovh.net"   # alternativa: ssl0.ovh.net
DEFAULT_SMTP_HOST_NA="smtp.mail.ovh.ca"
DEFAULT_SMTP_PORT="465"
DEFAULT_SMTP_SECURITY="force_tls"

SMTP_USER_DEFAULT="${SMTP_USER_DEFAULT:-info@northnexusmex.cloud}"
SMTP_FROM_NAME_DEFAULT="${SMTP_FROM_NAME_DEFAULT:-NNM Secure}"

# =================== Utilidades ===================
have(){ command -v "$1" >/dev/null 2>&1; }
fail(){ printf '[ERROR] %s\n' "$1" >&2; exit 1; }
need_root(){ [[ ${EUID:-0} -eq 0 ]] || fail "ejecútalo como root"; }

json_escape(){ local s=${1:-}; s=${s//\\/\\\\}; s=${s//\"/\\\"}; s=${s//$'\n'/\\n}; s=${s//$'\r'/\\r}; s=${s//$'\t'/\\t}; printf '%s' "$s"; }
json(){ printf '{"status":"%s","message":"%s"}\n' "$(json_escape "$1")" "$(json_escape "$2")"; }

kv_set_env(){
  local file="$1" key="$2" val="$3"
  touch "$file"
  if grep -qE "^[# ]*${key}=" "$file"; then
    # Reemplazo sin interpretar barras
    sed -i "s|^[# ]*${key}=.*|${key}=${val}|" "$file"
  else
    echo "${key}=${val}" >> "$file"
  fi
}

ensure_tools(){
  if have apt-get; then
    apt-get update -y
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
      docker.io docker-compose-plugin nginx certbot python3-certbot-nginx curl jq openssl
    systemctl enable --now docker >/dev/null 2>&1 || true
  else
    have docker || fail "instala docker"
    have nginx  || fail "instala nginx"
    have certbot || fail "instala certbot"
    have jq     || fail "instala jq"
    have curl   || fail "instala curl"
    have openssl || fail "instala openssl"
  fi
}

smtp_host_by_region(){
  case "$REGION" in
    eu) echo "$DEFAULT_SMTP_HOST_EU";;
    na|apac|us|america|asia) echo "$DEFAULT_SMTP_HOST_NA";;
    *) echo "$DEFAULT_SMTP_HOST_EU";;
  esac
}

write_env(){
  install -d -m 700 "$BASE_DIR"
  touch "$ENV_FILE"; chmod 600 "$ENV_FILE"

  # Si no hay token admin, crea uno
  if ! grep -q '^VW_ADMIN_TOKEN=' "$ENV_FILE"; then
    kv_set_env "$ENV_FILE" VW_ADMIN_TOKEN "$(openssl rand -hex 32)"
  fi

  # SMTP por defecto si faltan
  local HOST PORT SEC
  HOST="$(smtp_host_by_region)"
  PORT="$DEFAULT_SMTP_PORT"
  SEC="$DEFAULT_SMTP_SECURITY"

  grep -q '^SMTP_HOST=' "$ENV_FILE"     || kv_set_env "$ENV_FILE" SMTP_HOST "$HOST"
  grep -q '^SMTP_PORT=' "$ENV_FILE"     || kv_set_env "$ENV_FILE" SMTP_PORT "$PORT"
  grep -q '^SMTP_SECURITY=' "$ENV_FILE" || kv_set_env "$ENV_FILE" SMTP_SECURITY "$SEC"
  grep -q '^SMTP_USERNAME=' "$ENV_FILE" || kv_set_env "$ENV_FILE" SMTP_USERNAME "$SMTP_USER_DEFAULT"
  grep -q '^SMTP_FROM=' "$ENV_FILE"     || kv_set_env "$ENV_FILE" SMTP_FROM "$SMTP_USER_DEFAULT"
  grep -q '^SMTP_FROM_NAME=' "$ENV_FILE"|| kv_set_env "$ENV_FILE" SMTP_FROM_NAME "$SMTP_FROM_NAME_DEFAULT"

  # La contraseña SMTP debe aportarse con --smtp-pass o variable SMTP_PASSWORD
  if ! grep -q '^SMTP_PASSWORD=' "$ENV_FILE"; then
    if [[ -n "${SMTP_PASSWORD:-}" ]]; then
      kv_set_env "$ENV_FILE" SMTP_PASSWORD "$SMTP_PASSWORD"
    else
      fail "falta SMTP_PASSWORD. Pásala con --smtp-pass '...' o exporta SMTP_PASSWORD y reintenta"
    fi
  fi
}

write_compose(){
  if [[ ! -f "$YML_FILE" ]]; then
    cat >"$YML_FILE" <<'EOF'
version: "3.8"
services:
  vaultwarden:
    image: vaultwarden/server:latest
    container_name: vaultwarden
    restart: unless-stopped
    environment:
      DOMAIN: "https://${DOMAIN}"
      SIGNUPS_ALLOWED: "false"
      INVITATIONS_ALLOWED: "true"
      ADMIN_TOKEN: "${VW_ADMIN_TOKEN}"
      SMTP_HOST: "${SMTP_HOST}"
      SMTP_PORT: "${SMTP_PORT}"
      SMTP_SECURITY: "${SMTP_SECURITY}"
      SMTP_USERNAME: "${SMTP_USERNAME}"
      SMTP_PASSWORD: "${SMTP_PASSWORD}"
      SMTP_FROM: "${SMTP_FROM}"
      SMTP_FROM_NAME: "${SMTP_FROM_NAME}"
    volumes:
      - /srv/vaultwarden/data:/data
    ports:
      - "127.0.0.1:8080:80"
EOF
    sed -i "s|\${DOMAIN}|${DOMAIN}|g" "$YML_FILE"
  fi
}

write_nginx(){
  if [[ ! -f "$SITE_FILE" ]]; then
    cat >"$SITE_FILE" <<EOF
server {
  listen 80;
  listen [::]:80;
  server_name ${DOMAIN};
  location / {
    proxy_pass http://127.0.0.1:8080;
    proxy_set_header Host \$host;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto http;
  }
}
EOF
    ln -sf "$SITE_FILE" /etc/nginx/sites-enabled/vaultwarden.conf
    nginx -t || fail "nginx config inválida"
    systemctl reload nginx
  fi

  # Certificado + redirección TLS
  if [[ ! -d "/etc/letsencrypt/live/${DOMAIN}" ]]; then
    certbot --nginx -d "${DOMAIN}" --redirect -n --agree-tos --email "${SMTP_USER_DEFAULT}"
  else
    # Asegura redirección si cambió algo
    certbot --nginx -d "${DOMAIN}" --redirect -n --agree-tos --email "${SMTP_USER_DEFAULT}" || true
  fi
}

ensure_stack(){
  ensure_tools
  write_env
  write_compose
  docker compose --env-file "$ENV_FILE" -f "$YML_FILE" up -d
  write_nginx
}

invite(){
  local email="$1"
  [[ "$email" =~ .+@.+ ]] || fail "email inválido"

  # shellcheck source=/dev/null
  source "$ENV_FILE"
  : "${VW_ADMIN_TOKEN:?falta VW_ADMIN_TOKEN en $ENV_FILE}"

  # espera a que /health esté ok
  local tries=40
  until curl -fsS "http://127.0.0.1:8080/health" >/dev/null 2>&1 || (( --tries == 0 )); do sleep 1; done
  (( tries > 0 )) || fail "vaultwarden no responde"

  # envía invitación
  curl -sS -f \
    -H "Admin-Token: $VW_ADMIN_TOKEN" \
    -H "Content-Type: application/json" \
    -X POST "http://127.0.0.1:8080/admin/invite" \
    -d "$(jq -c --null-input --arg email "$email" '{email:$email}')" \
    >/dev/null
}

# =================== CLI ===================
usage(){
cat <<EOF
Uso:
  $(basename "$0") --ensure [--region eu|na] [--smtp-user <addr>] [--smtp-pass '<secret>'] [--from-name 'NNM Secure']
  $(basename "$0") -a|--add <email> [--region eu|na] [--smtp-user <addr>] [--smtp-pass '<secret>'] [--from-name 'NNM Secure']

Notas:
  - Crea/asegura Vaultwarden en ${DOMAIN} y envía invitaciones por email.
  - SMTP OVH por defecto: EU ${DEFAULT_SMTP_HOST_EU} / NA ${DEFAULT_SMTP_HOST_NA}, puerto 465 TLS.
  - Requiere root. Salida JSON por stdout.
EOF
}

main(){
  need_root
  exec {lf}> "$LOCK" || fail "no lock"
  flock -w 60 "$lf" || fail "lock en uso"

  local action="" email="" from_name="" region=""
  while [[ $# -gt 0 ]]; do
    case "$1" in
      --ensure) action="ensure"; shift;;
      -a|--add) action="add"; email="${2:-}"; shift 2;;
      --region) REGION="${2:-eu}"; shift 2;;
      --smtp-user) SMTP_USER_DEFAULT="${2:-}"; shift 2;;
      --smtp-pass) SMTP_PASSWORD="${2:-}"; shift 2;;
      --from-name) SMTP_FROM_NAME_DEFAULT="${2:-NNM Secure}"; shift 2;;
      -h|--help) usage; exit 0;;
      *) fail "flag desconocido: $1";;
    esac
  done

  case "${action:-}" in
    ensure)
      ensure_stack
      json "ok" "stack ensured for ${DOMAIN}"
      ;;
    add)
      [[ -n "$email" ]] || fail "usa: $(basename "$0") -a <email>"
      ensure_stack
      invite "$email"
      json "ok" "invite sent to ${email}"
      ;;
    *)
      usage; exit 1;;
  esac
}

main "$@"
