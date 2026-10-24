#!/bin/bash
# Requests every URL in smoke-urls.txt from PHP 5.5 (:8055) and PHP 8.5 (:8085)
# and compares them. Page caches and PHP error logs are cleared first so every
# run starts cold. Bodies are kept in data/smoke/<port>/ for closer diffing.
#
# Lines marked "auth" are requested signed in as SMOKE_LOGIN (default ortemij,
# with the password set by scripts/dev-password.sh).
set -euo pipefail
cd "$(dirname "$0")/.."

URLS=${1:-smoke-urls.txt}
OUT=data/smoke
rm -rf "$OUT"
mkdir -p "$OUT/8055" "$OUT/8085"

for v in php55 php85; do
	rm -rf "data/$v/temp/cache" "data/$v/temp/locks"
	# Truncate inside the container: truncated from the host, PHP keeps writing
	# at its old offset and the log starts with a block of NUL bytes
	docker compose exec -T "$v" sh -c ': > /var/log/php/error.log'
done

# The auth token is tied to the user agent, so every request sends the same one.
# Sign in once on PHP 5.5; the token lives in the shared DB, so it works on both ports.
UA="pipeinpipe-dev-smoke"
JAR="$OUT/cookies.txt"
curl -s -o /dev/null -A "$UA" -c "$JAR" -H "Host: pipeinpipe.localhost:8055" \
	--data-urlencode "method=sign_in" --data-urlencode "remember=yes" \
	--data-urlencode "login=${SMOKE_LOGIN:-ortemij}" --data-urlencode "password=${SMOKE_PASSWORD:-dev}" \
	http://127.0.0.1:8055/authorize.php
grep -q '[[:space:]]at[[:space:]]' "$JAR" \
	|| echo "WARNING: sign-in failed, \"auth\" pages will redirect (run scripts/dev-password.sh?)" >&2

# Pages embed the port in links, so it is masked before comparing bodies
normalize() { sed -E 's/:80(55|85)/:PORT/g' "$1"; }

printf '%-63s %-13s %-13s %s\n' URL 'PHP 5.5' 'PHP 8.5' 'diff'
grep -vE '^[[:space:]]*(#|$)' "$URLS" | while read -r host path auth; do
	slug=$(printf '%s%s%s' "$host" "$path" "${auth:+_auth}" | tr -c 'A-Za-z0-9._-' '_')
	cookies=()
	[ -n "$auth" ] && cookies=(-b "$JAR")
	for port in 8055 8085; do
		# /life/people renders for ~35 s on the emulated PHP 5.5
		curl -s -o "$OUT/$port/$slug.html" -w '%{http_code} %{size_download}' --max-time 180 \
			-A "$UA" ${cookies[@]+"${cookies[@]}"} \
			-H "Host: $host:$port" "http://127.0.0.1:$port$path" > "$OUT/$port/$slug.status" \
			|| true
	done
	changed=$(diff <(normalize "$OUT/8055/$slug.html") <(normalize "$OUT/8085/$slug.html") | grep -c '^[<>]' || true)
	printf '%-63s %-13s %-13s %s\n' "$host$path${auth:+ (auth)}" "$(cat "$OUT/8055/$slug.status")" \
		"$(cat "$OUT/8085/$slug.status")" "$changed lines"
done

for v in php55 php85; do
	echo
	echo "== $v: PHP errors logged during the run (by count)"
	grep -a '^\[' "data/$v/log/error.log" | sed -E 's/^\[[^]]*\] //' | sort | uniq -c | sort -rn | head -30 || true
done
