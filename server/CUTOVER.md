# Cutover

Moves pipeinpipe.info, `m.`, `cupms.` and `total.` from the old server
(Ubuntu 14.04, PHP 5.5) to the new one by moving the old server's public
address, 158.160.188.92, to the new VM. The live sites' DNS doesn't change.

**Done on 2026-10-25.** The data was copied at 23:34 UTC on the 24th behind the
maintenance page, and the address moved at about 10:20 UTC. The old VM is
stopped. The new server is now at 158.160.188.92, so the script's defaults
(the old server as a jump host) no longer apply.

`server/cutover.sh` does the data side from a laptop that can SSH to both
servers. The console steps are yours. Expect about 10 minutes of downtime: a
couple of minutes for the script, the rest for moving the address.

## Beforehand

1. **Check dev signed in**, see below. The rehearsal copies the live data into
   dev, so it's a recent copy.
2. **Make 158.160.188.92 static** in the Yandex Cloud console (Virtual Private
   Cloud → IP addresses). An ephemeral address is released once it's detached
   from the VM and can't be got back.
3. **Run the checks and a rehearsal:**

   ```sh
   server/cutover.sh check
   server/cutover.sh rehearse   # live data -> dev; the live sites stay up
   ```

   On 2026-10-25 the rehearsal took 1 min 44 s: 4 s for the content (307 MB,
   already mirrored), 32 s for the `pipeinpipe` database, 4 s for
   `totalizator`, and a minute for the cold `/sport/pipemen` page.

Nothing else on the old server needs its address: betacompany.spb.ru and
socialstats.ru resolve elsewhere, and `xn--80asqc.xn--p1ai` has no DNS (as of
2026-10-25).

## The cutover

Avoid 23:45–00:15 UTC, when the new server's rating cron runs.

1. `server/cutover.sh start`. It runs the checks, then:
   - syncs the content ahead of the downtime;
   - switches the old server's four pipeinpipe vhosts to a "moving to a new
     server" page (503), leaving its other sites alone;
   - dumps both databases into `dev/data/cutover/` and imports them into the new
     prod instance;
   - checks that every table has the same row count on both servers;
   - empties the page cache and fetches every site's main pages, warming
     `/sport/pipemen`.

   If it stops, fix the problem and run `start` again, or bring the old server
   back with `server/cutover.sh abort`.
2. **In the Yandex Cloud console:**
   1. Stop the old VM.
   2. Remove the public address from the old VM's network interface.
   3. On the new VM's network interface, remove the temporary address
      (158.160.231.151) and add 158.160.188.92.
3. `server/cutover.sh finish` checks that the sites answer from the new server
   under their real names.
4. **Point `dev.pipeinpipe.info` and `*.dev.pipeinpipe.info` at
   158.160.188.92.** They point at the temporary address, which is gone now.
5. **SSH** to 158.160.188.92 now reaches the new server, which has a different
   host key:

   ```sh
   ssh-keygen -R 158.160.188.92
   ssh ortemij@158.160.188.92
   ```

## Rolling back

- **Before the address moves:** `server/cutover.sh abort`.
- **After:** move 158.160.188.92 back to the old VM, start it, and run
  `server/cutover.sh abort`. Anything written on the new server in between is
  lost unless you copy it back by hand.

## Afterwards

- HTTPS with Let's Encrypt (`certbot certonly --webroot -w /var/www/letsencrypt`).
  Keep plain HTTP working at first: whatever calls totalizator's `api_proc.php`
  may use `http://`.
- Keep the old VM stopped, along with its disk snapshot, for a couple of weeks
  before deleting it.
- Merge betacompany/pipeinpipe#1 and betacompany/totalizator#19, switch the
  checkouts to `master` and `develop`, and drop PHP 5.5 compatibility (see
  `dev/PHP85-FINDINGS.md`).

## Checking dev before the cutover

The new server's temporary address only accepts connections from Russia and
Kazakhstan, so reach it through an SSH tunnel and a browser window that sends
the dev hostnames into the tunnel.

1. Keep the tunnel open in a terminal:

   ```sh
   ssh -N -L 18080:127.0.0.1:80 -J ortemij@158.160.188.92 ortemij@158.160.231.151
   ```

2. Open a separate Edge window (its own profile, so your usual one isn't
   affected):

   ```sh
   open -na "Microsoft Edge" --args --user-data-dir=/tmp/pipeinpipe-dev-edge --no-first-run \
       --host-resolver-rules="MAP *dev.pipeinpipe.info 127.0.0.1:18080" http://dev.pipeinpipe.info/
   ```

   Any Chromium browser takes the same flags.

3. It asks for the dev password (user `pipe`):

   ```sh
   ssh -J ortemij@158.160.188.92 ortemij@158.160.231.151 'sudo grep DEV_BASIC_AUTH_PASS /etc/pipeinpipe/secrets.env'
   ```

Then sign in with your usual pipeinpipe login: dev's database is a copy of the
live one, and writes there don't reach the live sites. The mobile site, cupms
and totalizator are at `m.dev.`, `cupms.dev.` and `total.dev.pipeinpipe.info`.
