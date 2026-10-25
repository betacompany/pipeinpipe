# PHP 8.5 findings (2026-10-24)

From `scripts/scan.sh` (php -l on PHP 8.5.12 and 5.5.9, PHPCompatibility
develop with testVersion 5.5-8.5), `scripts/smoke.sh` runs over
`smoke-urls.txt` (anonymous and signed-in pages of all four sites), and
clicking through all four sites signed in, in a browser, on both ports.

## Status

Since the cutover on 2026-10-25, the sites run on PHP 8.5 only, and the code
no longer supports PHP 5.5 (see "Done after the cutover"). The rest of this
section describes the upgrade itself, when the code ran on both versions.

- **Smoke test:** all 68 checks return the same status on 5.5 and 8.5, with
  the same content apart from:
  - random content (home page video, forum tab);
  - render times;
  - the order of ties (see "Behaviour changes").
- **PHP 8.5 log:** the only fatals are the two existing prod bugs below, which
  fail the same way on 5.5. No runtime deprecations are logged. Its ~14,700
  warnings are 5.5's "undefined index/offset" notices, which PHP 8 reports as
  warnings.
- **Totalizator writes:** adding a match, staking and finishing it write
  identical rows on both versions.
- **Totalizator tests:** they pass on PHP 8.5 with PHPUnit 13.
- **PHP 5.5:** pages are unchanged from the pre-change baseline.
- **Static checks:** every file parses on 5.5. Nothing fails to compile on
  8.5. A signature check finds no incompatible overrides, and every static
  call resolves.

## Fixed

