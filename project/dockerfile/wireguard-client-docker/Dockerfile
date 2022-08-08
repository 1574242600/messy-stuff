FROM alpine:3
COPY entry.sh /entry.sh

RUN apk update -f \
  && apk --no-cache add -f \
  curl ca-certificates \
  iproute2 iptables \
  wireguard-tools openresolv \
  && rm -rf /var/cache/apk/*

VOLUME /etc/wireguard/

ENTRYPOINT ["/bin/bash", "/entry.sh"]
