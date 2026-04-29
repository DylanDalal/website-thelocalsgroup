# The Locals Group — WordPress site

Custom WordPress theme for The Locals Realty Group, with Lofty IDX integration. Local development runs in Local by Flywheel; production deploys to WPEngine via git push.

## Repo layout

```
wp-content/
  themes/
    locals-realty/         <- our custom theme (this repo's main payload)
  plugins/                  <- only first-party plugins are committed; ACF etc. are installed via WP admin or Composer
.gitignore                  <- excludes WP core, third-party plugins, uploads
```

WordPress core, `wp-config.php`, uploads, and third-party plugins are **not** in this repo. WPEngine provides core; admins install third-party plugins through the WP dashboard. Anything we author lives in `wp-content/themes/locals-realty/` (and eventually `wp-content/plugins/locals-*/` if we extract reusable functionality).

## Local setup (Local by Flywheel)

1. Install [Local](https://localwp.com/) and create a new site:
   - Name: `thelocalsgroup`
   - Environment: **Custom** — PHP **8.1+**, MySQL **8.0**, nginx
   - WordPress username/password: your choice (admin)
2. Once the site is created, in Local click **→ Open site folder**. You will see `app/public/` — that's WordPress.
3. **Replace `app/public/wp-content/themes/` and `app/public/wp-content/plugins/` with symlinks to this repo:**
   ```bash
   cd /path/to/Local/sites/thelocalsgroup/app/public/wp-content
   rm -rf themes plugins
   ln -s /Users/Dylan/Documents/GitHub/website-thelocalsgroup/wp-content/themes themes
   ln -s /Users/Dylan/Documents/GitHub/website-thelocalsgroup/wp-content/plugins plugins
   ```
   (You can instead clone the repo directly into `wp-content/`, but symlinking keeps the working copy outside Local's site directory.)
4. Start the site in Local and visit **WP Admin → Appearance → Themes** → activate **Locals Realty**.
5. Install required plugins via WP Admin → Plugins → Add New:
   - **Advanced Custom Fields** (free is enough; Pro if we want repeaters/flexible content)
   - **WP Migrate** (optional, for syncing DB with WPEngine)
   - **Yoast SEO** or **Rank Math**
6. Create the page stubs and assign templates:
   - `/` (front page, "Sample Page" deletable) → assigns `front-page.php` automatically
   - `/search` page → template **Search (Lofty IDX)**
   - `/about` page → template **About**
   - `/join` page → template **Recruitment**
   - **Settings → Reading** → set Front page = the page you want as Home (or leave Latest Posts; `front-page.php` overrides regardless)
7. Add CPT entries: WP Admin → States (4: Florida, North Carolina, South Carolina, Tennessee), Towns, Agents.

## Lofty IDX integration

Lofty does not ship a WordPress plugin. Three integration options, in order of effort:

| Option | Description | When to use |
|--------|-------------|-------------|
| **Iframe subdomain** | Lofty hosts a fully-branded site at e.g. `search.thelocalsgroup.com`; we iframe it on `/search` and on state pages via `[lofty_search]` shortcode. | **Phase 1.** Default in this scaffold. |
| **JS widgets** | Lofty's lead-capture / search-bar JS snippets pasted into specific pages. | Add alongside iframe for landing-page lead capture. |
| **Direct API → CPT** | Sync Lofty property feed into a `property` post type, render natively. | Phase 3 once SEO needs justify it. |

To configure Phase 1, add to `wp-config.php` (above the `/* That's all, stop editing! */` line):

```php
define('LOFTY_IDX_BASE_URL', 'https://search.thelocalsgroup.com');
define('LOFTY_WIDGET_KEY',   'paste-from-lofty-dashboard');
```

These constants are read by `wp-content/themes/locals-realty/inc/lofty-idx.php`. The `[lofty_search town="Stuart" state="FL" height="800"]` shortcode then renders an iframe pointed at the Lofty search.

**WPEngine gotcha**: WPEngine's default cache may interfere with iframe-heavy pages and lead-capture POSTs. After deploying:
- WPEngine User Portal → your install → Caching → exclude `/search/*` and any Lofty subdomain routes from full-page cache.
- Confirm no `X-Frame-Options: DENY` header is set; the Lofty subdomain must allow framing from the production domain.

## Code vs. content (important)

WPEngine's git push deploys **files only**. The repo carries:

- Theme code, CSS, JS
- Custom post type / taxonomy registration (`inc/post-types.php`)
- ACF field group **definitions** (`inc/acf.php`)
- Lofty integration code & shortcode

The **database** holds everything else — and is *not* in git:

- Pages, posts, State/Town/Agent entries
- ACF field **values** (the filled-in data)
- Menus and theme location assignment
- Settings (Reading, Permalinks, etc.)
- Users
- Media in `wp-content/uploads/`

After the first git push, WPEngine will have our theme but zero content. Use **WP Migrate** (free is enough) to sync DB + uploads from Local → WPEngine staging, verify, then promote staging → production with WPEngine's **Copy Environment** tool.

Recommended workflow:

| Layer | Source of truth | Sync method |
|---|---|---|
| Theme / plugin code | This git repo | `git push production main` |
| Page content, CPTs, ACF values | Whichever environment is currently being authored on | WP Migrate (Local → staging → production) |
| Media (`uploads/`) | Same as content | WP Migrate, or rsync via SSH gateway |

Once production is the live site, content authoring shifts to production. Devs pull a fresh DB *down* to Local before doing template work that interacts with real content.

## WPEngine deploy

WPEngine offers two git-deploy methods. We will use the **Git Push** method (simpler, no GitHub Action required):

1. In the WPEngine User Portal, open the install (e.g. `thelocalsgroup`) and add your SSH public key under **SSH Gateway / Git Push**.
2. Add the WPEngine remote in this repo:
   ```bash
   git remote add production git@git.wpengine.com:production/thelocalsgroup.git
   git remote add staging    git@git.wpengine.com:staging/thelocalsgroup.git
   ```
3. Push:
   ```bash
   git push production main
   git push staging main
   ```
   WPEngine receives the push at the `wp-content/` level — our repo layout matches that, so theme and plugin paths land in the right place.
4. After first deploy, activate the theme in WPEngine's WP Admin and install the same plugin set as Local.

> **Heads up**: WPEngine's git deploy is **add/update only by default**. Files removed from the repo are not removed on the server unless you explicitly enable file deletion in the install's Git settings.

## Conventions

- PHP 8.1+; no global functions outside `inc/` or templates; prefix helpers with `locals_`.
- Follow [WordPress coding standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) for PHP files.
- Escape on output (`esc_html`, `esc_url`, `esc_attr`); validate/sanitize on input.
- Asset versions are pinned to `LOCALS_REALTY_VERSION` so cache-busting is automatic on bumps.

## Status

- [x] Theme scaffold (header, footer, landing, state, search, about, recruitment)
- [x] Custom post types: state, town, agent + lifestyle/state taxonomies
- [x] ACF field groups (defined in PHP, registered if ACF is active)
- [x] Lofty iframe + `[lofty_search]` shortcode
- [ ] Brand tokens applied (colors, type) from final design
- [ ] Lofty subdomain provisioned & `LOFTY_IDX_BASE_URL` set
- [ ] WPEngine install created, SSH key added, first deploy
- [ ] About + Recruitment final copy
- [ ] Lead capture wiring (Lofty CRM endpoint or HubSpot/etc.)
- [ ] WP Migrate installed on both Local and WPEngine for DB sync
