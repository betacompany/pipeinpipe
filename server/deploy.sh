#!/bin/bash
# Updates the code of one instance and reloads PHP (running requests get up to
# 30 s to finish).
#
#   sudo server/deploy.sh prod|dev
#       pulls the checked-out branches of pipeinpipe and totalizator
#   sudo server/deploy.sh prod|dev <branch> <commit> <assets dir>
#       what the GitHub Actions deploy runs, through server/ci-deploy.sh:
#       checks out <commit> of pipeinpipe's <branch>, installs the all.css and
#       all.js built from it, and pulls totalizator
#
# provision.sh installs this script as /usr/local/sbin/pipeinpipe-deploy.
set -euo pipefail

NAME=${1:-} BRANCH=${2:-} COMMIT=${3:-} ASSETS=${4:-}
case $NAME in
	prod) HOST=pipeinpipe.info ;;
	dev) HOST=dev.pipeinpipe.info ;;
	*) echo "usage: $0 prod|dev [<branch> <commit> <assets dir>]" >&2; exit 2 ;;
esac
DEPLOY_USER=${DEPLOY_USER:-ortemij}
PIPE_ROOT=/var/www/$HOST
TOTAL_ROOT=/var/www/total.$HOST
DATA=/srv/pipeinpipe/$NAME

as_deploy() { sudo -u "$DEPLOY_USER" "$@"; }

# Connections from Yandex Cloud to github.com fail now and then
retry() {
	local i
	for i in 1 2 3 4 5; do
		"$@" && return 0
		[ "$i" = 5 ] && return 1
		echo "  failed, retrying in 5 s" >&2
		sleep 5
	done
}

if [ -n "$ASSETS" ]; then
	for f in css/all.css js/all.js; do
		# A failed build leaves an empty or stub file; the real ones are 18 and 38 KB
		[ "$(stat -c %s "$ASSETS/$f")" -gt 10000 ] || { echo "$f from the build looks broken" >&2; exit 1; }
	done
fi

if [ -n "$BRANCH" ]; then
	retry as_deploy git -C "$PIPE_ROOT" fetch -q origin "+refs/heads/$BRANCH:refs/remotes/origin/$BRANCH"
	if ! as_deploy git -C "$PIPE_ROOT" merge-base --is-ancestor "$COMMIT" "origin/$BRANCH" 2>/dev/null; then
		echo "$COMMIT isn't on $BRANCH" >&2
		exit 1
	fi
	as_deploy git -C "$PIPE_ROOT" checkout -q -B "$BRANCH" "$COMMIT"
	as_deploy git -C "$PIPE_ROOT" branch -q -u "origin/$BRANCH"
else
	retry as_deploy git -C "$PIPE_ROOT" pull -q --ff-only
fi
retry as_deploy git -C "$TOTAL_ROOT" pull -q --ff-only

if [ -n "$ASSETS" ]; then
	for f in css/all.css js/all.js; do
		cmp -s "$ASSETS/$f" "$PIPE_ROOT/main/$f" ||
			install -o "$DEPLOY_USER" -g "$DEPLOY_USER" -m 0644 "$ASSETS/$f" "$PIPE_ROOT/main/$f"
	done
fi

# The ?VERSION the pages put on CSS and JS links (my-properties.php reads it),
# so browsers fetch them again after a deploy
as_deploy git -C "$PIPE_ROOT" rev-parse --short HEAD > "$DATA/version"

for dir in "$PIPE_ROOT" "$TOTAL_ROOT"; do
	echo "$dir: $(as_deploy git -C "$dir" log -1 --format='%h %s')"
done
systemctl reload php8.5-fpm