| # | Repo | Problem | Fix |
| - | ---- | ------- | --- |
| 1 | both | ext/mysql removed in 7.0 (728 + 102 findings) | mysqli layer of `db_*` functions keeping ext/mysql's return values (`main/includes/mysql.php`, totalizator `lib/mysql.php`), and `mysqli_report(MYSQLI_REPORT_OFF)`, also in `auth/properties/commons.php`, since 8.1 mysqli throws instead of returning false |
| 2 | totalizator | Class `Match`: `match` is reserved since 8.0 | Renamed to `TotalMatch` |
| 3 | pipeinpipe | 14 child methods incompatible with the parent (fatal since 8.0) | The base classes' generic static methods got their own names: `Item::createItem()`, `getAllItems()`, `getItemsByPeriod()`, `Group::createGroup()`, `Comment::createComment()`, `Action::createAction()`. The children's `create()` etc. are unchanged. Also `CrossPhoto::create()` became `createFromSocialPhoto()`, `ForumTopic::addComment()` became `addMessage()`, `CupTwoLaps::getGameGrid()` takes `$reload`, and `ItemsContainer::isActedBy()` defaults `$type` |
| 4 | pipeinpipe | Non-static methods called statically | `RatingFormula` reads the `NAME` constants; `GroupDBClient::getAllByType()` and `remove()` are static |
| 5 | pipeinpipe | `$this` in static `ForumDB::updateTopic()` (also broken on 5.5) | Uses `$topic` |
| 6 | pipeinpipe | `call_user_method()` removed | `call_user_func(array($obj, ...))` |
| 7 | pipeinpipe | `$_REQUEST[method]`: undefined constant is an Error | Quoted |
| 8 | totalizator | `PHPUnit_Framework_TestCase` | `\PHPUnit\Framework\TestCase` |
| 9 | totalizator | Stakes popup: `strcmp()` with a `Stake` object | Starts from "no previous score", same output as 5.5 |
| 10 | pipeinpipe | `ResponseCache`: `array_merge()` with `$_SESSION`, which is never set | Explicit empty request when there is no session: one cache file per key, as on prod (`/sport/pipemen`, `/life/people`, `api.php`) |
| 11 | pipeinpipe | `count(null)` (a `TypeError` since 8.0) | `profile_view.php:343` (player pages), `forum_view.php:150` (forum topics) |
| 12 | pipeinpipe | Runtime deprecations | `trim()`/`htmlspecialchars()`/`mb_strlen()` with null, `setcookie()` with null expiry, dynamic `Cup::$pmids`/`$pmidsLoaded`, float modulo in `Logger` |
| 13 | pipeinpipe | Compile-time deprecations | 36 `(boolean)` casts; `#[\ReturnTypeWillChange]` on the Iterator methods (a comment on 5.5); `final private`; optional parameter before a required one in `ItemDBClient::insert()` |
| 14 | pipeinpipe | `PlayerComparator::getPlayer(int $which)`: `int` is a class name on PHP 5, so every call fails today | Type hint removed |
| 15 | pipeinpipe | The page cache never worked on prod (`main/temp` not writable by PHP), and its keys were unsafe to enable: `api.php` would serve uncached-TTL methods (incl. writes) from cache forever, and the home page and closed forum topics were cached across users | `api.php` caches only methods with a TTL (none while `API_DEBUG_MODE` is on); home page and closed topics are cached per user. Prod got a `www-data`-owned `temp/cache` on 2026-10-24: `/sport/pipemen` went from 40 s to 0.24 s |
| 16 | pipeinpipe | `Logger` with an unwritable `main/logs` (as on both servers): `fwrite(false)` is a `TypeError` since 8.0, which cut `/life` off mid-page | Skips the write when the file can't be opened |
| 17 | pipeinpipe | Found on prod after the cutover: `max()` of an empty array is a `ValueError` (not an `Exception`, so the `catch` around it didn't help), which cut off the pages of players with no rating history (`/pm328`) | Returns 0 for them, as PHP 5's `false` did |
| 18 | pipeinpipe | Found on prod after the cutover: a cup with one child cup has zero columns, and `%`/`/` by zero is a `DivisionByZeroError` (`/sport/league/1/competition/3`) | No division when there are no columns; nothing is drawn for them either way |

## MariaDB 11.8 instead of MySQL 5.5

The new server runs Ubuntu 26.04's MariaDB. Replaying the sites' queries on
both engines found:

- **`ResultCupDBClient::getCompetitionPlaces()`** (places on player pages): a
  `GROUP BY` over an ordered `FROM` subquery, which MariaDB doesn't keep
  ordered, so it picked an arbitrary cup per competition. Rewritten with a
  correlated subquery; identical to MySQL 5.5 for all 344 players.
- **`Feed`:** `IF(uid=0, UUID(), uid)` fails because MariaDB's `UUID()` has its
  own type. Cast to `CHAR`.
- **`GROUP_CONCAT`:** MariaDB cuts results at 1 MB instead of 1024 bytes. Kept.
- **Failed queries** are now logged (`db_qw: ...` in the PHP error log) in both
  repos, since the callers only see `false`.

## Done after the cutover

Once the sites ran on the new server (2026-10-25), the code stopped supporting
PHP 5.5, which made these possible. PHPCompatibility against 8.5 now finds
nothing in either repo.

- **Implicitly nullable parameters (`Type $x = null`):** 4 deprecations on
  8.4+, now `?Type` (PHP 7.1+ syntax).
- **`imagedestroy()`:** 16 calls, deprecated in 8.5 and a no-op since 8.0.
  Removed.
- **`#[\ReturnTypeWillChange]`** on the `Iterator` methods of
  `MySQLResultIterator` and `BijectiveMap` is now real return types.

Prod's error log turned up two pages the upgrade broke (fixes 17 and 18). A
crawl of 1,955 pages on the stand then found no others. It covered every
player, league, competition, forum topic, blog post and video, plus 300
photos and 652 user profiles. The parser's output for all 15,280 stored
comments and forum messages is identical on PHP 5.5 and 8.5.

## Requirements for the new server

- **Start from PHP's production `php.ini`, as Ubuntu's `php8.5-fpm` package
  does.** In particular, keep `request_order = "GP"`: the API echoes
  `$_REQUEST` in error responses and would include the login cookie otherwise.
  Also set `short_open_tag = On`.
- **Page cache directory:** `main/temp/cache` must be writable by PHP
  (`www-data`); `main/temp` itself stays read-only, as on prod, so the lock
  and per-user `temp/user_*` files keep behaving as they do today.
- **GD (`php8.5-gd`):** installed since 2026-10-25 for the photo backgrounds
  and avatar miniatures.
- **nginx `fastcgi_read_timeout 120s`:** a cold `/sport/pipemen` renders for
  about 60 s, nginx's default.

## Behaviour changes to check by eye

- **Sort order of ties:** PHP 8 sorts are stable, so equal items now keep their
  original order. Visible in album order on `/media/photo` and in totalizator's
  stakes popup.
- **Hall of fame:** totalizator's `gloryhall.php` order of tied winners varies
  between requests on both versions (`ORDER BY count DESC` without a
  tie-breaker).
- **`htmlspecialchars()`:** it now also escapes `'` by default (8.1), in 7
  calls. It looks the same in a browser.
- **`mb_strlen()` in the tag cloud:** it counts Cyrillic characters instead of
  bytes, so tag sizes may change slightly.
- **cupms player editor:** it now works. On 5.5 it runs out of the 128 MB
  memory limit (`players_view.php:113`), so it's broken on prod today.

## Broken before the upgrade

These failed the same way on the old server. Fixed on 2026-10-25:

- **Photo backgrounds** (`api.php?handler=photobg`, called by every desktop
  page) returned 500 without GD. GD is installed now.
- **Avatars:** pages stopped at a missing avatar miniature (comments on
  `/life/blog/1762` and `/1789`), since making one needs GD and a writable
  `images/users`. `server/make-miniatures.php` made the 21 missing ones, and a
  page shows the default avatar instead of stopping if one is still missing.
- **Disabled users:** the forum list stopped at a topic by a disabled user
  (`/forum/part1`; users 767 and 1126 aren't in the `p_user` view). It now
  shows no author there, as `blocks.php` already did.
- **Forum topics 211 and 351:** a stray closing tag in messages 5656 and 10163
  made the parser give up (PHP 5: "Please remove tag </idle>"). It now drops
  such a tag, as browsers do. Every other stored text parses as before.
- **Deleted and missing content:** deleted photo albums and blog posts,
  profiles of missing users, and empty albums or tags (the photo viewer's
  `null`) rendered an empty or cut-off page with status 200. They're now a 404.
- **Latest comments** (`/life/comments`): `CommentDBClient::getByItemType()`
  built invalid SQL, so the page showed none.

Still broken:

- **Dead media:** album photos hosted on VK's old CDN no longer load, and the
  home page video is a Flash embed.
- **Photo and avatar uploads:** PHP can't write to the uploaded content on the
  server, as on the old one.
- **Unresolved static calls:** three calls to methods that don't exist:
  `UserDataDBClient::getUIDByFBId()`, `CompetitionDBClient::removeById()` in a
  test, and `Parser::parse()` in `junk/`.
