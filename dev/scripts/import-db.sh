#!/bin/sh
# (Re)creates the pipeinpipe and totalizator databases in the dev MariaDB
# from data/db/prod.sql.gz (made by fetch-prod-data.sh), with the prod users.
set -eu
cd "$(dirname "$0")/.."

DUMP=data/db/prod.sql.gz
[ -f "$DUMP" ] || { echo "No $DUMP, run scripts/fetch-prod-data.sh first" >&2; exit 1; }

docker compose exec -T mariadb mariadb -uroot -pdev <<'SQL'
DROP DATABASE IF EXISTS pipeinpipe;
DROP DATABASE IF EXISTS totalizator;
CREATE USER IF NOT EXISTS 'pipeman'@'localhost' IDENTIFIED BY 'dev';
CREATE USER IF NOT EXISTS 'pipeman'@'%' IDENTIFIED BY 'dev';
CREATE USER IF NOT EXISTS 'totalizator'@'localhost' IDENTIFIED BY 'dev';
CREATE USER IF NOT EXISTS 'totalizator'@'%' IDENTIFIED BY 'dev';
GRANT ALL ON pipeinpipe.* TO 'pipeman'@'localhost', 'pipeman'@'%';
GRANT ALL ON totalizator.* TO 'totalizator'@'localhost', 'totalizator'@'%';
SQL

# The prod views are owned by root@localhost and ortemij@%; drop DEFINER so
# they belong to the importing user instead of a user that doesn't exist here.
gunzip -c "$DUMP" \
	| sed -E 's/DEFINER=`[^`]+`@`[^`]+`//g' \
	| docker compose exec -T mariadb mariadb -uroot -pdev --default-character-set=utf8

docker compose exec -T mariadb mariadb -uroot -pdev -N -e "
	SELECT table_schema, COUNT(*) AS tables, ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS mb
	FROM information_schema.tables
	WHERE table_schema IN ('pipeinpipe', 'totalizator')
	GROUP BY table_schema"
