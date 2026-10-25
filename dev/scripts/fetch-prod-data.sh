#!/bin/sh
# Copies the prod databases, uploaded content and prebuilt assets from the
# server (see server/README.md) into dev/data/. Read-only on the server:
# mariadb-dump (brief per-database read locks) + rsync.
#
#   scripts/fetch-prod-data.sh                  # from ortemij@158.160.188.92
#   PROD_HOST=user@host scripts/fetch-prod-data.sh
set -eu

HOST=${PROD_HOST:-ortemij@158.160.188.92}
DIR=$(cd "$(dirname "$0")/.." && pwd)/data
mkdir -p "$DIR/db" "$DIR/content" "$DIR/assets"

echo "Dumping databases..."
# As root over the unix socket
ssh "$HOST" 'sudo -n nice mariadb-dump --default-character-set=utf8 --quick --routines --triggers \
	--databases pipeinpipe totalizator | gzip' > "$DIR/db/prod.sql.gz.tmp"
# Only gzip's exit status comes back, so a failed dump shows as a missing last line
gunzip -c "$DIR/db/prod.sql.gz.tmp" | tail -n 1 | grep -q '^-- Dump completed' \
	|| { echo "The dump is incomplete, keeping the old one" >&2; exit 1; }
mv "$DIR/db/prod.sql.gz.tmp" "$DIR/db/prod.sql.gz"
ls -la "$DIR/db/prod.sql.gz"

echo "Syncing content..."
SRC=/srv/pipeinpipe/prod/content
set --
for d in users competitions pipemen leagues photos videos download; do
	set -- "$@" "$HOST:$SRC/$d"
done
rsync -az --delete "$@" "$DIR/content/"

echo "Syncing prebuilt assets (all.css, all.js are not in git)..."
rsync -az "$HOST:/var/www/pipeinpipe.info/main/css/all.css" "$HOST:/var/www/pipeinpipe.info/main/js/all.js" "$DIR/assets/"
ls -la "$DIR/assets"

du -sh "$DIR/db" "$DIR/content" "$DIR/assets"
