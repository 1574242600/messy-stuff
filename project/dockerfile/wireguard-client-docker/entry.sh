#!/usr/bin/env bash
set -e

DEFAULT_GATEWAY_NETWORK_CARD_NAME=`ip route | grep default | awk '{print $5}'`
DEFAULT_ROUTE_IP=`ip addr show $DEFAULT_GATEWAY_NETWORK_CARD_NAME | grep "inet " | awk '{print $2}' | sed "s/\/.*//"`
#WIREGUARD_CONFIGS_PATH=/etc/wireguard
CONFIG_NAMES="$1"

main() {
  trap '_down' TERM INT
  trap '_error' ERR ABRT

  ### https://github.com/jordanpotter/docker-wireguard/blob/74c36454059b52814bb7701ae2ebc235cd72b300/entrypoint.sh#L20-L26
  if [[ "$(cat /proc/sys/net/ipv4/conf/all/src_valid_mark)" != "1" ]]; then
    echo "sysctl net.ipv4.conf.all.src_valid_mark=1 is not set" >&2
    exit 1
  fi

  sed -i "s:sysctl -q net.ipv4.conf.all.src_valid_mark=1:echo Skipping setting net.ipv4.conf.all.src_valid_mark:" /usr/bin/wg-quick
  ###
  
  ip rule add from $DEFAULT_ROUTE_IP lookup main prio 0
  for config_name in $(echo $CONFIG_NAMES); do
    echo ;
    wg-quick up $config_name
  done

  sleep 3
  echo ; _checkV4 ; echo ; _checkV6

  sleep infinity & wait
}

_down() {
  for config_name in $(echo $CONFIG_NAMES); do
    echo ;
    wg-quick down $config_name
  done

  ip rule del from $DEFAULT_ROUTE_IP lookup main prio 0

  exit ;
}

_error() {
  if [[ $_DEBUG = 1 ]]; then sleep infinity; fi;
  exit 2
}

_checkV4() {
  _check 4
}

_checkV6() {
  _check 6
}

_check() {
  echo "Checking ipv$1 network status, please wait...."; echo;

  errorCount=0;

  while ! curl -s$1 --max-time 2  https://www.cloudflare.com/cdn-cgi/trace/; do
    if [[ $errorCount = 3 ]]; then return 1; fi;

    echo "Sleep 3 and retry again. count: $((errorCount + 1))/3";
    sleep 3;
    
    let errorCount++;
  done
}

main
