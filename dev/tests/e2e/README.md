# Live-site browser tests

Drive the live site like a visitor: every calculator, stoppage rows, the T20 slider, the par table,
the live tracker, the widget inside another site, WhatsApp and copy links, print sheets, the forms
with JavaScript off, offline mode and a slow 3G phone. Expected answers come from the engines in
this checkout (`assets/js/*-engine.js`), so run the suite from the commit that is live.

```sh
npm i --no-save playwright@1.56.1 && npx playwright install chromium   # once
node dev/tests/e2e/run.mjs                        # https://dlscalculator.com
node dev/tests/e2e/run.mjs https://staging.example --only=offline   # another site, some tests
```

- Phone tests use a Pixel 5 screen; page checks also run at 320px and 1280px.
- Every request carries a cache-busting `nc` parameter, so a stale LiteSpeed or CDN copy cannot pass.
- Tests tagged `known: 'F..'` check something an audit found wrong whose fix is not live
  (`/mnt/project-files/audit/`). They print `KNOWN` and do not fail the run; when the fix is live
  they print `FIXED`, and the tag should be removed.
- `.github/workflows/live-tests.yml` runs this and `dev/tests/run.sh` after every push to `main`
  (after waiting for the Hostinger deploy), every morning, and by hand.
- Chromium only: Safari (WebKit) and Firefox are not covered yet.
