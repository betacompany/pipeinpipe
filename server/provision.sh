#!/bin/bash
# Sets up a pipeinpipe server on Ubuntu 26.04: nginx, PHP 8.5-FPM, MariaDB and
# two instances of the sites, prod (pipeinpipe.info, m., cupms., total.) and
# dev (dev.pipeinpipe.info and the same subdomains under it). Safe to re-run.
#
#   sudo server/provision.sh
#
# Databases, uploaded content and the prebuilt CSS/JS are loaded separately,
# see server/README.md. Secrets are generated on first run into $SECRETS.
set -euo pipefail

HERE=$(cd "$(dirname "$0")" && pwd)
DEPLOY_USER=${DEPLOY_USER:-ortemij}
PIPE_REPO=https://github.com/betacompany/pipeinpipe.git
TOTAL_REPO=https://github.com/betacompany/totalizator.git
# Only used for new checkouts
PIPE_BRANCH=${PIPE_BRANCH:-master}
TOTAL_BRANCH=${TOTAL_BRANCH:-develop}

INSTANCES="prod dev"
SECRETS=/etc/pipeinpipe/secrets.env
PHP_SOCK=/run/php/php8.5-fpm.sock
# Let's Encrypt sends expiry warnings here. CERTBOT_ARGS=--dry-run tries the
# certificates against its staging servers without keeping them.
LETSENCRYPT_EMAIL=${LETSENCRYPT_EMAIL:-ortemij@gmail.com}
CERTBOT_ARGS=${CERTBOT_ARGS:-}

instance_vars() {
	NAME=$1
	if [ "$NAME" = prod ]; then
		HOST=pipeinpipe.info SUFFIX= AUTH_COOKIE=at
	else
		HOST=$NAME.pipeinpipe.info SUFFIX=_$NAME AUTH_COOKIE=at_$NAME
	fi
	PIPE_ROOT=/var/www/$HOST
	TOTAL_ROOT=/var/www/total.$HOST
	DATA=/srv/pipeinpipe/$NAME
	PIPE_DB=pipeinpipe$SUFFIX PIPE_DB_USER=pipeman$SUFFIX
	TOTAL_DB=totalizator$SUFFIX TOTAL_DB_USER=totalizator$SUFFIX
	local upper; upper=$(echo "$NAME" | tr a-z A-Z)
	PIPE_DB_PASS=$(secret "${upper}_PIPE_DB_PASS")
	TOTAL_DB_PASS=$(secret "${upper}_TOTAL_DB_PASS")
	TOTAL_ACCESS_KEY=$(secret "${upper}_TOTAL_ACCESS_KEY")
}

# Value of a secret from $SECRETS, generated on first use
secret() {
	if ! grep -q "^$1=" "$SECRETS"; then
		echo "$1=$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 24)" >> "$SECRETS"
	fi
	grep "^$1=" "$SECRETS" | cut -d= -f2-
}

# render <template> <destination> <owner:group> <mode>: fills in @VARIABLES@
render() {
	local tmp; tmp=$(mktemp)
	sed -e "s|@NAME@|$NAME|g" -e "s|@HOST@|$HOST|g" -e "s|@AUTH_COOKIE@|$AUTH_COOKIE|g" \
		-e "s|@PIPE_ROOT@|$PIPE_ROOT|g" -e "s|@TOTAL_ROOT@|$TOTAL_ROOT|g" -e "s|@DATA@|$DATA|g" \
		-e "s|@PIPE_DB@|$PIPE_DB|g" -e "s|@PIPE_DB_USER@|$PIPE_DB_USER|g" -e "s|@PIPE_DB_PASS@|$PIPE_DB_PASS|g" \
		-e "s|@TOTAL_DB@|$TOTAL_DB|g" -e "s|@TOTAL_DB_USER@|$TOTAL_DB_USER|g" -e "s|@TOTAL_DB_PASS@|$TOTAL_DB_PASS|g" \
		-e "s|@TOTAL_ACCESS_KEY@|$TOTAL_ACCESS_KEY|g" "$1" > "$tmp"
	install -o "${3%:*}" -g "${3#*:}" -m "$4" "$tmp" "$2"
	rm -f "$tmp"
}

# put <source> <destination>: installs a file, returns 1 if it was already identical
put() {
	if cmp -s "$1" "$2"; then return 1; fi
	install -m 0644 "$1" "$2"
}

as_deploy() { sudo -u "$DEPLOY_USER" "$@"; }

# HTTPS for the instance's sites (included by site.conf), once it has a
# certificate from the HTTPS step below
https_snippet() {
	local cert=/etc/letsencrypt/live/pipeinpipe-$NAME
	if [ -s "$cert/fullchain.pem" ]; then
		cat <<-EOF
			listen 443 ssl;
			listen [::]:443 ssl;
			http2 on;
			ssl_certificate $cert/fullchain.pem;
			ssl_certificate_key $cert/privkey.pem;
			include /etc/nginx/snippets/pipeinpipe-tls.conf;
		EOF
	else
		echo "# No certificate for $NAME yet: HTTP only"
	fi > "/etc/nginx/snippets/pipeinpipe-$NAME-https.conf"
}

