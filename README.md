# DLSCalc theme

WordPress child theme for [dlscalculator.com](https://dlscalculator.com): DLS, T20 DLS target, net run rate and run rate calculators.

- Parent theme: **GeneratePress** (free). Install it from WP admin; this theme only replaces its header, footer, styles and templates.
- Deploy: Hostinger Git deploys the `main` branch of this repo to `public_html/wp-content/themes/dlscalc`. Merging a PR into `main` publishes it.

## First setup on the server

1. Install and activate GeneratePress (Appearance → Themes → Add New).
2. Let Hostinger deploy this repo, then activate **DLSCalc** in Appearance → Themes.
3. On activation the theme creates its pages (Home, the four calculators, the DLS par score table, the live DLS par tracker, the three guides, About, Contact, and the famous DLS matches hub with one page per match under it), sets Home as the front page and turns on pretty permalinks. Existing pages are never changed. When a deploy adds a page, it is created the next time an admin opens WP admin.
4. Publish WordPress's draft **Privacy Policy** page (Pages → Privacy Policy → Publish). Its text comes from `page-privacy-policy.php`, so the sample text in the editor never shows.
5. Optional: delete WordPress's "Sample Page" and "Hello world!" post. They are `noindex` and left out of the sitemap anyway.

The site launched on 19 October 2026: `DLSCALC_LAUNCHED` in `inc/layout.php` is `true`. Setting it to `false` sends `noindex, nofollow` on every page again.

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
| `inc/dls-ui.php` | DLS tool pages: inputs from the URL (result permalinks), result panel, field errors, scenario buttons (ICC examples), working, par tables, script and style loading; the resource lookup on `/dls-method/` |
| `inc/security.php` | Hides the REST users endpoints from visitors, removes the author from oEmbed, turns off XML-RPC, one login error for wrong username or password, and sends security headers (HSTS, nosniff, Referrer-Policy, frame protection, Permissions-Policy) on every page except the `/embed/dls/` widget |
| `inc/nrr.php` | NRR engine in PHP (NRR across matches, points table, qualification solver, run rate), a line-for-line copy of `assets/js/nrr-engine.js` |
| `inc/nrr-ui.php` | NRR and run rate pages: inputs from the URL and no-JS forms, match and team rows, standings, script loading |
| `inc/content.php` | FAQs (also used for FAQPage schema), sources, related links and content dates |
| `inc/guides.php` | Guides (`dlscalc_guides()`: heading, answer box, contents, call to action), Article schema, trust page layout, contact email |
| `page-guide.php` | Shared template for the guides (`/dls-method/`, `/how-to-calculate-nrr/`, `/vjd-method/`) and the famous matches pages; articles in `template-parts/content/<slug>.php` |
| `inc/embed.php`, `template-parts/embed-dls.php`, `assets/js/embed.js` | Embeddable DLS widget at `/embed/dls/` (standalone, noindex, works without JS) and the "Add this DLS calculator to your website" box with the embed code on `/dls-calculator/`. The code puts the credit link in the host page, outside the iframe |
| `inc/pwa.php`, `assets/js/sw.js` | Offline mode: service worker served at `/sw.js` (site root, so it covers every page). It saves home, the calculators, the par table and the live tracker with their theme files when it installs; pages are network first with the saved copy offline, theme files saved-copy first. Not registered for logged-in users |
| `inc/matches.php` | Famous rain-hit matches: facts, the DLS Calculator inputs for each and the official target to compare with |
| `inc/matches-ui.php` | `/famous-dls-matches/` hub and the match pages under it: URLs, SEO data, FAQs, the recalculation box and the accuracy table |
| `page-about.php`, `page-contact.php`, `page-privacy-policy.php` | Trust pages, written in the theme |
| `page-dls-calculator.php`, `page-dls-target-calculator-t20.php`, `page-dls-par-score-table.php`, `page-live-dls-par-score.php` | The DLS tool pages and the live par tracker; their articles are in `template-parts/content/` |
| `page-net-run-rate-calculator.php`, `page-run-rate-calculator.php` | NRR calculator with the qualification scenario, and the run rate calculator |
| `front-page.php`, `404.php` | Home and not-found pages |
| `template-parts/` | Site header and footer |
| `assets/css/main.css` | Site styles, printed inline in `<head>` |
| `assets/css/prose.css` | Article styles (tables, FAQ, sources, related links, guide boxes), printed inline on tool pages, guides and trust pages |
| `assets/css/tools.css` | Tool styles, printed inline only on the tool pages and home |
| `assets/css/nrr.css` | NRR, run rate and live tracker styles, printed inline only on those pages |
| `assets/js/dls-engine.js` | DLS engine: ICC Standard Edition table (delta-encoded), targets, par scores, par tables |
| `assets/js/dls-tools.js` | Live results, stoppage rows, permalinks and sharing on the DLS pages and the home quick box |
| `assets/js/resource-lookup.js` | Resource lookup on `/dls-method/` (overs left and wickets lost to resources %) |
| `assets/js/live-par.js` | Live DLS par tracker: score rows, par per entry, saved on the device |
| `assets/js/nrr-engine.js` | NRR engine: NRR with the all-out and no-result rules, points table, qualification solver, run rate |
| `assets/js/nrr-tools.js` | Live results, match and team rows, saved points table, permalinks and sharing on the NRR and run rate pages |
| `assets/fonts/` | Barlow Condensed 700 and Source Sans 3 (latin, SIL Open Font License) |
| `assets/img/` | Favicon, Apple touch icon, app icons, logo and per-page Open Graph images |
| `inc/ads.php` | Ad slots (print nothing until `DLSCALC_ADSENSE_CLIENT` is set) and the `/ads.txt` body |
| `inc/llms.php` | Serves `/llms.txt` (built from the site's pages) and `/ads.txt` |
| `inc/indexnow.php` | IndexNow for Bing and the other IndexNow engines: key file at `/<key>.txt`, and after a deploy (checked every 5 minutes from the theme files' modification times) a ping with the pages whose files changed, or every page when a shared file changed. The key file's `X-IndexNow-Last` header shows the last ping |
| `docs/keyword-map.md` | One owner page per keyword. Check it before adding a page, title, H1 or section. Blocked from the web by `docs/.htaccess` |
| `dev/tests/run.sh` | Engine tests: the ICC DLS worked examples plus NRR and run rate examples in JS and PHP, JS and PHP agreement on about 2,000 cases (DLS, live tracker, NRR, solver, run rate), and every table cell against the ICC table. Needs Node and PHP |
| `dev/make-charts.mjs` | Redraws the SVG charts in `assets/img/dls/` from the engine (`node dev/make-charts.mjs`) |
| `dev/make-images.mjs` | Regenerates the icons and Open Graph images (`node dev/make-images.mjs`, needs Playwright and Pillow; add slugs to redraw only those share images). Blocked from the web by `dev/.htaccess` |

## Speed rules

- No page builder, no jQuery, no parent theme CSS or JS on the front end.
- CSS is under 10 KB and inlined, so there is no render-blocking stylesheet.
- Two self-hosted, preloaded WOFF2 fonts with `font-display: swap`.
- Each calculator loads its own small script only on its own page.

Lighthouse (mobile, local test server): Performance 99 to 100, Accessibility 100, Best Practices 100, SEO 100. WordPress's block preset CSS (global styles, about 11 KB) and the feed, oEmbed and REST links are removed from every page.

## Result permalinks

Every tool keeps its inputs in the address, so a shared link opens the same result:

- DLS Calculator: `n` overs per side, `r` Team 1 runs, `g` G50, `c` Team 2 overs at the start of the chase, `a` and `b` stoppages for Team 1 and Team 2 as `overs-wickets-lost`, joined with `_`, `f` format (`odi`, `t20`, `t10` or `custom`; sets the minimum overs for a result, ODI 20 and T20 5; without it a 50-over match counts as ODI and a 20-over match as T20), `k` penalty runs awarded to Team 1 during the chase (raise the target and every par, ICC DLS section 6.2). Errors name their field (`field` in the engine result: `n`, `c`, `k` or a stoppage field like `b-2-at`). Example: `/dls-calculator/?n=50&r=250&g=245&c=50&b=12-1-10_22-3-2`.
- T20: `n`, `r`, `l` overs lost before the chase, `b`.
- Resource lookup on `/dls-method/`: `v` overs left, `d` wickets lost.
- Par score table: `o` overs per side, `r`, and optional `c`.
- Live DLS par: `n`, `r`, `g`, `c`, and `x` the score log as `overs-runs-wickets` joined with `_`.
- Net run rate: `q` overs per side and `x` matches as `scored-overs-conceded-overs`, with `a` after overs for an all-out innings, an optional fifth field for the overs per side when rain cut that match (`143-15-120-12a-15`; an all-out side counts those overs) and `-n` on the end for a no result. Qualification scenario: `u=t` opens it, `t` the points table (teams joined with `_`, fields with `~`: name, played, won, points, runs for, overs faced, runs against, overs bowled), `y` your team, `v` team to pass, `o` next opponent (`0` = not in the table), `k` `f` bat first or `c` chase, `e` score, `h` overs per side, `d` points for a win.
- Run rate: `u` `c` chasing or `f` batting first, `n`, `t` target, `r` runs, `o` overs bowled.

The names avoid WordPress's own query variables (`s` is search, `m` is a date archive, `p` and `w` are taken too). Pages with parameters stay indexable, and the canonical always points to the clean URL (a noindex on top would conflict with it). The NRR points table and the live tracker are also saved in the visitor's browser (localStorage), so a return visit to the clean URL reopens them.

## Menus

Header and footer links work out of the box and skip pages that are not published. To change them, create menus in Appearance → Menus and assign them to **Main menu** and **Footer menu**.

## Ads

Ad slots are reserved but print nothing yet. To switch ads on, set `DLSCALC_ADSENSE_CLIENT` in `inc/ads.php` to the AdSense publisher ID (`ca-pub-…`): that loads AdSense Auto ads and adds the Google line to `/ads.txt`. For fixed units, put their `data-ad-slot` IDs in `dlscalc_ad_units()`: `top` sits where the article starts (under the calculator, or under a guide's contents box) and `bottom` above the author box. About, Contact, Privacy Policy and 404 pages never show ads. Each fixed slot reserves 280 px so a loading ad does not shift the page.

## llms.txt and ads.txt

WordPress serves both (`inc/llms.php`), like robots.txt. A real file with the same name in `public_html` would win, so none should exist there.

## SEO plugins

If Yoast SEO or Rank Math is active, the theme stops printing titles, descriptions, Open Graph and schema so nothing is duplicated. Icons and robots.txt rules stay with the theme.
