#!/bin/bash
# Moves the live sites from the old server (Ubuntu 14.04, PHP 5.5) to the new
# one. Runs on a laptop that can SSH to both; the steps around it are in
# server/CUTOVER.md.
#
#   server/cutover.sh check      # checks both servers, puts the (disabled) maintenance page on the old one
#   server/cutover.sh rehearse   # live data -> the new server's dev instance; the live sites stay up
#   server/cutover.sh start      # maintenance page on the old server, live data -> the new prod instance
#   server/cutover.sh abort      # takes the maintenance page down again
#   server/cutover.sh finish     # once the address has moved: checks the sites under their real names
#
# Dumps are kept in dev/data/cutover/, and dev/data/content is a mirror of the
# old server's uploaded content (as for the local Docker stand).
set -euo pipefail

OLD=${OLD:-ortemij@158.160.188.92}
NEW=${NEW:-ortemij@158.160.231.151}
# The new server's temporary address only accepts connections from Russia and
# Kazakhstan, so it's reached through the old one; JUMP= to connect directly
JUMP=${JUMP-$OLD}
LIVE_IP=158.160.188.92
HOSTS="pipeinpipe.info www.pipeinpipe.info m.pipeinpipe.info cupms.pipeinpipe.info total.pipeinpipe.info"

HERE=$(cd "$(dirname "$0")" && pwd)
WORK=${WORK:-$(dirname "$HERE")/dev/data}

old() { ssh -o BatchMode=yes -o ConnectTimeout=15 "$OLD" "$@"; }
new() { ssh -o BatchMode=yes -o ConnectTimeout=15 ${JUMP:+-J "$JUMP"} "$NEW" "$@"; }
step() { echo "== $(date -u +%H:%M:%S) $*"; }
die() { echo "!! $*" >&2; exit 1; }

# The old server's pipeinpipe vhosts; its other sites (betacompany.spb.ru,
# socialstats.ru) don't point at it any more and are left alone
OLD_SITES="000-pipeinpipe.info 001-m.pipeinpipe.info 002-cupms.pipeinpipe.info 100-total.pipeinpipe.info"
MAINTENANCE_CONF='# Installed by server/cutover.sh: answers for the pipeinpipe sites during the cutover
server {
	listen 80;
	listen [::]:80;
	server_name pipeinpipe.info www.pipeinpipe.info xn--80asqc.xn--p1ai m.pipeinpipe.info cupms.pipeinpipe.info total.pipeinpipe.info;
	root /var/www/pipeinpipe-maintenance;
	charset utf-8;
	error_page 503 @maintenance;
	location @maintenance { rewrite ^ /index.html break; }
	location / { return 503; }
}'
MAINTENANCE_HTML='<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>Переезд</title></head>
<body style="font-family: sans-serif; text-align: center; margin-top: 20vh">
<h1>Сайт переезжает на новый сервер</h1>
<p>Вернёмся через несколько минут.</p>
</body>
</html>'

# Writes the maintenance page and its vhost to the old server without enabling
# them, and checks that its nginx accepts the vhost
install_maintenance() {
	old "sudo -n mkdir -p /var/www/pipeinpipe-maintenance"
	old "sudo -n tee /var/www/pipeinpipe-maintenance/index.html >/dev/null" <<< "$MAINTENANCE_HTML"
	old "sudo -n tee /etc/nginx/sites-available/pipeinpipe-maintenance >/dev/null" <<< "$MAINTENANCE_CONF"
	old 'sudo -n bash -s' <<-'EOF'
		t=$(mktemp -d)
		printf 'events {}\nhttp {\n\tinclude /etc/nginx/mime.types;\n\tinclude /etc/nginx/sites-available/pipeinpipe-maintenance;\n}\n' > "$t/nginx.conf"
		nginx -t -c "$t/nginx.conf" -g "pid $t/pid;" > "$t/out" 2>&1
		status=$?
		tail -n 1 "$t/out"
		rm -rf "$t"
		exit "$status"
	EOF
}

