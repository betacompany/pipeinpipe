#!/bin/bash
# Updates the code of one instance to the latest commit of its branches and
# reloads PHP (running requests get up to 30 s to finish).
#
#   sudo server/deploy.sh prod|dev
set -euo pipefail

NAME=$1
HOST=pipeinpipe.info
[ "$NAME" = prod ] || HOST=$NAME.pipeinpipe.info
DEPLOY_USER=${DEPLOY_USER:-ortemij}

for dir in "/var/www/$HOST" "/var/www/total.$HOST"; do
	# Connections from Yandex Cloud to github.com fail now and then
	for i in 1 2 3 4 5; do
		sudo -u "$DEPLOY_USER" git -C "$dir" pull -q --ff-only && break
		[ "$i" = 5 ] && exit 1
		echo "  pull failed, retrying in 5 s" >&2
		sleep 5
	done
	echo "$dir: $(sudo -u "$DEPLOY_USER" git -C "$dir" log -1 --format='%h %s')"
done
systemctl reload php8.5-fpm
