# Deploy checklist — Laravel Cloud

Tallypal is hosted on Laravel Cloud. Two environments, one per branch:

| Environment | Branch   | URL                                      |
|-------------|----------|------------------------------------------|
| Production  | `master` | https://tallypal.it                      |
| Dev         | `dev`    | https://tallypal-dev-gujkpj.laravel.cloud |

Pushing a branch deploys its environment. Cloud builds and starts the release itself: there is no SSH session, no
`systemctl`, no manual `composer install` / `npm ci`. What you control is the environment variables, the build and
deploy commands in the dashboard, and the release order below.

**Before the first release**, on the machine that runs the gate: `npx playwright install chromium` (needed by
`composer check`; its last step, `test:browser`, fails on a fresh machine without it). CI installs it in
`shared-ci.yml`. The gate itself is `composer check; echo "exit: $?"` and must print `exit: 0`.

## 1. Environment variables

Set in **each** Cloud environment (dev and production), in the dashboard. A change to a variable takes effect on the
next deploy, so redeploy after editing.

| Variable                 | Value                                                                                             |
|--------------------------|---------------------------------------------------------------------------------------------------|
| `APP_NAME`               | `Tallypal` (not `TallyPal`: it shows in titles and mails, FP-16)                                  |
| `APP_ENV`                | `production` on **both** environments. The `/dev/*` routes (`routes/web.php`) are registered whenever `APP_ENV` is not `production`, so a dev environment left on another value exposes them. |
| `APP_URL`                | The environment's own URL from the table above (it signs the invitation links).                   |
| `ALLOW_ACTIVITY_RESET`   | **Unset.** Set to `true` only for the duration of a wipe (section 3), then remove it.             |

Anything else that differs from `.env.example` (mail transport and sender, `NIGHTWATCH_*`, and so on): **verify in
the Cloud dashboard**. Cloud injects database, cache, session and queue settings itself when the resources are attached
to the environment; do not copy those by hand. Compare the dashboard's variable list against `.env.example` for each
environment and make sure the two Cloud environments do not share mail credentials or Nightwatch tokens by accident.

Inertia SSR is on by default (`INERTIA_SSR_ENABLED`, `config/inertia.php`). How Cloud runs it is **verify in the Cloud
dashboard**: `deploy/SSR_SETUP.md` (Laravel Cloud section) describes the intent (a long-running
`php artisan inertia:start-ssr` process, `INERTIA_SSR_URL` pointing at it) and says the exact dashboard fields must be
checked. If the app and the SSR process do not share a host, set `INERTIA_SSR_URL` in the environment. If SSR is not
running, the app falls back silently to client-side rendering.

## 2. Deploy commands

Configured **once per environment, before the first push to it**, in the Cloud dashboard (where the build and deploy
commands live: **verify in the Cloud dashboard**). A push that lands before they are set deploys code against an
unmigrated database with no reference data. Cloud then runs them on every deploy.

Build commands:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build:ssr
```

`npm run build` alone does **not** produce the SSR bundle: `build:ssr` runs `vite build && vite build --ssr`, which
writes `bootstrap/ssr/ssr.mjs`. Use it if SSR is enabled on Cloud.

Deploy commands, in this order:

```bash
php artisan migrate --force
php artisan db:seed --class=ReferenceDataSeeder --force
```

`ReferenceDataSeeder` upserts the currencies (by `code`) and the categories (by `slug`). It is idempotent, never
deletes a row, and must run on every deploy so new reference data reaches both environments.

Caching (`php artisan optimize`): **verify in the Cloud dashboard** whether Cloud already runs it during the build. If
it does not, add it as the last deploy command, after the environment variables are final, so the route cache is built
with `APP_ENV=production`.

## 3. One-off commands

Run from the Cloud **command runner** of the environment concerned. They are never part of the deploy commands.

### Wipe test activity

Deletes every transaction, tally invitation, membership and tally; keeps users, roles, tokens, currencies and
categories; clears every user's Home cache and rotates their sidebar tokens. Used while the app is a friends-only test
environment.

1. Set `ALLOW_ACTIVITY_RESET=true` in the environment and redeploy so the value is live (config is cached). Without it
   the command refuses to run, with or without `--force`.
2. In the command runner: `php artisan tallypal:reset-activity`. It prints the row counts it would delete and exits 1.
   Check them.
3. In the command runner: `php artisan tallypal:reset-activity --force`.
4. **Remove `ALLOW_ACTIVITY_RESET` from the environment right after** and redeploy.

## 4. Release flow

1. **Gate.** `composer check; echo "exit: $?"` prints `exit: 0` on the branch to release.
2. **Merge into `dev` and push.** Dev deploys. Watch the deployment in the dashboard until it is green.
3. **Smoke-test the dev URL** (https://tallypal-dev-gujkpj.laravel.cloud):
   1. The home page loads and its `<title>` reads "Tallypal".
   2. Log in (or register and verify) and land on Home.
   3. Create a tally, then add a transaction: the currency and category pickers are populated (reference data seeded).
   4. Invite someone to a tally: the invitation email arrives with correct styling and a working signed link.
   5. View-source (or `curl -A "Googlebot" <url>/`) shows the page meta tags inside `<head>`, not only the empty Inertia
      shell (SSR is serving). If it does not, check the SSR process in the dashboard.

   Plus the dev-route check: `curl -s -o /dev/null -w '%{http_code}\n'` against `<url>/dev/design-system` and
   `<url>/dev/error-page` must print `404` for both.
4. **Mirror the configuration on production.** Compare dev and production side by side in the dashboard: environment
   variables (section 1), build commands and deploy commands (section 2), and the SSR process. Any change made on dev
   while testing is applied to production **before** the push.
5. **Merge `dev` into `master` and push `master`.** Production deploys. Watch the deployment until it is green.
6. **Repeat the smoke test on https://tallypal.it**, including the `/dev/design-system` and `/dev/error-page` 404 check.
