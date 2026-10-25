#!/bin/bash
# What GitHub Actions' deploy key may run on the server: its line in
# ~ortemij/.ssh/authorized_keys forces this command (see server/README.md),
# so the key can deploy and do nothing else.
#
# The deploy workflow (.github/workflows/deploy.yml) sends "<prod|dev> <branch>
# <commit>" as the command and a tar.gz of css/all.css and js/all.js on stdin.
#
# provision.sh installs this script as /usr/local/sbin/pipeinpipe-ci-deploy.
set -euo pipefail

read -r name branch commit extra <<< "${SSH_ORIGINAL_COMMAND:-}"
if ! [[ $name =~ ^(prod|dev)$ && $branch =~ ^[A-Za-z0-9][A-Za-z0-9._/-]*$ && $branch != *..* &&
	$commit =~ ^[0-9a-f]{40}$ && -z $extra ]]; then
	echo "expected: prod|dev <branch> <commit>, got: ${SSH_ORIGINAL_COMMAND:-nothing}" >&2
	exit 2
fi

assets=$(mktemp -d)
trap 'rm -rf "$assets"' EXIT
# Only these two names, so the archive can't write anywhere else
tar -xzf - -C "$assets" css/all.css js/all.js
sudo /usr/local/sbin/pipeinpipe-deploy "$name" "$branch" "$commit" "$assets"
