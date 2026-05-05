# Theme images

Drop static brand assets and fallback imagery here. Files in this directory ship via git push and are served from `/wp-content/themes/locals-realty/assets/images/` on every environment — no DB, no media library, no migration needed.

## Naming convention

The theme auto-loads these filenames as fallbacks when the corresponding ACF field is empty:

| Filename | Used when |
|---|---|
| `logo.svg` (or `.png`) | Site header, when no Custom Logo is set in WP Admin |
| `default-landing-hero.jpg` | Landing hero, when "Hero fallback image" ACF field is empty |
| `default-state-hero.jpg` | Any state page, when no per-state hero is set |
| `default-state-florida.jpg` | Florida specifically (slug-based) — wins over `default-state-hero.jpg` |
| `default-state-north-carolina.jpg` | NC, slug-based |
| `default-state-south-carolina.jpg` | SC, slug-based |
| `default-state-tennessee.jpg` | TN, slug-based |
| `default-lifestyle-hero.jpg` | Lifestyle realty hero on state pages |
| `state-card-florida.jpg` | "By state" card on landing — Florida (overrides featured image if set) |
| `state-card-north-carolina.jpg` | "By state" card — NC |
| `state-card-south-carolina.jpg` | "By state" card — SC |
| `state-card-tennessee.jpg` | "By state" card — TN |
| `state-card-default.jpg` | Generic "By state" card fallback when no per-state file exists |
| `mission-aside.jpg` | Landing — small image next to mission paragraph ("build the next version of your life.") |
| `team.jpg` | Landing — Meet the Team block; falls back to About page featured image if set |
| `join.jpg` | Landing — Join Today block |
| `lifestyle-small-towns-1.jpg` … `-3.jpg` | State page — Small Towns lifestyle feature, 3-image stack |
| `were-here-to-help.jpg` | State page — bottom CTA block |

## When to use this folder vs. the media library

- **Here (git):** logos, default hero/fallback imagery, decorative graphics, anything that should never change without a deploy.
- **Media library (`wp-content/uploads/`):** state hero photos that admins may swap, town gallery photos, agent headshots, anything editorial.

## Sizing guidance

- Hero images: 1920×900 minimum, JPG or WebP, optimized to <300KB.
- Logo: SVG preferred. Falls back to PNG at 2× retina (e.g. 400×120 for a 200×60 display).

## Format notes

- SVGs upload through the editor are also allowed (we enabled `image/svg+xml` in `inc/setup.php`).
- Avoid HEIC. Avoid uncompressed PNGs >1MB.
