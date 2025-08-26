#!/usr/bin/env bash
set -euo pipefail
# storage.sh — gestor Seafile para NNM
# Convenciones:
# - Usa /opt/nnm-seafile como raíz.
# - docker compose con perfiles.
# - Lee .env para puertos/hostnames.
# - Comandos: install|start|stop|restart|status|logs|admin-url|backup|restore

ROOT="/opt/nnm-seafile"
ENV_FILE="${ROOT}/.env"
COMPOSE="${ROOT}/docker-compose.yml"
DATA="${ROOT}/data"
BKDIR="${ROOT}/backup"
DOMAIN="${SEAFILE_DOMAIN:-storage.northnexusmex.cloud}"
HTTP_PORT="${SEAFILE_HTTP_PORT:-8082}"
ADMIN_EMAIL="${SEAFILE_ADMIN_EMAIL:-admin@northnexusmex.cloud}"
ADMIN_PASS="${SEAFILE_ADMIN_PASS:-ChangeMe-$(tr -dc A-Za-z0-9 </dev/urandom | head -c 8)}"
IMG="seafileltd/seafile-mc:latest"   # multi-container oficial

ensure_root() { [ "${EUID:-$(id -u)}" -eq 0 ] || { echo "root requerido" >&2; exit 1; }; }
ensure_dirs() { mkdir -p "$ROOT" "$DATA" "$BKDIR"; }

write_env() {
  cat > "$ENV_FILE" <<EOF
SEAFILE_DOMAIN=${DOMAIN}
SEAFILE_HTTP_PORT=${HTTP_PORT}
SEAFILE_ADMIN_EMAIL=${ADMIN_EMAIL}
SEAFILE_ADMIN_PASS=${ADMIN_PASS}
EOF
}

write_compose() {
  cat > "$COMPOSE" <<'YML'
services:
  seafile:
    image: seafileltd/seafile-mc:latest
    container_name: seafile
    restart: unless-stopped
    ports:
      - "${SEAFILE_HTTP_PORT:-8082}:80"
    volumes:
      - ./data:/shared
    environment:
      - SEAFILE_SERVER_HOSTNAME=${SEAFILE_DOMAIN}
      - SEAFILE_ADMIN_EMAIL=${SEAFILE_ADMIN_EMAIL}
      - SEAFILE_ADMIN_PASSWORD=${SEAFILE_ADMIN_PASS}
      - TIME_ZONE=Europe/Madrid
    healthcheck:
      test: ["CMD","curl","-fsS","http://localhost/"]
      interval: 30s
      timeout: 5s
      retries: 10
YML
}

dc() { docker compose -f "$COMPOSE" "$@"; }

cmd_install() {
  ensure_root; ensure_dirs
  command -v docker >/dev/null || { echo "docker requerido"; exit 1; }
  command -v docker compose >/dev/null || { echo "docker compose v2 requerido"; exit 1; }
  [ -f "$ENV_FILE" ] || write_env
  write_compose
  echo "Inicializando Seafile…"
  dc up -d
  echo "STATUS: STARTED"
  echo "ADMIN: http://${DOMAIN}:${HTTP_PORT}/"
  echo "Credenciales: ${ADMIN_EMAIL} / ${ADMIN_PASS}"
}

cmd_start()   { ensure_root; dc up -d; echo "STATUS: STARTED"; }
cmd_stop()    { ensure_root; dc down;  echo "STATUS: STOPPED"; }
cmd_restart() { ensure_root; dc down; dc up -d; echo "STATUS: STARTED"; }
cmd_status()  {
  if docker ps --format '{{.Names}}' | grep -q '^seafile$'; then
    echo "STATUS: RUNNING"
  else
    echo "STATUS: STOPPED"
    exit 1
  fi
}
cmd_logs()    { ensure_root; dc logs -n 100 --tail=100 -f; }
cmd_admin_url(){ echo "http://${DOMAIN}:${HTTP_PORT}/"; }

# backup simple de volumen compartido
cmd_backup()  {
  ensure_root; ts="$(date +%Y%m%d-%H%M%S)"
  tar -C "$ROOT" -czf "${BKDIR}/seafile-${ts}.tgz" data
  echo "Backup: ${BKDIR}/seafile-${ts}.tgz"
}

cmd_restore() {
  ensure_root
  file="${1:-}"; [ -f "$file" ] || { echo "uso: $0 restore /ruta/backup.tgz"; exit 1; }
  cmd_stop || true
  rm -rf "$DATA"
  tar -C "$ROOT" -xzf "$file"
  cmd_start
}

usage(){
  cat <<USAGE
Uso: $0 <comando> [args]
Comandos: install | start | stop | restart | status | logs | admin-url | backup | restore <tgz>
USAGE
}

main(){
  cmd="${1:-}"; shift || true
  case "$cmd" in
    install) cmd_install "$@" ;;
    start) cmd_start ;;
    stop) cmd_stop ;;
    restart) cmd_restart ;;
    status) cmd_status ;;
    logs) cmd_logs ;;
    admin-url) cmd_admin_url ;;
    backup) cmd_backup ;;
    restore) cmd_restore "$@" ;;
    *) usage; exit 2 ;;
  esac
}
main "$@"
