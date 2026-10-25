# Local dev stand

Runs pipeinpipe.info and total.pipeinpipe.info in Docker on a copy of the prod
data, with PHP 8.5, MariaDB and nginx set up as on the server (see
[server/](../server/README.md)):

http://pipeinpipe.localhost:8085, and `m.`, `cupms.` and `total.` in front of
it for the other sites.

## Setup

```sh
cp .env.example .env              # point TOTALIZATOR_DIR at your checkout
scripts/fetch-prod-data.sh        # DB dump + uploaded content from the server (~350 MB)
docker compose up -d --build
scripts/import-db.sh
scripts/dev-password.sh           # lets you sign in (see below)
```

`fetch-prod-data.sh` connects to `ortemij@158.160.188.92` (set `PROD_HOST` for
another account) and needs passwordless `sudo` there for the database dump.

`data/` holds the prod copy (including users' emails and password hashes) and
is git-ignored. Re-run `fetch-prod-data.sh` and `import-db.sh` to refresh it.

## In the browser

Open http://total.pipeinpipe.localhost:8085 or http://pipeinpipe.localhost:8085.
`*.localhost` resolves to your machine without any `/etc/hosts` changes, in
Safari too.

To sign in, give an account a known password in the dev copy:

```sh
scripts/dev-password.sh            # ortemij / dev; see the script for other accounts
```

Then use the normal login form. One sign-in covers every site, because the
cookie is set for `.pipeinpipe.localhost` and the token is stored in the shared
DB. `ortemij` (id 1) also opens totalizator's `cpanel.php`. Re-run the script
after every `import-db.sh`.

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
- `/`, `/sport`, `/sport/statistics` and `/sport/pipemen` take about 30 s
  each, every time. Each logs thousands of PHP warnings, and every line
  written to `data/php85/log` through Docker Desktop's file sharing takes a
  few milliseconds. The same page renders in under a second otherwise.

## Checks

- `scripts/smoke.sh` requests every page in `smoke-urls.txt` and prints status
  codes, sizes and times, then the PHP errors and warnings logged during the
  run. Lines marked `auth` are requested signed in, which needs
  `dev-password.sh`. It fails if a page returns another status than expected
  (200 unless its line says otherwise) or PHP logs a fatal error. It starts
  from a cold page cache and takes under 3 minutes.
- `scripts/scan.sh` runs `php -l` and PHPCompatibility (against PHP 8.5) over
  both repos.

Current results and the fix list: [PHP85-FINDINGS.md](PHP85-FINDINGS.md).

PHP error log: `data/php85/log/error.log`.
MariaDB from the host: `mysql -h127.0.0.1 -P3307 -uroot -pdev`.

## How it mirrors prod

- nginx vhosts follow the server's (`server/nginx/site.conf`) and include the
  repo's own `.nginxconf` rewrite rules.
- Code is mounted at the prod paths (`/var/www/pipeinpipe.info`,
  `/var/www/total.pipeinpipe.info`). Config files that are git-ignored in the
  repos come from `config/` (stand-ins for `server/templates/`); uploaded
  content from `data/content/`.
- PHP starts from PHP's production `php.ini`, as Ubuntu's `php8.5-fpm` does,
  with the server's settings on top (`php/php.ini`). Unlike the server, it logs
  every error level to its own file instead of the nginx error logs.
- PHP reaches MariaDB as `localhost` through a shared socket, as on prod.
  totalizator hardcodes `localhost`.
- MariaDB runs with an empty `sql_mode` and utf8mb3, as on the server.
- The page cache (`main/temp`) and `main/logs` live in `data/php85/`.

Single files are bind-mounted: `nginx/default.conf`, the files in `config/`,
and the empty placeholders Docker creates for them in the repos (git-ignored,
e.g. totalizator's `lib/local-properties.php`). An editor or tool that saves by
replacing the file, such as `perl -pi`, leaves the container on the old copy
(nginx then can't find its config, PHP fails with undefined `DB_*`
constants). Run `docker compose up -d --force-recreate php85 nginx` after
changing them.