maintenance_on() {
	old "sudo -n bash -s -- $OLD_SITES" <<-'EOF'
		set -eu
		cd /etc/nginx
		if [ ! -e sites-enabled/000-pipeinpipe-maintenance ]; then
			mkdir -p sites-paused
			for s in "$@"; do mv "sites-enabled/$s" sites-paused/; done
			ln -s ../sites-available/pipeinpipe-maintenance sites-enabled/000-pipeinpipe-maintenance
		fi
		if ! out=$(nginx -t 2>&1); then
			echo "$out"
			rm sites-enabled/000-pipeinpipe-maintenance
			mv sites-paused/* sites-enabled/ && rmdir sites-paused
			exit 1
		fi
		service nginx reload >/dev/null
		echo "maintenance page on: $(ls sites-paused | tr '\n' ' ')are paused"
	EOF
}

maintenance_off() {
	old 'sudo -n bash -s' <<-'EOF'
		set -eu
		cd /etc/nginx
		rm -f sites-enabled/000-pipeinpipe-maintenance
		if [ -d sites-paused ]; then
			mv sites-paused/* sites-enabled/
			rmdir sites-paused
		fi
		nginx -t 2>&1 | tail -n 1
		service nginx reload >/dev/null
		echo "maintenance page off: $(ls sites-enabled | tr '\n' ' ')"
	EOF
}

# fetch_urls <url>...: fetches each URL with curl "${CURL[@]}" and prints a line
# per URL. Pages must be whole HTML without PHP errors, other files just a 200.
# Returns the number of failures.
fetch_urls() {
	local body url code time verdict failed=0
	body=$(mktemp)
	for url in "$@"; do
		read -r code time < <(curl -s -o "$body" -w '%{http_code} %{time_total}\n' --max-time 150 "${CURL[@]}" "http://$url" || true)
		verdict=ok
		if [ "$code" != 200 ]; then
			verdict=FAILED
		elif ! [[ $url =~ \.(css|js|jpg|png)$ ]] &&
			{ ! tail -c 300 "$body" | grep -q '</html>' || grep -qE 'Fatal error|Parse error|Uncaught' "$body"; }; then
			verdict=FAILED
		fi
		[ "$verdict" = ok ] || failed=$((failed + 1))
		printf '  %-42s %s %9ss  %s\n' "$url" "$code" "$time" "$verdict"
	done
	rm -f "$body"
	return "$failed"
}

# site_urls <host>: what fetch_urls checks for one instance (a cold
# /sport/pipemen takes about a minute)
site_urls() {
	echo "$1/ $1/life $1/forum $1/media/photo $1/sport $1/sport/league $1/sport/rating $1/sport/pipemen" \
		"$1/images/pipemen/1.jpg $1/css/all.css $1/js/all.js m.$1/ cupms.$1/ total.$1/"
}

# check_pages <instance>: site_urls of that instance, fetched on the new server
# from its own nginx (dev's basic auth password stays there)
check_pages() {
	{
		declare -f fetch_urls site_urls
		cat <<-'EOF'
			NAME=$1
			HOST=pipeinpipe.info
			CURL=(-A pipeinpipe-cutover-check --connect-to ::127.0.0.1:80)
			if [ "$NAME" != prod ]; then
				HOST=$NAME.pipeinpipe.info
				netrc=$(mktemp)
				var=$(echo "$NAME" | tr a-z A-Z)_BASIC_AUTH_PASS
				printf 'default login pipe password %s\n' "$(grep "^$var=" /etc/pipeinpipe/secrets.env | cut -d= -f2-)" > "$netrc"
				CURL+=(--netrc-file "$netrc")
			fi
			fetch_urls $(site_urls "$HOST")
			failed=$?
			rm -f "${netrc:-}"
			exit "$failed"
		EOF
	} | new "sudo -n bash -s -- $1"
}

# count_rows <old|new> <db>: "<table> <rows>" for every table of a database
count_rows() {
	local client=mariadb
	[ "$1" = old ] && client="mysql --defaults-file=/etc/mysql/debian.cnf"
	"$1" "sudo -n bash -s -- '$client' $2" <<-'EOF'
		for t in $($1 -N "$2" -e "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'" | cut -f1); do
			echo "$t $($1 -N "$2" -e "SELECT COUNT(*) FROM \`$t\`")"
		done
	EOF
}

# Mirrors the old server's content into $WORK/content, then into <instance>
sync_content() {
	mkdir -p "$WORK/content"
	rsync -az --delete --exclude temp/ "$OLD:pipeinpipe.info.content/" "$WORK/content/"
	rsync -az --delete --exclude temp/ -e "ssh -o BatchMode=yes ${JUMP:+-J $JUMP}" \
		"$WORK/content/" "$NEW:/srv/pipeinpipe/$1/content/"
	echo "  $(du -sh "$WORK/content" | cut -f1) in sync"
}

# copy <instance> <strict>: live databases and content -> that instance. With
# strict, the row counts of the copy must match the old server's (no one can
# write while the maintenance page is on); without, they can drift.
copy() {
	local name=$1 strict=$2 host=pipeinpipe.info suffix= dir db diffs
	if [ "$name" != prod ]; then host=$name.pipeinpipe.info suffix=_$name; fi
	dir=$WORK/cutover/$(date -u +%Y%m%d-%H%M)-$name
	mkdir -p "$dir"

	step "Content -> $name"
	sync_content "$name"

	for db in pipeinpipe totalizator; do
		step "Database $db -> $db$suffix"
		old "sudo -n nice mysqldump --defaults-file=/etc/mysql/debian.cnf --default-character-set=utf8 \
			--quick --routines --triggers $db | gzip -c" > "$dir/$db.sql.gz"
		gunzip -c "$dir/$db.sql.gz" | tail -n 1 | grep -q '^-- Dump completed' || die "$db: the dump is incomplete"
		echo "  dumped: $(du -h "$dir/$db.sql.gz" | cut -f1)"
		new "sudo -n /var/www/$host/server/import-db.sh $db$suffix" < "$dir/$db.sql.gz" | sed 's/^/  imported: /'
		if diffs=$(diff <(count_rows old "$db") <(count_rows new "$db$suffix")); then
			echo "  row counts match"
		elif [ "$strict" = strict ]; then
			echo "$diffs" >&2
			die "$db: row counts differ from the old server's"
		else
			echo "  row counts changed since the dump (the live site is up):"
			echo "$diffs" | grep '^[<>]' | sed 's/^/    /'
		fi
	done

	step "Page cache of $name emptied"
	new "sudo -n find /srv/pipeinpipe/$name/temp/cache -mindepth 1 -delete"

	step "Pages of $name on the new server"
	check_pages "$name" || die "some pages failed"
	echo "  dumps kept in $dir"
}

# check: read-only checks that everything is in place; returns the number of problems
check() {
	local problems=0 h ip line dir branch commit dirty repo tip key_old key_new
	step "Connections"
	old true || die "can't connect to $OLD"
	new true || die "can't connect to $NEW"
	echo "  old and new server: ok"

	step "DNS"
	for h in $HOSTS; do
		ip=$(dig +short "$h" A | tail -n 1)
		if [ "$ip" = "$LIVE_IP" ]; then echo "  $h: $ip"; else echo "  $h: $ip, expected $LIVE_IP"; problems=$((problems + 1)); fi
	done

	step "Code on the new server"
	while read -r line; do
		set -- $line
		dir=$1 branch=$2 commit=$3 dirty=$4
		case $dir in */total.*) repo=totalizator ;; *) repo=pipeinpipe ;; esac
		tip=$(git ls-remote "https://github.com/betacompany/$repo.git" "refs/heads/$branch" | cut -f1)
		if [ "$commit" != "$tip" ]; then
			echo "  $dir: $branch at ${commit:0:7}, but GitHub has ${tip:0:7} (server/deploy.sh)"; problems=$((problems + 1))
		elif [ "$dirty" != 0 ]; then
			echo "  $dir: $dirty uncommitted changes"; problems=$((problems + 1))
		else
			echo "  $dir: $branch at ${commit:0:7}"
		fi
	done < <(new 'for d in /var/www/pipeinpipe.info /var/www/total.pipeinpipe.info /var/www/dev.pipeinpipe.info /var/www/total.dev.pipeinpipe.info; do
		echo "$d $(git -C $d rev-parse --abbrev-ref HEAD) $(git -C $d rev-parse HEAD) $(git -C $d status --porcelain | wc -l)"
	done')
	if ! new 'for h in pipeinpipe.info dev.pipeinpipe.info; do test -s /var/www/$h/main/css/all.css -a -s /var/www/$h/main/js/all.js || exit 1; done'; then
		echo "  all.css or all.js is missing in a checkout (see server/README.md)"; problems=$((problems + 1))
	fi

	step "Totalizator's ACCESS_KEY"
	key_old=$(old 'php -r "include \"/home/ortemij/totalizator/lib/local-properties.php\"; echo ACCESS_KEY;" | sha256sum')
	key_new=$(new 'sudo -n php -r "include \"/etc/pipeinpipe/prod/total-local-properties.php\"; echo ACCESS_KEY;" | sha256sum')
	if [ "$key_old" = "$key_new" ]; then echo "  same on both"; else echo "  differs: set PROD_TOTAL_ACCESS_KEY (server/README.md)"; problems=$((problems + 1)); fi

	step "Disk space on the new server"
	echo "  $(new 'df -BM --output=avail / | tail -n 1') free"

	step "Maintenance page on the old server"
	if ! install_maintenance | sed 's/^/  /'; then
		echo "  its nginx rejects the maintenance vhost"; problems=$((problems + 1))
	fi
	if old 'test -e /etc/nginx/sites-enabled/000-pipeinpipe-maintenance'; then echo "  it's ON"; else echo "  off, ready"; fi

	if [ "$problems" = 0 ]; then step "All checks passed"; else step "$problems problem(s)"; fi
	return "$problems"
}

