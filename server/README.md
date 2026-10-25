# Server

Sets up the Ubuntu 26.04 server for pipeinpipe.info and total.pipeinpipe.info:
nginx, PHP 8.5-FPM and MariaDB, with two instances of the sites.

| Instance | Hostnames | Code | Databases |
| --- | --- | --- | --- |
| prod | `pipeinpipe.info`, `m.`, `cupms.`, `total.` (and `www.` → redirect) | `/var/www/pipeinpipe.info`, `/var/www/total.pipeinpipe.info` | `pipeinpipe`, `totalizator` |
| dev | `dev.pipeinpipe.info`, `m.dev.`, `cupms.dev.`, `total.dev.` | `/var/www/dev.pipeinpipe.info`, `/var/www/total.dev.pipeinpipe.info` | `pipeinpipe_dev`, `totalizator_dev` |

Each instance has its own config in `/etc/pipeinpipe/<instance>/` (symlinked
into the checkouts at the git-ignored paths the code expects), uploaded content
in `/srv/pipeinpipe/<instance>/content` and a page cache in
`/srv/pipeinpipe/<instance>/temp/cache` (the only part of `temp/` PHP can write).

dev is behind HTTP basic auth (user `pipe`), tells search engines not to index it
and uses its own login cookie (`at_dev`), so prod logins don't carry over.

Secrets (database passwords, totalizator's `ACCESS_KEY`, the dev basic auth
password) are generated on first run into `/etc/pipeinpipe/secrets.env`, readable
by root only:

```sh
sudo grep DEV_BASIC_AUTH_PASS /etc/pipeinpipe/secrets.env
```

## Setting up a server

As a user with sudo (`ortemij`), from a copy of this directory:

```sh
# prod's totalizator ACCESS_KEY is shared with the external caller of api_proc.php,
# so it has to be the old one, not a generated one
echo 'PROD_TOTAL_ACCESS_KEY=...' | sudo tee -a /etc/pipeinpipe/secrets.env

sudo ./provision.sh        # safe to re-run
```

Then load the data. Databases, one at a time, from a single-database dump:

```sh
ssh OLD 'sudo mysqldump --defaults-file=/etc/mysql/debian.cnf --default-character-set=utf8 \
    --quick --routines --triggers pipeinpipe | gzip' | ssh NEW 'sudo ./import-db.sh pipeinpipe'
```

Uploaded content goes to `/srv/pipeinpipe/<instance>/content/` (rsync). Pages
would make missing avatar miniatures on the fly, but PHP can't write to the
content, so make them once after copying it (it skips the ones that exist):

```sh
sudo -u ortemij php /var/www/pipeinpipe.info/server/make-miniatures.php /var/www/pipeinpipe.info
sudo -u ortemij php /var/www/dev.pipeinpipe.info/server/make-miniatures.php /var/www/dev.pipeinpipe.info
```

The first deploy from GitHub Actions (below) installs `main/css/all.css` and
`main/js/all.js`, which aren't in git.

## Moving the live sites here

See [CUTOVER.md](CUTOVER.md) and `cutover.sh`.

## Deploying

From GitHub: **Actions → Deploy → Run workflow**, then pick the branch and the
instance. dev takes any branch, prod only `master`. The workflow
(`.github/workflows/deploy.yml`) builds `main/css/all.css` and `main/js/all.js`
with the compilers in the repo, then runs `deploy.sh` on the server with them.
It also builds them on every pull request, so a broken build shows up there.

On the server, without rebuilding the CSS and JS:

```sh
sudo ./deploy.sh prod      # or dev: git pull both checkouts, reload PHP
```

Every deploy writes the pipeinpipe commit to `/srv/pipeinpipe/<instance>/version`.
That is the `?VERSION` the pages put on CSS and JS links, so browsers fetch the
new files.

The workflow signs in as `ortemij` with a key whose line in
`~ortemij/.ssh/authorized_keys` forces `ci-deploy.sh`, so the key can't run
anything else:

```
restrict,command="/usr/local/sbin/pipeinpipe-ci-deploy" ssh-ed25519 AAAA... github-actions-deploy
```

Its private half is the repo's `DEPLOY_SSH_KEY` secret. To replace it:

```sh
ssh-keygen -t ed25519 -N '' -C github-actions-deploy -f deploy_key
gh secret set DEPLOY_SSH_KEY --repo betacompany/pipeinpipe < deploy_key
# swap deploy_key.pub into authorized_keys as above, then delete both files
```

If the server's SSH host key changes, update `HOST_KEY` in the workflow.

## HTTPS

Every site is HTTPS. Plain HTTP redirects to it, except Let's Encrypt's
challenges and totalizator's `api_proc.php` (`nginx/https-redirect.conf`), whose
outside caller may not follow a redirect. HSTS tells browsers to stay on HTTPS
for a day (raise `max-age` in `provision.sh` once that has run fine for a
while), and the login cookies are HTTPS-only (`secure` in
`templates/auth-local-properties.php` and `templates/my-properties.php`).

Each instance has one Let's
Encrypt certificate for all its names (`pipeinpipe-prod`: `pipeinpipe.info`,
`www.`, `m.`, `cupms.`, `total.`; `pipeinpipe-dev`: the same under `dev.`).
`provision.sh` gets a missing one with `certbot certonly --webroot`: Let's
Encrypt fetches a token over HTTP from `/var/www/letsencrypt`, which every site
serves at `/.well-known/acme-challenge/` (`nginx/common.conf`), dev without
its password. Then it writes `/etc/nginx/snippets/pipeinpipe-<instance>-https.conf`,
which adds the HTTPS listener to that instance's sites.

```sh
sudo env CERTBOT_ARGS=--dry-run ./provision.sh   # tries the certificates against the staging servers
sudo certbot certificates                        # what there is and when it expires
```

`certbot.timer` renews them; `/etc/letsencrypt/renewal-hooks/deploy/reload-nginx`
reloads nginx afterwards. Expiry warnings go to `LETSENCRYPT_EMAIL` in
`provision.sh`. Unknown names on 443 get no certificate (`ssl_reject_handshake`).

## Crons

`/etc/cron.d/pipeinpipe-<instance>`, from `cron/site.cron`, run as `www-data`:

| When | What |
| --- | --- |
| daily, 00:00 UTC | `main/cron/cron_sport_rating.php`: recomputes the ratings of the last and next 5 days (about 2 s) |
| every 5 minutes | `main/cron/cron_crossposting.php`: publishes collected VK/Twitter posts; nothing has been collected since 2013, so it's a no-op |

Their output goes to `/srv/pipeinpipe/<instance>/logs/cron.log`; the scripts'
own logs (`cross_posting.log`) and the app's `custom.log` sit next to it,
rotated weekly by `/etc/logrotate.d/pipeinpipe`.

## Notes

- PHP errors go to the per-site nginx error logs in `/var/log/nginx/`, as on
  the old server. Failed SQL queries are logged there too (`db_qw: ...`).
- MariaDB runs without strict mode and with utf8mb3 tables, like the old MySQL
  5.5. Queries that relied on MySQL 5 specifics were rewritten; see
  `dev/PHP85-FINDINGS.md`.
- Connections from Yandex Cloud to github.com fail now and then; the scripts
  retry them.
