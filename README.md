# The Locals Group — WordPress site

Custom WordPress theme for The Locals Realty Group, with Lofty IDX integration. Local development runs in Local by Flywheel; production deploys to WP Engine automatically via GitHub Actions on every push to `main`.

## TL;DR — daily workflow

1. **Edit** the theme/plugin in Local by Flywheel (`localwp.com`) — your Local site is symlinked to this repo, so changes show up instantly at `https://thelocalsgroup.local`.
2. **Commit and push to `main`** on GitHub.
3. **GitHub Actions deploys** the changed files to WP Engine over SSH (rsync). Live in ~1–2 min. Watch progress under the repo's **Actions** tab.

That covers **code** (PHP, CSS, JS, theme, our `locals-*` plugins). It does **not** sync the database (pages, posts, ACF values, menus, settings) or `wp-content/uploads/`. For those, see [Code vs. content](#code-vs-content-important) below.

## What is Lofty?

[Lofty](https://www.lofty.com/) (formerly Chime) is the IDX/CRM platform that powers our property search and lead capture. It does **not** ship a WordPress plugin — instead we embed Lofty's hosted search at `search.thelocalsgroup.com` via iframe (Phase 1) and drop in their JS lead-capture widgets where needed. See [Lofty IDX integration](#lofty-idx-integration) for the three integration tiers and how to configure each.

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
| Theme / plugin code | This git repo | Push to `main` → GitHub Actions deploys |
| Page content, CPTs, ACF values | Whichever environment is currently being authored on | WP Migrate (Local → staging → production) |
| Media (`uploads/`) | Same as content | WP Migrate, or rsync via SSH gateway |

Once production is the live site, content authoring shifts to production. Devs pull a fresh DB *down* to Local before doing template work that interacts with real content.

## WP Engine deploy (GitHub Actions)

Deployment is automated by `.github/workflows/deploy.yml`. On every push to `main` (or manual dispatch from the **Actions** tab), GitHub:

1. Checks out the repo.
2. Uses [`wpengine/github-action-wpe-site-deploy`](https://github.com/wpengine/github-action-wpe-site-deploy) to rsync over SSH:
   - `wp-content/themes/locals-realty/` → WPE install `thelocalsgroup`, same path.
   - `wp-content/plugins/locals-*/` → same. (Only our first-party plugins; other plugins on the server are left untouched.)
3. rsync runs with `--delete` — files removed locally are removed on WPE.

### One-time setup

1. **WP Engine** → User Portal → install `thelocalsgroup` → **SSH Keys** → add a deploy key. WPE provides the `WPE_SSHG_KEY_PRIVATE` to use as the GitHub secret.
2. **GitHub** → repo → Settings → Secrets and variables → Actions → add secret `WPE_SSHG_KEY_PRIVATE` with the private key contents.
3. Push to `main`. The Actions tab will show the deploy job. First run, activate **Locals Realty** in WPE's WP Admin → Appearance → Themes, and install the same plugin set as Local.

### Deploy gotchas

- **`--delete` is on.** Deleting a theme file locally and pushing will delete it on production. Lofty/ACF field-group PHP exports living inside the theme are easy to lose this way — keep them committed.
- **Only `wp-content/themes/locals-realty/` and `wp-content/plugins/locals-*/` ship.** Anything outside those paths (other themes, third-party plugins, `wp-config.php`, mu-plugins) is managed in WPE's WP Admin and is not touched by deploys.
- **No DB or uploads sync.** Use WP Migrate (see below) for content.
- **Manual re-deploy:** repo → Actions → "Deploy to WP Engine" → **Run workflow**.

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
- [x] WP Engine install created, SSH key added, GitHub Actions deploy workflow live
- [ ] First successful deploy verified on production
- [ ] About + Recruitment final copy
- [ ] Lead capture wiring (Lofty CRM endpoint or HubSpot/etc.)
- [ ] WP Migrate installed on both Local and WPEngine for DB sync
