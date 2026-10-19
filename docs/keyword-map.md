# Keyword map (one owner page per keyword)

Check this file before writing a new page, a title, an H1 or a new H2 section. Updated 19 October 2026 (audit 2 phase 2: F26, F35, F44).
Volumes are Google Keyword Planner averages per month, Oct 2025 to Sep 2026 (global / Pakistan + India + Bangladesh), from `/mnt/project-files/keywords/`.

## Rules

1. Every keyword has exactly one **owner page**. Only the owner uses the keyword in its title tag and H1.
2. Close variants (calculator / formula / table / full form, T20 / ODI, IPL / PSL) are sections (H2s and FAQs) of the owner page, not new pages.
3. Other pages may mention the keyword in body text and **link to the owner** with a descriptive anchor, but never answer the whole question again in an H2. One or two sentences plus the link is enough.
4. A new page needs a keyword that no page below owns. Add its row here in the same PR.
5. Title tags live in `dlscalc_seo_pages()` (`inc/seo.php`), H1s in the page templates or `dlscalc_guides()` (`inc/guides.php`).

## Owner pages (live)

| Page | Owns (title and H1) | Also owns (sections, FAQs) | Volume of main keyword | Must not target |
| --- | --- | --- | --- | --- |
| `/` Home | brand "DLSCalc", cricket calculators | links to every tool | – | "dls calculator", "net run rate calculator" in the title (they belong to the tool pages) |
| `/dls-calculator/` | **dls calculator** | duckworth lewis calculator (5K), dls method calculator (5K), dls target calculator (500), dls calculator odi / 50 overs, rain rule calculator, revised target calculator | 50K / 5K | t20, par score table, dls method explanation |
| `/dls-target-calculator-t20/` | **dls target calculator t20** | dls calculator t20 (500), dls calculator t10 (50), IPL/PSL/T20 World Cup rain targets | 500 / 500 | dls par score t20 as a heading (owned by the par table) |
| `/dls-par-score-table/` | **dls par score calculator / table** | dls par score (500), dls par score chart / t20 / odi, par for 8/10/12/15/20/50 overs | 500 / 500 | live par |
| `/live-dls-par-score/` | **live dls par score** | live dls calculator, who is ahead on dls, dls par score live (50) | 50 / 50 | the par table itself |
| `/net-run-rate-calculator/` | **net run rate calculator** | nrr calculator (5K), nrr calculator cricket / t20 / ipl / psl, qualification and scenario terms (cricket qualification calculator, how many runs to win to improve nrr, points table calculator, t20 world cup / ipl / psl qualification scenarios) | 50K / 5K | net run rate formula, how to calculate nrr (summary + link only) |
| `/run-rate-calculator/` | **run rate calculator cricket** | required run rate calculator (500), current run rate calculator (50), required run rate formula, projected score calculator, overs to balls calculator (50), rrr calculator | 5K / 500 | net run rate |
| `/dls-method/` | **dls method** | how dls method works (500), dls method formula (500), how is dls target calculated (50), dls full form (5K), dls resource table / duckworth lewis table / dls table t20 / dls table 50 overs, dls minimum overs / dls rules, d/l vs dls | 50K / 50K | calculator terms |
| `/strike-rate-calculator/` | **strike rate calculator cricket**, **batting average calculator cricket** | strike rate formula, batting average formula (not outs), runs per innings, balls per boundary, target strike rate, good strike rate T20 / ODI / Test | 500 / 500 each | bowling strike rate (one sentence + link to the bowling page) |
| `/bowling-average-calculator/` | **bowling average calculator**, **economy rate calculator cricket** | bowling average formula, economy rate formula (overs in balls), bowling strike rate, dot ball %, which wickets count for the bowler, good figures by format | 500 / 500 and 50 / 50 | batting strike rate (link to the strike rate page) |
| `/how-to-calculate-nrr/` | **how to calculate nrr** | net run rate formula (5K), nrr formula (5K), nrr full form (5K), what is nrr in cricket (5K) | 5K / 500 | net run rate calculator |
| `/vjd-method/` | **vjd method** | vjd method in cricket (5K), vjd vs dls (50), jayadevan method | 5K / 5K | dls method |
| `/famous-dls-matches/` | **famous dls matches** | dls controversy matches, famous rain-affected matches, dls calculator accuracy (official vs our target table) | no data | "dls calculator" in title or H1 |
| `/famous-dls-matches/<match>/` (9 pages) | the match name + dls, e.g. **2019 world cup semi final dls**, **ipl 2023 final dls target**, **india vs bangladesh dls 2022** | that match's targets, par and story | no data | "dls calculator", "dls method" in title or H1; the general method (link to `/dls-method/`) |
| `/about/`, `/contact/`, `/privacy-policy/` | brand and trust | – | – | – |

