#!/usr/bin/env bash
set -euo pipefail

# ========= Config por defecto (ajusta por flags o variables de entorno) =========
WG_DIR="${WG_DIR:-/etc/wireguard}"
KEY_DIR="$WG_DIR/keys"
CLI_DIR="$WG_DIR/clients"
IF_PREFIX="${IF_PREFIX:-wg}"
PORT_BASE="${PORT_BASE:-51820}"
PORT_MAX="${PORT_MAX:-51920}"
MAX_PEERS="${MAX_PEERS:-128}"        # cambia si quieres menos/más peers por server
DNS_DEFAULT="${DNS_DEFAULT:-1.1.1.1, 1.0.0.1}"
ALLOWED_DEFAULT="${ALLOWED_DEFAULT:-0.0.0.0/0, ::/0}"
ENDPOINT_HOST="${ENDPOINT_HOST:-REEMPLAZA_CON_TU_FQDN_O_IP}"   # o pásalo por -e
AUTOSTART="${AUTOSTART:-1}"          # 1=systemd enable --now
APPLY_LIVE="${APPLY_LIVE:-1}"        # 1=aplica wg set/addconf si está levantado
QR_BASE64="${QR_BASE64:-0}"          # 1=devuelve PNG en base64

# ========= Utilidades JSON seguras (sin jq) =========
json_escape() {
  local s=${1:-}
  s=${s//\\/\\\\}; s=${s//\"/\\\"}; s=${s//$'\n'/\\n}; s=${s//$'\r'/\\r}
  s=${s//$'\t'/\\t}
  printf '%s' "$s"
}
json_kv() { printf '"%s":"%s"' "$(json_escape "$1")" "$(json_escape "$2")"; }
json_obj_open(){ printf "{"; }
json_obj_close(){ printf "}"; }
json_pair(){ json_kv "$1" "$2"; }
json_comma(){ printf ","; }

# ========= Mensajería =========
fail(){ printf '[ERROR] %s\n' "$1" >&2; exit 1; }
need_root(){ [[ ${EUID:-0} -eq 0 ]] || fail "Ejecuta como root."; }

usage(){
cat >&2 <<'EOF'
Uso:
  # Crea cliente. Autoelige servidor. Si todos llenos, crea uno nuevo.
  wg-manage.sh client -n <cliente> [-s <puerto>] [-e <endpoint>] [--json]
  # Asegura servidor en puerto dado (idempotente)
  wg-manage.sh ensure-server -p <puerto> [-i <iface>] [-e <endpoint>] [--json]

Flags comunes:
  -e, --endpoint  FQDN o IP pública para Endpoint (por defecto ENDPOINT_HOST)
  --max-peers N   Límite de peers por server (default $MAX_PEERS)
  --port-base P   Puerto base (default $PORT_BASE)
  --port-max P    Puerto máximo (default $PORT_MAX)
  --if-prefix X   Prefijo de interfaz (default "$IF_PREFIX")
  --qr-b64        Devuelve QR en base64 en el JSON

Salida: JSON por stdout. Errores por stderr. Requiere root.
EOF
}

# ========= Detección de paquetes =========
pkg_mgr=""
detect_pkg_mgr(){
  if command -v apt-get >/dev/null; then pkg_mgr="apt";
  elif command -v dnf >/dev/null; then pkg_mgr="dnf";
  elif command -v yum >/dev/null; then pkg_mgr="yum";
  elif command -v pacman >/dev/null; then pkg_mgr="pacman";
  else pkg_mgr=""; fi
}
ensure_packages(){
  detect_pkg_mgr
  local missing=()
  command -v wg >/dev/null 2>&1 || missing+=("wireguard-tools")
  command -v qrencode >/dev/null 2>&1 || missing+=("qrencode")
  [[ ${#missing[@]} -eq 0 ]] && return 0
  [[ -n "$pkg_mgr" ]] || fail "No detecto gestor paquetes: ${missing[*]}"
  case "$pkg_mgr" in
    apt) apt-get update -y
         DEBIAN_FRONTEND=noninteractive apt-get install -y "${missing[@]}";;
    dnf) dnf install -y "${missing[@]}";;
    yum) yum install -y "${missing[@]}";;
    pacman) pacman -Sy --noconfirm "${missing[@]}";;
  esac
}

mkdirs(){ install -d -m 700 "$KEY_DIR" "$CLI_DIR"; }

# ========= Red y firewall =========
default_wan(){
  ip route show default 2>/dev/null | awk '/default/ {for(i=1;i<=NF;i++) if($i=="dev"){print $(i+1); exit}}'
}
fw_open_udp(){
  local port="$1"
  if command -v ufw >/dev/null 2>&1 && ufw status | grep -qi 'active'; then
    ufw allow proto udp to any port "$port" comment "WireGuard"
  elif command -v firewall-cmd >/dev/null 2>&1 && systemctl is-active --quiet firewalld; then
    firewall-cmd --add-port="${port}/udp" --permanent
    firewall-cmd --reload
  elif command -v iptables >/dev/null 2>&1; then
    iptables -C INPUT -p udp --dport "$port" -j ACCEPT 2>/dev/null || iptables -I INPUT -p udp --dport "$port" -j ACCEPT
    command -v netfilter-persistent >/dev/null 2>&1 && netfilter-persistent save || true
  else
    printf '[WARN] Abre manualmente %s/udp en firewall.\n' "$port" >&2
  fi
}
enable_forwarding(){
  sysctl -w net.ipv4.ip_forward=1 >/dev/null
  sysctl -w net.ipv6.conf.all.forwarding=1 >/dev/null || true
  sed -i '/^net\.ipv4\.ip_forward/d' /etc/sysctl.conf
  echo 'net.ipv4.ip_forward=1' >> /etc/sysctl.conf
}

# ========= Helpers WireGuard =========
subnet_for_iface(){
  local iface="$1" n
  if [[ "$iface" =~ ([0-9]+)$ ]]; then
    n="${BASH_REMATCH[1]}"; echo "10.8.$n.0/24"
  else
    local h; h=$(echo -n "$iface" | sha256sum | cut -c1-2)
    local o=$(( 100 + (16#"$h") % 100 )); echo "10.8.$o.0/24"
  fi
}
server_ip_from_cidr(){ local net="${1%/*}"; echo "$net" | awk -F. '{print $1"."$2"."$3".1"}'; }
peer_count(){ [[ -f "$1" ]] || { echo 0; return; }; grep -c '^\[Peer\]' "$1" || true; }
server_by_port(){
  local port="$1"
  for f in "$WG_DIR"/*.conf; do [[ -e "$f" ]] || continue
    grep -qE "^ListenPort\s*=\s*$port\$" "$f" && { echo "$f"; return; }
  done
  return 1
}
least_loaded_server(){
  local best="" cnt bestcnt=999999
  for f in "$WG_DIR"/*.conf; do [[ -e "$f" ]] || continue
    cnt=$(peer_count "$f"); ((cnt<bestcnt)) && { bestcnt=$cnt; best="$f"; }
  done
  [[ -n "$best" ]] && echo "$best"
}
port_in_use(){
  local p="$1"
  ss -Hnu 'sport = :'"$p" 2>/dev/null | grep -q .
}
next_free_port(){
  local p
  for ((p=PORT_BASE; p<=PORT_MAX; p++)); do
    if ! server_by_port "$p" >/dev/null 2>&1 && ! port_in_use "$p"; then
      echo "$p"; return 0
    fi
  done
  return 1
}
used_ips(){ local conf="$1"; grep -E '^AllowedIPs\s*=\s*[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/32' "$conf" | sed -E 's/.*= *([0-9\.]+)\/32.*/\1/'; }
next_ip(){
  local cidr="$1" conf="$2" net base ip
  net="${cidr%/*}"; base=$(echo "$net" | awk -F. '{print $1"."$2"."$3}')
  declare -A used=()
  while read -r ip; do [[ -n "${ip:-}" ]] && used["$ip"]=1; done < <(used_ips "$conf" || true)
  used["$base.1"]=1
  local host
  for host in $(seq 2 254); do ip="$base.$host"; [[ -z "${used[$ip]+x}" ]] && { echo "$ip"; return; }; done
  echo "ERROR"
}

# ========= Creación de servidor =========
create_server(){
  local port="$1" iface="$2" endpoint="${3:-$ENDPOINT_HOST}"
  local conf="$WG_DIR/$iface.conf"
  [[ -e "$conf" ]] && { echo "$conf"; return 0; }

  ensure_packages; mkdirs; enable_forwarding
  local WAN; WAN="$(default_wan)"; [[ -z "$WAN" ]] && WAN="eth0"
  fw_open_udp "$port"

  umask 077
  local sk="$KEY_DIR/$iface.private" pk="$KEY_DIR/$iface.public"
  wg genkey | tee "$sk" | wg pubkey > "$pk"

  local cidr; cidr=$(subnet_for_iface "$iface")
  local srv_ip; srv_ip=$(server_ip_from_cidr "$cidr")
  local net="${cidr%/*}"

  cat > "$conf" <<EOF
[Interface]
Address = $srv_ip/${cidr#*/}
ListenPort = $port
PrivateKey = $(cat "$sk")
PostUp = sysctl -w net.ipv4.ip_forward=1 >/dev/null; iptables -t nat -A POSTROUTING -s $net/24 -o $WAN -j MASQUERADE; iptables -A FORWARD -i $iface -o $WAN -j ACCEPT; iptables -A FORWARD -i $WAN -o $iface -m state --state RELATED,ESTABLISHED -j ACCEPT; iptables -A FORWARD -i $iface -o $iface -j DROP
PostDown = iptables -t nat -D POSTROUTING -s $net/24 -o $WAN -j MASQUERADE; iptables -D FORWARD -i $iface -o $WAN -j ACCEPT; iptables -D FORWARD -i $WAN -o $iface -m state --state RELATED,ESTABLISHED -j ACCEPT; iptables -D FORWARD -i $iface -o $iface -j DROP
SaveConfig = false
# Endpoint: $endpoint
EOF

  chmod 600 "$conf"
  if [[ "$AUTOSTART" = "1" ]]; then
    systemctl enable --now "wg-quick@$iface" >/dev/null 2>&1 || wg-quick up "$iface"
  fi

  echo "$conf"
}

# ========= Alta de cliente =========
add_client(){
  local server_conf="$1" cname="$2" endpoint="$3" dns="${4:-$DNS_DEFAULT}" allowed="${5:-$ALLOWED_DEFAULT}"
  local iface; iface=$(basename "$server_conf" .conf)

  umask 077
  local cli_path="$CLI_DIR/$iface"; install -d -m 700 "$cli_path"
  local csk="$cli_path/$cname.private" cpk="$cli_path/$cname.public" psk="$cli_path/$cname.psk"
  wg genkey | tee "$csk" | wg pubkey > "$cpk"
  wg genpsk > "$psk"

  local srv_port; srv_port=$(grep -E '^ListenPort' "$server_conf" | awk -F'= *' '{print $2}')
  local srv_priv; srv_priv=$(grep -E '^PrivateKey' "$server_conf" | awk -E '= *' '{print $2}' || true)
  # si private key no está accesible, derivamos pub desde interfaz en vivo
  local srv_pub=""
  if [[ -n "${srv_priv:-}" ]]; then
    srv_pub=$(echo "$srv_priv" | wg pubkey)
  else
    srv_pub=$(wg show "$iface" public-key)
  fi

  local addr_line; addr_line=$(grep -E '^Address' "$server_conf" | head -n1 | awk -F'= *' '{print $2}')
  local netcidr="${addr_line%%,*}"
  local cli_ip; cli_ip=$(next_ip "$netcidr" "$server_conf"); [[ "$cli_ip" == "ERROR" ]] && fail "Sin IPs libres en $netcidr"

  local cconf="$cli_path/$cname.conf"
  cat > "$cconf" <<EOF
[Interface]
PrivateKey = $(cat "$csk")
Address = $cli_ip/32
DNS = $dns

[Peer]
PublicKey = $srv_pub
PresharedKey = $(cat "$psk")
AllowedIPs = $allowed
Endpoint = ${endpoint:-$ENDPOINT_HOST}:$srv_port
PersistentKeepalive = 25
EOF
  chmod 600 "$cconf"

  # Append seguro a server.conf
  {
    printf '\n[Peer]\n# %s\nPublicKey = %s\nPresharedKey = %s\nAllowedIPs = %s/32\n' \
      "$cname" "$(cat "$cpk")" "$(cat "$psk")" "$cli_ip"
  } >> "$server_conf"

  # Aplica en vivo si procede
  if [[ "$APPLY_LIVE" = "1" ]] && wg show "$iface" >/dev/null 2>&1; then
    wg set "$iface" peer "$(cat "$cpk")" preshared-key "$psk" allowed-ips "$cli_ip/32"
  fi

  # QR
  local png="$cli_path/$cname.png"
  qrencode -o "$png" < "$cconf"

  # Salida JSON
  json_obj_open
    json_pair "status" "ok"; json_comma
    json_pair "iface" "$iface"; json_comma
    json_pair "server_conf" "$server_conf"; json_comma
    json_pair "server_port" "$srv_port"; json_comma
    json_pair "client_name" "$cname"; json_comma
    json_pair "client_ip" "$cli_ip"; json_comma
    json_pair "client_conf_path" "$cconf"; json_comma
    json_pair "qr_path" "$png"; 
    if [[ "$QR_BASE64" = "1" ]]; then
      json_comma
      local b64; b64=$(base64 -w0 "$png" 2>/dev/null || base64 "$png")
      json_pair "qr_base64" "$b64"
    fi
  json_obj_close
  printf '\n'
}

# ========= Elección de servidor con capacidad =========
ensure_server_on_port(){
  local port="$1" iface="$2" endpoint="$3"
  local conf; conf=$(server_by_port "$port" || true)
  if [[ -n "$conf" ]]; then
    echo "$conf"; return 0
  fi
  [[ -z "$iface" ]] && iface="${IF_PREFIX}$(( port - PORT_BASE ))"
  create_server "$port" "$iface" "$endpoint"
}
choose_server_with_capacity(){
  local endpoint="$1"
  # 1) si hay alguno con hueco, escoger el de menor carga
  local best=""; best=$(least_loaded_server || true)
  if [[ -n "$best" ]] && (( $(peer_count "$best") < MAX_PEERS )); then
    echo "$best"; return 0
  fi
  # 2) si no, crear uno nuevo en siguiente puerto libre
  local np; np=$(next_free_port) || fail "No hay puertos libres entre $PORT_BASE-$PORT_MAX"
  local iface="${IF_PREFIX}$(( np - PORT_BASE ))"
  local conf; conf=$(create_server "$np" "$iface" "$endpoint")
  echo "$conf"
}

# ========= Entrada principal =========
main(){
  need_root
  [[ $# -ge 1 ]] || { usage; exit 1; }

  # Lock global
  install -d -m 755 "$WG_DIR"
  exec {lockfd}>"/run/wg-manage.lock"
  flock -w 30 "$lockfd" || fail "Lock en uso"

  local cmd="$1"; shift
  local cname="" port="" iface="" endpoint="$ENDPOINT_HOST"

  # Parse flags genéricos
  while [[ $# -gt 0 ]]; do
    case "$1" in
      -n|--name) cname="$2"; shift 2;;
      -p|--port) port="$2"; shift 2;;
      -i|--iface) iface="$2"; shift 2;;
      -e|--endpoint) endpoint="$2"; shift 2;;
      --max-peers) MAX_PEERS="$2"; shift 2;;
      --port-base) PORT_BASE="$2"; shift 2;;
      --port-max) PORT_MAX="$2"; shift 2;;
      --if-prefix) IF_PREFIX="$2"; shift 2;;
      --qr-b64) QR_BASE64=1; shift;;
      -h|--help) usage; exit 0;;
      *) break;;
    esac
  done

  case "$cmd" in
    ensure-server)
      [[ -n "$port" ]] || fail "Falta -p|--port"
      local conf; conf=$(ensure_server_on_port "$port" "$iface" "$endpoint")
      json_obj_open
        json_pair "status" "ok"; json_comma
        json_pair "server_conf" "$conf"; json_comma
        json_pair "server_port" "$port"; json_comma
        json_pair "iface" "$(basename "$conf" .conf)"; json_comma
        json_pair "peers" "$(peer_count "$conf")"
      json_obj_close; printf '\n'
      ;;
    client)
      [[ -n "$cname" ]] || fail "Falta -n|--name <cliente>"
      mkdirs; ensure_packages
      local server_conf=""
      if [[ -n "$port" ]]; then
        server_conf=$(server_by_port "$port" || true)
        if [[ -z "$server_conf" ]]; then
          # si no existe, créalo
          server_conf=$(ensure_server_on_port "$port" "$iface" "$endpoint")
        fi
        if (( $(peer_count "$server_conf") >= MAX_PEERS )); then
          # lleno: buscar/crear otro
          server_conf=$(choose_server_with_capacity "$endpoint")
        fi
      else
        server_conf=$(choose_server_with_capacity "$endpoint")
      fi
      add_client "$server_conf" "$cname" "$endpoint" "$DNS_DEFAULT" "$ALLOWED_DEFAULT"
      ;;
    *)
      usage; exit 1;;
  esac
}

main "$@"
