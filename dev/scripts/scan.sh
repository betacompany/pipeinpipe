#!/bin/sh
# Static checks of both repos against PHP 8.5, written to data/scan/:
#   <repo>-lint.txt        php -l output for files that don't parse cleanly
#   <repo>-phpcompat.json  PHPCompatibility findings (testVersion 5.5-8.5, so
#                          fixes must also keep working on the prod PHP 5.5)
#   lint55.txt             files that don't parse on PHP 5.5 (php55 container)
# Then prints a summary (scripts/scan-summary.py).
set -eu
cd "$(dirname "$0")/.."
mkdir -p data/scan

docker compose --profile tools build tools
docker compose --profile tools run --rm -T tools sh -s <<'EOF'
set -u
php -v | head -1
composer global show phpcompatibility/php-compatibility 2>/dev/null | grep -E '^(versions|source)'

for repo in pipeinpipe totalizator; do
	src=/src/$repo
	echo "== $repo: php -l"
	# "exit 0": xargs stops starting new jobs after one exits with 255
	find "$src" -name '*.php' -not -path '*/.git/*' -not -path '*/dev/data/*' -print0 \
		| xargs -0 -n1 -P8 sh -c 'php -l "$0" 2>&1; exit 0' \
		| grep -vE '^(No syntax errors detected|Errors parsing|$)' \
		| sed "s|$src/||g" > /out/$repo-lint.txt || true
	wc -l < /out/$repo-lint.txt

	echo "== $repo: PHPCompatibility"
	phpcs "$src" --standard=PHPCompatibility --runtime-set testVersion 5.5-8.5 \
		--extensions=php --ignore='*/dev/data/*' --parallel=8 -q \
		--basepath="$src" --report=json --report-file=/out/$repo-phpcompat.json || true
done
EOF

# The code also has to keep running on prod's PHP 5.5 until the cutover
echo "== PHP 5.5 lint (needs the stack running)"
docker compose exec -T php55 sh -s <<'EOF' > data/scan/lint55.txt
for src in /var/www/pipeinpipe.info /var/www/total.pipeinpipe.info; do
	find "$src" -name '*.php' -not -path '*/.git/*' -not -path '*/dev/*' -print0 \
		| xargs -0 -n1 -P8 sh -c 'php -d display_errors=stderr -l "$0" 2>&1 >/dev/null; exit 0' \
		| grep -E 'error' | sed "s|$src/||"
done
EOF
wc -l < data/scan/lint55.txt

python3 scripts/scan-summary.py data/scan