## Old plan URLs that are now sections (do not create these pages)

| Old idea | Now covered by |
| --- | --- |
| `/how-dls-method-works/`, `/dls-rules/`, `/dls-resource-table/` | `/dls-method/` |
| `/dls-par-score-t20/`, `/dls-par-score-odi/`, per-overs par pages | `/dls-par-score-table/` |
| `/what-is-nrr/`, `/glossary/` (nrr full form, dls full form) | `/how-to-calculate-nrr/` and `/dls-method/` |
| `/qualification-scenarios/` | Qualification tab of `/net-run-rate-calculator/` |
| `/overs-converter/` | Converter on `/run-rate-calculator/` |

## Free keywords for future pages (no owner yet)

None left from the keyword research. The stats keywords got their pages in audit fix phase 4 (F16, 19 October 2026).

## Fixes made on 19 October 2026

- Home title was "DLS Calculator & Net Run Rate Calculator for Cricket", which competed with both tool pages. Now "DLSCalc: Free Cricket Calculators for Rain Targets, NRR & Run Rate".
- `/dls-calculator/` title and H1 said "Par Score", the par table's keyword. Now "DLS Calculator: Revised Target for Rain-Hit Matches".
- `/dls-target-calculator-t20/` had an H2 "T20 par score table". Now "Par score for the rest of the chase", the same wording as the DLS calculator.
- Audit F10 (19 Oct): `/dls-calculator/` H2 "Par score table for the rest of the chase" is now "Par score for the rest of the chase", and the T20 title "Rain Target & Par Table" is now "DLS Target Calculator T20: Rain Target & Par Score". "Par table" / "par score table" stay with `/dls-par-score-table/` only.

- Audit 2 F26 (19 Oct): `/dls-calculator/` title drops "(ICC)", because the site is not an ICC product: now "DLS Calculator: Revised Target for Rain-Hit Matches | DLSCalc".
- Audit 2 F35 (19 Oct): four H2s answered another page's question and are now one or two sentences plus a link to the owner: `/dls-calculator/` "What is the DLS target when rain interrupts the chase?" and "How is a match decided if it cannot be finished?" (owner `/dls-method/`), `/net-run-rate-calculator/` "How do rain and DLS matches count in NRR?" (owner `/how-to-calculate-nrr/`), `/how-to-calculate-nrr/` "How do you convert overs for NRR?" (owner `/run-rate-calculator/`).
- Audit 2 F44 (19 Oct): strike rate title is now "Strike Rate & Batting Average Calculator (Cricket) | DLSCalc"; home, famous matches hub and strike rate descriptions cut to under 160 characters.
- Testing audit F53 (19 Oct): the 34-figure real-match accuracy table is one H2 on `/famous-dls-matches/` ("How close is it across 32 recent rain-hit matches?", `#real-matches`), the "dls calculator accuracy" owner. Other pages give the T20/ODI numbers in a sentence and link there; none repeats the table.

Checked and left as they are: the NRR calculator only summarises the formula and links to `/how-to-calculate-nrr/`; tool pages link to `/dls-method/` for the method; the run rate page's "How is run rate different from net run rate?" is a short contrast with a link.
