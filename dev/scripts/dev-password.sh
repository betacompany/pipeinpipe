#!/bin/sh
# Sets the password of one account in the dev database copy, so you can sign in
# through the normal login form. Never touches prod. Re-run after import-db.sh.
#
#   scripts/dev-password.sh [login] [password]
#
# Defaults to ortemij (id 1, which also opens totalizator's cpanel.php) / dev.
set -eu
cd "$(dirname "$0")/.."

LOGIN=${1:-ortemij}
PASSWORD=${2:-dev}

# common-auth stores unsalted md5(password)
docker compose exec -T mariadb mariadb -uroot -pdev pipeinpipe -e "
	UPDATE common_user SET hash = MD5('$PASSWORD') WHERE login = '$LOGIN';
	SELECT id, login, name, surname FROM common_user WHERE login = '$LOGIN';"
