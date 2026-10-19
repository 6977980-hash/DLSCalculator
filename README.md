# DLSCalc theme

WordPress child theme for [dlscalculator.com](https://dlscalculator.com): DLS, T20 DLS target, net run rate and run rate calculators.

- Parent theme: **GeneratePress** (free). Install it from WP admin; this theme only replaces its header, footer, styles and templates.
- Deploy: Hostinger Git deploys the `main` branch of this repo to `public_html/wp-content/themes/dlscalc`. Merging a PR into `main` publishes it.

## First setup on the server

1. Install and activate GeneratePress (Appearance → Themes → Add New).
2. Let Hostinger deploy this repo, then activate **DLSCalc** in Appearance → Themes.
3. On activation the theme creates its pages (Home, the four calculators, the DLS par score table, the two guides, About, Contact), sets Home as the front page and turns on pretty permalinks. Existing pages are never changed. When a deploy adds a page, it is created the next time an admin opens WP admin.
4. Optional: delete WordPress's "Sample Page" and "Hello world!" post, which add thin content.

The theme sends `noindex` on every page until launch. Phase 4 sets `DLSCALC_LAUNCHED` to `true` in `inc/layout.php`.

## What is where

| Path | What it does |
| --- | --- |
| `functions.php` | Loads the files in `inc/` |
| `inc/setup.php` | Menus, inline CSS, font preloads, removes unused parent and core assets |
| `inc/layout.php` | Replaces the parent header and footer; breadcrumbs, byline and author box on tool and guide pages; pre-launch `noindex` |
| `inc/pages.php` | Creates the site pages on activation |
| `inc/seo.php` | Titles, meta descriptions, Open Graph and Twitter cards, icons, base schema, robots.txt, sitemap trimming, thin-archive redirects |
| `inc/template-tags.php` | Logo, menus with built-in fallbacks, breadcrumbs, author details and box, tool page wrapper |
| `inc/dls.php` | DLS engine in PHP, a line-for-line copy of `assets/js/dls-engine.js`, so tool pages print real results without JavaScript |
| `inc/dls-ui.php` | DLS tool pages: inputs from the URL (result permalinks), result panel, working, par tables, script and style loading |
| `inc/content.php` | FAQs (also used for FAQPage schema), sources, related links and content dates |
| `page-dls-calculator.php`, `page-dls-target-calculator-t20.php`, `page-dls-par-score-table.php` | The three DLS tool pages; their articles are in `template-parts/content/` |
| `front-page.php`, `404.php` | Home and not-found pages |
| `template-parts/` | Site header and footer |
| `assets/css/main.css` | Site styles, printed inline in `<head>` |
| `assets/css/tools.css` | Tool styles, printed inline only on the DLS pages and home |
| `assets/js/dls-engine.js` | DLS engine: ICC Standard Edition table (delta-encoded), targets, par scores, par tables |
| `assets/js/dls-tools.js` | Live results, stoppage rows, permalinks and sharing on the DLS pages and the home quick box |
| `assets/fonts/` | Barlow Condensed 700 and Source Sans 3 (latin, SIL Open Font License) |
| `assets/img/` | Favicon, Apple touch icon, app icons, logo and per-page Open Graph images |
| `dev/tests/run.sh` | DLS engine tests: the six ICC worked examples in JS and PHP, JS and PHP agreement on 1,200 matches, and every table cell against the ICC table. Needs Node and PHP |
| `dev/make-charts.mjs` | Redraws the SVG charts in `assets/img/dls/` from the engine (`node dev/make-charts.mjs`) |
| `dev/make-images.mjs` | Regenerates the icons and Open Graph images (`node dev/make-images.mjs`, needs Playwright and Pillow). Blocked from the web by `dev/.htaccess` |

## Speed rules

- No page builder, no jQuery, no parent theme CSS or JS on the front end.
- CSS is under 10 KB and inlined, so there is no render-blocking stylesheet.
- Two self-hosted, preloaded WOFF2 fonts with `font-display: swap`.
- Each calculator loads its own small script only on its own page.

Lighthouse (mobile, local test server): Performance 100, Accessibility 100, Best Practices 100. SEO shows 66 only because of the pre-launch `noindex`.

## Result permalinks

The DLS pages keep their inputs in the address, so a shared link opens the same result:

- DLS Calculator: `n` overs per side, `r` Team 1 runs, `g` G50, `c` Team 2 overs at the start of the chase, `a` and `b` stoppages for Team 1 and Team 2 as `overs-wickets-lost`, joined with `_`. Example: `/dls-calculator/?n=50&r=250&g=245&c=50&b=12-1-10_22-3-2`.
- T20: `n`, `r`, `l` overs lost before the chase, `b`.
- Par score table: `o` overs per side, `r`, and optional `c`.

The names avoid WordPress's own query variables (`s` is search, `m` is a date archive). Pages with parameters send `noindex, follow`, and the canonical always points to the clean URL.

## Menus

Header and footer links work out of the box and skip pages that are not published. To change them, create menus in Appearance → Menus and assign them to **Main menu** and **Footer menu**.

## SEO plugins

If Yoast SEO or Rank Math is active, the theme stops printing titles, descriptions, Open Graph and schema so nothing is duplicated. Icons and robots.txt rules stay with the theme.