# Connections from Yandex Cloud to github.com fail now and then; retry them
retry() {
	local i
	for i in 1 2 3 4 5; do
		"$@" && return 0
		echo "  failed, retrying in 5 s: $*" >&2
		sleep 5
	done
	return 1
}

echo "== Packages"
apt-get update -q
DEBIAN_FRONTEND=noninteractive apt-get install -y -q \
	nginx mariadb-server php8.5-fpm php8.5-cli php8.5-mysql php8.5-mbstring php8.5-gd \
	git rsync ufw unattended-upgrades cron logrotate certbot

echo "== Swap"
if ! swapon --show | grep -q /swapfile; then
	[ -f /swapfile ] || { fallocate -l 2G /swapfile; chmod 600 /swapfile; mkswap /swapfile; }
	swapon /swapfile
	grep -q '^/swapfile ' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

echo "== Secrets"
install -d -o root -g www-data -m 0750 /etc/pipeinpipe
[ -f "$SECRETS" ] || install -o root -g root -m 0600 /dev/null "$SECRETS"

echo "== MariaDB"
if put "$HERE/mariadb/99-pipeinpipe.cnf" /etc/mysql/mariadb.conf.d/99-pipeinpipe.cnf; then
	systemctl restart mariadb
fi
for NAME in $INSTANCES; do
	instance_vars "$NAME"
	mariadb <<-SQL
		CREATE DATABASE IF NOT EXISTS \`$PIPE_DB\` CHARACTER SET utf8mb3;
		CREATE DATABASE IF NOT EXISTS \`$TOTAL_DB\` CHARACTER SET utf8mb3;
		CREATE USER IF NOT EXISTS '$PIPE_DB_USER'@'localhost';
		ALTER USER '$PIPE_DB_USER'@'localhost' IDENTIFIED BY '$PIPE_DB_PASS';
		GRANT ALL ON \`$PIPE_DB\`.* TO '$PIPE_DB_USER'@'localhost';
		CREATE USER IF NOT EXISTS '$TOTAL_DB_USER'@'localhost';
		ALTER USER '$TOTAL_DB_USER'@'localhost' IDENTIFIED BY '$TOTAL_DB_PASS';
		GRANT ALL ON \`$TOTAL_DB\`.* TO '$TOTAL_DB_USER'@'localhost';
	SQL
done

echo "== PHP"
put "$HERE/php/99-pipeinpipe.ini" /etc/php/8.5/fpm/conf.d/99-pipeinpipe.ini || true
put "$HERE/php/99-pipeinpipe.ini" /etc/php/8.5/cli/conf.d/99-pipeinpipe.ini || true
put "$HERE/php/www.conf" /etc/php/8.5/fpm/pool.d/www.conf || true
# Let running requests finish (up to 30 s) on reload instead of cutting them off
sed -i -E 's/^;?process_control_timeout = .*/process_control_timeout = 30s/' /etc/php/8.5/fpm/php-fpm.conf
php-fpm8.5 -t
systemctl reload php8.5-fpm

echo "== Sites"
install -d -m 0755 /var/www/letsencrypt
for NAME in $INSTANCES; do
	instance_vars "$NAME"
	echo "-- $NAME: $HOST"

	install -d -o "$DEPLOY_USER" -g "$DEPLOY_USER" -m 0755 /srv/pipeinpipe "$DATA" "$DATA/content" "$DATA/temp"
	for d in users competitions pipemen leagues photos videos download; do
		install -d -o "$DEPLOY_USER" -g "$DEPLOY_USER" -m 0755 "$DATA/content/$d"
	done
	# PHP can write the page cache, the crons' locks and the logs (main/logs);
	# the rest of temp/ stays read-only, as on the old server
	install -d -o www-data -g www-data -m 0755 "$DATA/temp/cache" "$DATA/temp/locks" "$DATA/logs"

	for pair in "$PIPE_ROOT $PIPE_REPO $PIPE_BRANCH" "$TOTAL_ROOT $TOTAL_REPO $TOTAL_BRANCH"; do
		set -- $pair
		if [ ! -d "$1/.git" ]; then
			install -d -o "$DEPLOY_USER" -g "$DEPLOY_USER" -m 0755 "$1"
			retry as_deploy git clone -q -b "$3" "$2" "$1"
		fi
	done

	install -d -o root -g www-data -m 0750 "/etc/pipeinpipe/$NAME"
	render "$HERE/templates/my-properties.php" "/etc/pipeinpipe/$NAME/my-properties.php" root:www-data 0640
	render "$HERE/templates/auth-local-properties.php" "/etc/pipeinpipe/$NAME/auth-local-properties.php" root:www-data 0640
	render "$HERE/templates/total-local-properties.php" "/etc/pipeinpipe/$NAME/total-local-properties.php" root:www-data 0640

	# The git-ignored paths the code expects, as on the old server
	as_deploy ln -sfn "/etc/pipeinpipe/$NAME/my-properties.php" "$PIPE_ROOT/main/config/my-properties.php"
	as_deploy ln -sfn "/etc/pipeinpipe/$NAME/auth-local-properties.php" "$PIPE_ROOT/auth/properties/local-properties.php"
	as_deploy ln -sfn "/etc/pipeinpipe/$NAME/total-local-properties.php" "$TOTAL_ROOT/lib/local-properties.php"
	for d in users competitions pipemen leagues; do
		as_deploy ln -sfn "$DATA/content/$d" "$PIPE_ROOT/main/images/$d"
	done
	as_deploy mkdir -p "$PIPE_ROOT/main/content"
	for d in photos videos download; do
		as_deploy ln -sfn "$DATA/content/$d" "$PIPE_ROOT/main/content/$d"
	done
	as_deploy ln -sfn "$DATA/temp" "$PIPE_ROOT/main/temp"
	as_deploy ln -sfn "$DATA/logs" "$PIPE_ROOT/main/logs"
	# ?VERSION on CSS and JS links; deploy.sh updates it
	[ -s "$DATA/version" ] || as_deploy git -C "$PIPE_ROOT" rev-parse --short HEAD > "$DATA/version"

	if [ "$NAME" = prod ]; then
		echo "# prod: open to everyone" > "/etc/nginx/snippets/pipeinpipe-$NAME-access.conf"
	else
		htpasswd=$(mktemp)
		printf 'pipe:%s\n' "$(openssl passwd -apr1 "$(secret "$(echo "$NAME" | tr a-z A-Z)_BASIC_AUTH_PASS")")" > "$htpasswd"
		install -o root -g www-data -m 0640 "$htpasswd" "/etc/nginx/pipeinpipe-$NAME.htpasswd"
		rm -f "$htpasswd"
		cat > "/etc/nginx/snippets/pipeinpipe-$NAME-access.conf" <<-EOF
			auth_basic "pipeinpipe $NAME";
			auth_basic_user_file /etc/nginx/pipeinpipe-$NAME.htpasswd;
			add_header X-Robots-Tag "noindex, nofollow" always;
		EOF
	fi
	https_snippet
	render "$HERE/nginx/site.conf" "/etc/nginx/sites-available/pipeinpipe-$NAME.conf" root:root 0644
	ln -sfn "/etc/nginx/sites-available/pipeinpipe-$NAME.conf" "/etc/nginx/sites-enabled/pipeinpipe-$NAME.conf"

	# cron ignores files in /etc/cron.d that aren't regular, root-owned files
	# (the old server's symlinks into the repo never ran)
	render "$HERE/cron/site.cron" "/etc/cron.d/pipeinpipe-$NAME" root:root 0644
done

put "$HERE/logrotate/pipeinpipe" /etc/logrotate.d/pipeinpipe || true

put "$HERE/nginx/common.conf" /etc/nginx/snippets/pipeinpipe-common.conf || true
put "$HERE/nginx/tls.conf" /etc/nginx/snippets/pipeinpipe-tls.conf || true
put "$HERE/nginx/default.conf" /etc/nginx/sites-available/pipeinpipe-default.conf || true
ln -sfn /etc/nginx/sites-available/pipeinpipe-default.conf /etc/nginx/sites-enabled/pipeinpipe-default.conf
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

echo "== HTTPS"
# certbot.timer renews the certificates; nginx has to reload to use them
install -d /etc/letsencrypt/renewal-hooks/deploy
printf '#!/bin/sh\nsystemctl reload nginx\n' > /etc/letsencrypt/renewal-hooks/deploy/reload-nginx
chmod 0755 /etc/letsencrypt/renewal-hooks/deploy/reload-nginx
for NAME in $INSTANCES; do
	instance_vars "$NAME"
	domains="$HOST,m.$HOST,cupms.$HOST,total.$HOST"
	[ "$NAME" = prod ] && domains="$domains,www.$HOST"
	if [ -n "$CERTBOT_ARGS" ] || [ ! -s "/etc/letsencrypt/live/pipeinpipe-$NAME/fullchain.pem" ]; then
		# Let's Encrypt fetches a token over HTTP from /var/www/letsencrypt (common.conf)
		certbot certonly --webroot -w /var/www/letsencrypt --cert-name "pipeinpipe-$NAME" -d "$domains" \
			--email "$LETSENCRYPT_EMAIL" --agree-tos --no-eff-email --non-interactive $CERTBOT_ARGS \
			|| echo "  no certificate for $NAME: do all of $domains point here?"
	fi
	https_snippet
done
nginx -t
systemctl reload nginx

echo "== Deploy scripts"
# Copies, not the checkouts' files: GitHub Actions' deploy key runs them as root
install -m 0755 "$HERE/deploy.sh" /usr/local/sbin/pipeinpipe-deploy
install -m 0755 "$HERE/ci-deploy.sh" /usr/local/sbin/pipeinpipe-ci-deploy

echo "== Firewall"
ufw allow OpenSSH >/dev/null
ufw allow 80/tcp >/dev/null
ufw allow 443/tcp >/dev/null
ufw --force enable >/dev/null
ufw status | head -1

echo "== Done. Secrets: $SECRETS (root only); dev basic auth user: pipe"
