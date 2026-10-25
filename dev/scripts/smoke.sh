#!/bin/bash
# Requests every URL in smoke-urls.txt from the stand (:8085) and prints status,
# size and time, then the PHP errors and warnings logged during the run. The
# page cache and the PHP error log are cleared first so every run starts cold.
# Bodies are kept in data/smoke/ for a closer look.
#
# Lines marked "auth" are requested signed in as SMOKE_LOGIN (default ortemij,
# with the password set by scripts/dev-password.sh). Every URL should return
# 200 unless its line gives another status.
#
# Exits with 1 if a URL returns another status or PHP logged a fatal error.
set -euo pipefail
cd "$(dirname "$0")/.."
export LC_ALL=C

URLS=${1:-smoke-urls.txt}
OUT=data/smoke
LOG=data/php85/log/error.log
rm -rf "$OUT"
mkdir -p "$OUT"

rm -rf data/php85/temp/cache data/php85/temp/locks
# Truncate inside the container: truncated from the host, PHP keeps writing at
# its old offset and the log starts with a block of NUL bytes
docker compose exec -T php85 sh -c ': > /var/log/php/error.log'

# The auth token is tied to the user agent, so every request sends the same one
UA="pipeinpipe-dev-smoke"
JAR="$OUT/cookies.txt"
curl -s -o /dev/null -A "$UA" -c "$JAR" -H "Host: pipeinpipe.localhost:8085" \
	--data-urlencode "method=sign_in" --data-urlencode "remember=yes" \
	--data-urlencode "login=${SMOKE_LOGIN:-ortemij}" --data-urlencode "password=${SMOKE_PASSWORD:-dev}" \
	http://127.0.0.1:8085/authorize.php
grep -q '[[:space:]]at[[:space:]]' "$JAR" \
	|| echo "WARNING: sign-in failed, \"auth\" pages will redirect (run scripts/dev-password.sh?)" >&2

urls=0
unexpected=0
printf '%-70s %-6s %-9s %s\n' URL status size time
while read -r host path opts; do
	auth=
	want=200
	for opt in $opts; do
		case $opt in
			auth) auth=1 ;;
			[1-5][0-9][0-9]) want=$opt ;;
		esac
	done
	cookies=()
	[ -n "$auth" ] && cookies=(-b "$JAR")
	slug=$(printf '%s%s%s' "$host" "$path" "${auth:+_auth}" | tr -c 'A-Za-z0-9._-' '_')
	read -r status size secs < <(curl -s -o "$OUT/$slug.html" -w '%{http_code} %{size_download} %{time_total}\n' \
		--max-time 150 -A "$UA" ${cookies[@]+"${cookies[@]}"} \
		-H "Host: $host:8085" "http://127.0.0.1:8085$path" </dev/null || true)
	note=
	if [ "$status" != "$want" ]; then
		note="  !! expected $want"
		unexpected=$((unexpected + 1))
	fi
	urls=$((urls + 1))
	printf '%-70s %-6s %-9s %5.2fs%s\n' "$host$path${auth:+ (auth)}" "$status" "$size" "$secs" "$note"
done < <(grep -vE '^[[:space:]]*(#|$)' "$URLS")

# One line per distinct message (stack traces skipped), most frequent first
grep -a '^\[' "$LOG" | sed -E 's/^\[[^]]*\] //' | sort | uniq -c | sort -rn > "$OUT/php-errors.txt" || true
FATAL='^ *[0-9]+ PHP (Fatal|Parse|Recoverable fatal) error'
total=$(awk '{ n += $1 } END { print n + 0 }' "$OUT/php-errors.txt")
fatal=$(awk -v re="$FATAL" '$0 ~ re { n += $1 } END { print n + 0 }' "$OUT/php-errors.txt")
echo
echo "== PHP errors logged during the run: $total, $fatal fatal (all in $OUT/php-errors.txt)"
grep -E "$FATAL" "$OUT/php-errors.txt" | sed 's/^/FATAL /' || true
grep -vE "$FATAL" "$OUT/php-errors.txt" | head -30 | sed 's/^/      /' || true

echo
echo "$urls URLs, $unexpected with an unexpected status; $fatal fatal PHP errors"
if [ "$unexpected" != 0 ] || [ "$fatal" != 0 ]; then
	exit 1
fi
