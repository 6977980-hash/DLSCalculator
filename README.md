# DLSCalc theme

WordPress child theme for [dlscalculator.com](https://dlscalculator.com): DLS, T20 DLS target, net run rate and run rate calculators.

- Parent theme: **GeneratePress** (free). Install it from WP admin; this theme only replaces its header, footer, styles and templates.
- Deploy: Hostinger Git deploys the `main` branch of this repo to `public_html/wp-content/themes/dlscalc`. Merging a PR into `main` publishes it.

## First setup on the server

1. Install and activate GeneratePress (Appearance → Themes → Add New).
2. Let Hostinger deploy this repo, then activate **DLSCalc** in Appearance → Themes.
3. On activation the theme creates its pages (Home, the four calculators, the two guides, About, Contact), sets Home as the front page and turns on pretty permalinks. Existing pages are never changed.
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
| `inc/template-tags.php` | Logo, menus with built-in fallbacks, breadcrumbs, author details and box |
| `front-page.php`, `404.php` | Home and not-found pages |
| `template-parts/` | Site header and footer |
| `assets/css/main.css` | All styles, printed inline in `<head>` |
| `assets/fonts/` | Barlow Condensed 700 and Source Sans 3 (latin, SIL Open Font License) |
| `assets/img/` | Favicon, Apple touch icon, app icons, logo and per-page Open Graph images |
| `dev/make-images.mjs` | Regenerates the icons and Open Graph images (`node dev/make-images.mjs`, needs Playwright and Pillow). Blocked from the web by `dev/.htaccess` |

## Speed rules

- No page builder, no jQuery, no parent theme CSS or JS on the front end.
- CSS is under 10 KB and inlined, so there is no render-blocking stylesheet.
- Two self-hosted, preloaded WOFF2 fonts with `font-display: swap`.
- Each calculator loads its own small script only on its own page.

Lighthouse (mobile, local test server): Performance 100, Accessibility 100, Best Practices 100. SEO shows 66 only because of the pre-launch `noindex`.

## Menus

Header and footer links work out of the box and skip pages that are not published. To change them, create menus in Appearance → Menus and assign them to **Main menu** and **Footer menu**.

## SEO plugins

If Yoast SEO or Rank Math is active, the theme stops printing titles, descriptions, Open Graph and schema so nothing is duplicated. Icons and robots.txt rules stay with the theme.
