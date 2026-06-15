#!/bin/sh
set -eu

: "${MYSQL_HOST:=gestor-arcon-db}"
: "${MYSQL_DATABASE:=newsoftware}"
: "${MYSQL_USER:=arcon_admin}"
: "${MYSQL_PASSWORD:?MYSQL_PASSWORD is required}"
: "${MSG_DB_NAME:=msg}"
: "${ADMIN_USER:=admin}"
: "${ADMIN_PASSWORD:?ADMIN_PASSWORD is required}"

echo "Aguardando banco ${MYSQL_HOST}..."
until mysql --skip-ssl -h"${MYSQL_HOST}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" -e "SELECT 1" "${MYSQL_DATABASE}" >/dev/null 2>&1; do
  sleep 2
done

php /usr/local/bin/gestor-bootstrap.php

exec "$@"
