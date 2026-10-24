#!/bin/sh
# Copies prod databases, uploaded content and prebuilt assets into dev/data/.
# Read-only on the server: mysqldump (brief per-database read locks) + rsync.
set -eu

HOST=${PROD_HOST:-ortemij@158.160.188.92}
DIR=$(cd "$(dirname "$0")/.." && pwd)/data
mkdir -p "$DIR/db" "$DIR/content" "$DIR/assets"

echo "Dumping databases..."
ssh "$HOST" 'sudo -n nice mysqldump --defaults-file=/etc/mysql/debian.cnf \
	--databases pipeinpipe totalizator \
	--default-character-set=utf8 --quick --routines --triggers | gzip -c' \
	> "$DIR/db/prod.sql.gz.tmp"
mv "$DIR/db/prod.sql.gz.tmp" "$DIR/db/prod.sql.gz"
ls -la "$DIR/db/prod.sql.gz"

echo "Syncing content..."
rsync -az --delete "$HOST:pipeinpipe.info.content/" "$DIR/content/"

echo "Syncing prebuilt assets (all.css, all.js are not in git)..."
rsync -az "$HOST:pipeinpipe.info/main/css/all.css" "$HOST:pipeinpipe.info/main/js/all.js" "$DIR/assets/"

du -sh "$DIR"/*
