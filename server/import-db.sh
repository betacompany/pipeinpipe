#!/bin/bash
# Replaces one database with a gzipped dump read from stdin. The dump is of a
# single database without --databases, so it can be loaded under any name:
#
#   ssh OLD 'sudo mysqldump --defaults-file=/etc/mysql/debian.cnf --default-character-set=utf8 \
#       --quick --routines --triggers pipeinpipe | gzip' \
#     | ssh NEW 'sudo /var/www/pipeinpipe.info/server/import-db.sh pipeinpipe_dev'
set -euo pipefail

DB=$1
mariadb -e "DROP DATABASE IF EXISTS \`$DB\`; CREATE DATABASE \`$DB\` CHARACTER SET utf8mb3"

# The old server's views belong to root@localhost and ortemij@%; without
# DEFINER they belong to the importing user and work here
gunzip -c | sed -E 's/DEFINER=`[^`]+`@`[^`]+`//g' | mariadb --default-character-set=utf8 "$DB"

mariadb -N -e "SELECT CONCAT('$DB: ', COUNT(*), ' tables') FROM information_schema.tables WHERE table_schema = '$DB'"
