#!/usr/bin/env bash
set -euo pipefail

WG_DIR="/etc/wireguard"
KEY_DIR="$WG_DIR/keys"
CLI_DIR="$WG_DIR/clients"

usage() {
cat <<'EOF'
Uso:
  Servidor: wg-manage.sh s <port> -n <iface>
  Cliente : wg-manage.sh c <client_name> [-s <port>]

Hace:
  - Instala wireguard-tools y qrencode si faltan.
  - Abre puerto UDP en firewall.
  - Server con NAT, forwarding y aislamiento peer-to-peer.
  - Cliente con QR en PNG.

Requiere root.
EOF
}

need_root(){ [[ ${EUID:-0} -eq 0 ]] || { echo "Ejecuta como root." >&2; exit 1; }; }

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
  [[ -n "$pkg_mgr" ]] || { echo "No detecto gestor de paquetes para instalar: ${missing[*]}" >&2; exit 1; }
  case "$pkg_mgr" in
    apt)
      apt-get update -y
      # wireguard-tools en Debian/Ubuntu; qrencode está en repos
      DEBIAN_FRONTEND=noninteractive apt-get install -y "${missing[@]}"
      ;;
    dnf) dnf install -y "${missing[@]}" ;;
    yum) yum install -y "${missing[@]}" ;;
    pacman) pacman -Sy --noconfirm "${missing[@]}" ;;
  esac
}

mkdirs(){ install -d -m 700 "$KEY_DIR" "$CLI_DIR"; }

# Subred por interfaz: wg0->10.8.0.0/24, wg1->10.8.1.0/24...
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
used_ips(){ local conf="$1"; grep -E '^AllowedIPs\s*=\s*[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/32' "$conf" | sed -E 's/.*= *([0-9\.]+)\/32.*/\1/'; }
next_ip(){
  local cidr="$1" conf="$2" net base ip
  net="${cidr%/*}"; base=$(echo "$net" | awk -F. '{print $1"."$2"."$3}')
  declare -A used=()
  while read -r ip; do [[ -n "${ip:-}" ]] && used["$ip"]=1; done < <(used_ips "$conf" || true)
  used["$base.1"]=1
  for host in $(seq 2 254); do ip="$base.$host"; [[ -z "${used[$ip]+x}" ]] && { echo "$ip"; return; }; done
  echo "ERROR"
}
peer_count(){ grep -c '^\[Peer\]' "$1" || true; }
least_loaded_server(){
  local best="" cnt bestcnt=999999
  for f in "$WG_DIR"/*.conf; do [[ -e "$f" ]] || continue; cnt=$(peer_count "$f"); ((cnt<bestcnt)) && { bestcnt=$cnt; best="$f"; }; done
  [[ -n "$best" ]] && echo "$best"
}
server_by_port(){
  local port="$1"
  for f in "$WG_DIR"/*.conf; do [[ -e "$f" ]] || continue; grep -qE "^ListenPort\s*=\s*$port\$" "$f" && { echo "$f"; return; }; done
  return 1
}

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
    echo "Aviso: no se detectó firewall gestionable; abre el puerto $port/udp manualmente." >&2
  fi
}

enable_forwarding_once(){
  sysctl -w net.ipv4.ip_forward=1 >/dev/null
  sysctl -w net.ipv6.conf.all.forwarding=1 >/dev/null || true
  # Persistente
  sed -i '/^net.ipv4.ip_forward/d' /etc/sysctl.conf
  echo 'net.ipv4.ip_forward=1' >> /etc/sysctl.conf
}

create_server(){
  local port="$1" iface="$2"
  local conf="$WG_DIR/$iface.conf"
  [[ -e "$conf" ]] && { echo "Ya existe $conf" >&2; exit 1; }

  ensure_packages
  mkdirs
  enable_forwarding_once

  local WAN; WAN="$(default_wan)"; [[ -z "$WAN" ]] && WAN="eth0"
  fw_open_udp "$port"

  umask 077
  local sk="$KEY_DIR/$iface.private" pk="$KEY_DIR/$iface.public"
  wg genkey | tee "$sk" | wg pubkey > "$pk"

  local cidr; cidr=$(subnet_for_iface "$iface")
  local srv_ip; srv_ip=$(server_ip_from_cidr "$cidr")
  local net="${cidr%/*}"

  # Reglas PostUp/PostDown:
  # - NAT hacia WAN
  # - permitir tráfico wg->WAN y retorno
  # - BLOQUEAR tráfico entre peers en la misma wg (aislamiento)
  cat > "$conf" <<EOF
[Interface]
Address = $srv_ip/${cidr#*/}
ListenPort = $port
PrivateKey = $(cat "$sk")
PostUp = sysctl -w net.ipv4.ip_forward=1 >/dev/null; iptables -t nat -A POSTROUTING -s $net/24 -o $WAN -j MASQUERADE; iptables -A FORWARD -i $iface -o $WAN -j ACCEPT; iptables -A FORWARD -i $WAN -o $iface -m state --state RELATED,ESTABLISHED -j ACCEPT; iptables -A FORWARD -i $iface -o $iface -j DROP
PostDown = iptables -t nat -D POSTROUTING -s $net/24 -o $WAN -j MASQUERADE; iptables -D FORWARD -i $iface -o $WAN -j ACCEPT; iptables -D FORWARD -i $WAN -o $iface -m state --state RELATED,ESTABLISHED -j ACCEPT; iptables -D FORWARD -i $iface -o $iface -j DROP
SaveConfig = false
EOF

  chmod 600 "$conf"
  echo "Servidor creado: $conf"
  echo "Claves: $sk / $pk"
  echo "Levanta: systemctl enable --now wg-quick@$iface || wg-quick up $iface"
}

