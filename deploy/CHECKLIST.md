# Deploy checklist — restyle + categories

Run in this order on production. Steps 1–8 belong to every deploy from now on. Step 9 runs **once**, for the
restyle + categories release, and is never part of the regular deploy commands.

**Before the deploy**, on the machine that runs the gate: `npx playwright install chromium` (needed by `composer check`;
its last step, `test:browser`, fails on a fresh machine without it). CI installs it in `shared-ci.yml`.

1. **Environment.** `.env` has `APP_NAME=Tallypal` (not `TallyPal`: it shows in titles and mails, FP-16) and
   `APP_ENV=production` (the `/dev/*` routes are only registered outside production).
2. **Code + dependencies.** `composer install --no-dev --optimize-autoloader`, `npm ci`.
3. **Migrations.** `php artisan migrate --force`.
4. **Reference data.** `php artisan db:seed --class=ReferenceDataSeeder --force`. This upserts the currencies (by
   `code`) and the categories (by `slug`). It is idempotent: run it on every deploy. It never deletes a row.
5. **Assets + SSR bundle.** `npm run build` (also writes `bootstrap/ssr/ssr.mjs`).
6. **Restart SSR.**
   - VPS: `sudo systemctl restart tallypal-ssr` (unit: `deploy/inertia-ssr.service`, see `deploy/SSR_SETUP.md`).
   - Laravel Cloud: restart the `php artisan inertia:start-ssr` background process.
7. **Caches.** `php artisan optimize` (config, routes, events, views). Run it after step 1, so the route cache is
   built with `APP_ENV=production`.
8. **Smoke checks.**
   - `curl -s -o /dev/null -w '%{http_code}\n' https://<host>/dev/design-system` prints `404`.
   - Same for `/dev/error-page`: prints `404`.
   - The page `<title>` reads "Tallypal".
9. **One-off: wipe test activity** (this release only; production is a friends-only test environment).
   1. Add `ALLOW_ACTIVITY_RESET=true` to the environment (and `php artisan config:clear` if config is cached). Set it
      **only for the wipe**: without it the command refuses to run, with or without `--force`.
   2. `php artisan tallypal:reset-activity` prints the row counts it would delete and exits 1. Check them.
   3. `php artisan tallypal:reset-activity --force` deletes every transaction, tally invitation, membership and tally,
      keeps users, roles, tokens, currencies and categories, clears every user's Home cache and rotates their sidebar
      tokens.
   4. **Remove `ALLOW_ACTIVITY_RESET` from the environment right after** (and re-run `php artisan optimize`).
