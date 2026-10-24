# Local dev stand

Runs pipeinpipe.info and total.pipeinpipe.info in Docker on a copy of the prod
data, side by side on two PHP versions:

| URL | PHP |
| --- | --- |
| http://pipeinpipe.localhost:8085 | 8.5, the upgrade target |
| http://pipeinpipe.localhost:8055 | 5.5, the exact `php5-fpm` package prod runs (Ubuntu 14.04, amd64) |

`m.`, `cupms.` and `total.pipeinpipe.localhost` work on both ports. Both PHP
containers run the same working tree against the same MariaDB, so any
difference between the ports comes from the PHP version.

## Setup

```sh
cp .env.example .env              # point TOTALIZATOR_DIR at your checkout
scripts/fetch-prod-data.sh        # DB dump + uploaded content from the server (~350 MB)
docker compose up -d --build
scripts/import-db.sh
scripts/dev-password.sh           # lets you sign in (see below)
```

`data/` holds the prod copy (including users' emails and password hashes) and
is git-ignored. Re-run `fetch-prod-data.sh` and `import-db.sh` to refresh it.

## In the browser

Open http://total.pipeinpipe.localhost:8055 or http://pipeinpipe.localhost:8055
(or `:8085` for PHP 8.5). `*.localhost` resolves to your machine without any
`/etc/hosts` changes, in Safari too.

To sign in, give an account a known password in the dev copy:

```sh
scripts/dev-password.sh            # ortemij / dev; see the script for other accounts
```

Then use the normal login form. One sign-in covers every site and both ports,
because the cookie is set for `.pipeinpipe.localhost` and the token is stored in
the shared DB. `ortemij` (id 1) also opens totalizator's `cpanel.php`. Re-run
the script after every `import-db.sh`.

Watch out for:

- Some links are hardcoded to prod and take you to the real site: the "CupMS"
  link in the main site's footer, the pipeinpipe.info links in cupms, and
  totalizator's "Pipeinpipe.info" menu item and its `ret=` return address after
  sign-up.
- The sign-in token is tied to the browser's user agent, so switching to phone
  emulation in devtools signs you out. Sign in again on the mobile form; prod
  behaves the same way.
- All three sites load Yandex.Metrika, so pages you open in a browser count as
  visits in the real counter. `smoke.sh` uses curl and runs no JavaScript.
- Anything you click (stakes, admin actions, posts) writes to the dev DB only.
  `import-db.sh` resets it.

## Checks

- `scripts/smoke.sh` requests every page in `smoke-urls.txt` on both ports and
  prints status codes, body differences and the PHP errors each version logged.
  Lines marked `auth` are requested signed in, which needs `dev-password.sh`.
  It takes about 5 minutes, mostly because PHP 5.5 runs emulated on Apple
  Silicon and `/life/people` is a 12 MB page.
- `scripts/scan.sh` runs `php -l` (PHP 8.5) and PHPCompatibility over both
  repos. It checks against `5.5-`, so a fix that only works on PHP 8 is
  flagged too. That keeps the code deployable to the current server until the
  cutover.

Current results and the fix list: [PHP85-FINDINGS.md](PHP85-FINDINGS.md).

PHP error logs: `data/php55/log/error.log`, `data/php85/log/error.log`.
MariaDB from the host: `mysql -h127.0.0.1 -P3307 -uroot -pdev`.

## How it mirrors prod

- nginx vhosts are copied from `~/pipeinpipe.info.conf.d` on the server and
  include the repo's own `.nginxconf` rewrite rules.
- Code is mounted at the prod paths (`/var/www/pipeinpipe.info`,
  `/var/www/total.pipeinpipe.info`). Config files that are git-ignored in the
  repos come from `config/`; uploaded content from `data/content/`.
- PHP reaches MariaDB as `localhost` through a shared socket, as on prod.
  totalizator hardcodes `localhost`.
- MariaDB runs with an empty `sql_mode` and utf8mb3, like the old MySQL 5.5.
- Each PHP version has its own `main/temp` (page cache) and `main/logs`, so
  one version never serves pages cached by the other.

Single files are bind-mounted: `nginx/default.conf`, the files in `config/`,
and the empty placeholders Docker creates for them in the repos (git-ignored,
e.g. totalizator's `lib/local-properties.php`). An editor or tool that saves by
replacing the file, such as `perl -pi`, leaves the container on the old copy
(nginx then can't find its config, PHP fails with undefined `DB_*`
constants). Run `docker compose up -d --force-recreate php55 php85 nginx`
after changing them.