create_client(){
  local cname="$1"; shift
  local specified_port=""
  while getopts ":s:" opt; do case "$opt" in s) specified_port="$OPTARG";; esac; done

  local server_conf=""
  if [[ -n "$specified_port" ]]; then
    server_conf=$(server_by_port "$specified_port") || { echo "No hay server con ListenPort=$specified_port" >&2; exit 1; }
  else
    server_conf=$(least_loaded_server) || { echo "No hay servidores en $WG_DIR" >&2; exit 1; }
  fi
  local iface; iface=$(basename "$server_conf" .conf)

  mkdirs
  umask 077
  local cli_path="$CLI_DIR/$iface"; install -d -m 700 "$cli_path"
  local csk="$cli_path/$cname.private" cpk="$cli_path/$cname.public" psk="$cli_path/$cname.psk"
  wg genkey | tee "$csk" | wg pubkey > "$cpk"
  wg genpsk > "$psk"

  local srv_port; srv_port=$(grep -E '^ListenPort' "$server_conf" | awk -F'= *' '{print $2}')
  local srv_priv; srv_priv=$(grep -E '^PrivateKey' "$server_conf" | awk -F'= *' '{print $2}')
  local srv_pub; srv_pub=$(echo "$srv_priv" | wg pubkey)
  local addr_line; addr_line=$(grep -E '^Address' "$server_conf" | head -n1 | awk -F'= *' '{print $2}')
  local netcidr="${addr_line%%,*}"
  local cli_ip; cli_ip=$(next_ip "$netcidr" "$server_conf"); [[ "$cli_ip" == "ERROR" ]] && { echo "Sin IPs libres en $netcidr" >&2; exit 1; }

  # Ajusta con tu FQDN/IP pública
  local endpoint_host="REEMPLAZA_CON_TU_FQDN_O_IP"
  local allowed="0.0.0.0/0, ::/0"
  local dns="1.1.1.1, 1.0.0.1"

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
Endpoint = $endpoint_host:$srv_port
PersistentKeepalive = 25
EOF
  chmod 600 "$cconf"

  # Añadir peer al server (aislado por AllowedIPs /32 en server)
  cat >> "$server_conf" <<EOF

[Peer]
# $cname
PublicKey = $(cat "$cpk")
PresharedKey = $(cat "$psk")
AllowedIPs = $cli_ip/32
EOF

  # QR del cliente
  qrencode -o "$cli_path/$cname.png" < "$cconf"

  echo "Cliente: $cconf"
  echo "QR: $cli_path/$cname.png"
  echo "Añadido a: $server_conf"
  echo "Aplica: wg addconf $iface <(wg-quick strip $server_conf)  # o systemctl restart wg-quick@$iface"
}

main(){
  need_root
  [[ $# -ge 1 ]] || { usage; exit 1; }
  case "$1" in
    s)
      [[ $# -ge 3 ]] || { usage; exit 1; }
      local port="$2"; shift 2
      local iface=""
      while getopts ":n:" opt; do case "$opt" in n) iface="$OPTARG";; esac; done
      [[ -n "$iface" ]] || { echo "Falta -n <iface>" >&2; exit 1; }
      create_server "$port" "$iface"
      ;;
    c)
      [[ $# -ge 2 ]] || { usage; exit 1; }
      local cname="$2"; shift 2
      create_client "$cname" "$@"
      ;;
    *)
      usage; exit 1;;
  esac
}

main "$@"