finish() {
	local h ip headers
	step "DNS"
	for h in $HOSTS; do
		ip=$(dig +short "$h" A | tail -n 1)
		[ "$ip" = "$LIVE_IP" ] || die "$h resolves to $ip, not $LIVE_IP"
	done
	echo "  all at $LIVE_IP"

	step "Which server answers at $LIVE_IP"
	headers=$(curl -sI --max-time 15 http://pipeinpipe.info/ || true)
	[ -n "$headers" ] || die "nothing answers at $LIVE_IP yet"
	if echo "$headers" | grep -qi '^x-powered-by: PHP/5'; then die "still the old server (PHP 5)"; fi
	echo "  the new one ($(echo "$headers" | grep -i '^server:' | tr -d '\r'))"

	step "Pages"
	CURL=(-A pipeinpipe-cutover-check)
	fetch_urls $(site_urls pipeinpipe.info) || die "some pages failed"
	echo "  www.pipeinpipe.info: $(curl -s -o /dev/null -w '%{http_code} -> %{redirect_url}' --max-time 15 http://www.pipeinpipe.info/)"

	step "dev"
	for h in dev.pipeinpipe.info m.dev.pipeinpipe.info cupms.dev.pipeinpipe.info total.dev.pipeinpipe.info; do
		ip=$(dig +short "$h" A | tail -n 1)
		if [ "$ip" = "$LIVE_IP" ]; then
			echo "  $h: $(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "http://$h/") (401 = asks for the password)"
		else
			echo "  $h resolves to $ip: point it at $LIVE_IP"
		fi
	done
	step "Done. Next steps are in server/CUTOVER.md"
}

case ${1:-} in
check)
	check ;;
rehearse)
	copy dev loose ;;
start)
	check || die "fix the problems above first"
	step "Content, ahead of the downtime"
	sync_content prod
	step "Maintenance page on the old server"
	maintenance_on | sed 's/^/  /'
	trap 'echo "!! The old server still shows the maintenance page: re-run server/cutover.sh start, or server/cutover.sh abort" >&2' EXIT
	# Let requests that were already running finish their writes
	sleep 15
	copy prod strict
	trap - EXIT
	cat <<-EOF

		The new server has the live data, and the old one shows the maintenance page.
		Now move the address in the Yandex Cloud console (server/CUTOVER.md), then run
		  server/cutover.sh finish
		To bring the old server back instead:
		  server/cutover.sh abort
	EOF
	;;
abort)
	maintenance_off | sed 's/^/  /' ;;
finish)
	finish ;;
*)
	sed -n '2,10p' "$0" | sed 's/^# \{0,1\}//'
	exit 1 ;;
esac
