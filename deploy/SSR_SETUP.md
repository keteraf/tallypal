# Inertia SSR — Production Setup

## Why SSR matters for SEO

Inertia renders pages client-side by default. Search engine crawlers (Googlebot) execute JavaScript, but a fully server-rendered HTML response is faster to index, eliminates flicker, and ensures correct Open Graph / meta tags appear in the first byte response — critical for social sharing cards.

The SSR port used by this application is **13714** (`config/inertia.php` → `ssr.url`).

## Pick your deployment target

- **Self-hosted (VPS, Forge, bare metal)** → follow [Self-hosted (systemd)](#self-hosted-systemd) below.
- **Laravel Cloud** → follow [Laravel Cloud](#laravel-cloud) instead; the systemd unit does not apply (no root access).

## Self-hosted (systemd)

### Prerequisites

- Node.js ≥ 18 (or Bun) available on the server
- The Inertia SSR bundle built: `npm run build` (Vite outputs `bootstrap/ssr/ssr.mjs`)
- PHP CLI available at `/usr/bin/php`

### Installation

1. Copy the unit file to systemd:

   ```bash
   sudo cp deploy/inertia-ssr.service /etc/systemd/system/tallypal-ssr.service
   ```

2. Edit the placeholders:

   ```bash
   sudo nano /etc/systemd/system/tallypal-ssr.service
   # Replace __APP_USER__ (e.g. www-data, forge)
   # Replace __APP_WORKDIR__ (e.g. /var/www/vhosts/tallypal)
   ```

3. Reload, enable, and start:

   ```bash
   sudo systemctl daemon-reload
   sudo systemctl enable tallypal-ssr
   sudo systemctl start tallypal-ssr
   ```

### Healthcheck

```bash
curl -s http://127.0.0.1:13714
```

A non-empty HTML response or an HTTP 200 confirms the SSR server is alive. You can also check:

```bash
sudo systemctl status tallypal-ssr
sudo journalctl -u tallypal-ssr -n 50
```

### After each deployment

Rebuild the SSR bundle and restart the service:

```bash
npm run build
sudo systemctl restart tallypal-ssr
```

> Note: the Laravel application will still serve pages even if the SSR service is down — it falls back silently to client-side rendering. Restart the service to restore SSR.

## Laravel Cloud

Laravel Cloud is a managed platform: there is no root access, no `systemctl`, and the systemd unit in `deploy/inertia-ssr.service` does not apply. Inertia SSR runs as a separate long-running **process** alongside the web app, the same mechanism used for queue workers.

> The exact field names and dashboard UI for declaring extra processes can change — verify against the current Laravel Cloud dashboard or docs before deploying. The steps below describe the intent, not the specific schema.

### Setup

1. **Build step** — ensure the deployment build command produces the SSR bundle. Typically:

   ```bash
   npm ci && npm run build
   ```

   Vite must output `bootstrap/ssr/ssr.mjs`. Verify after a deploy that this file exists in the deployed release.

2. **SSR process** — declare a long-running process in the Cloud dashboard (the section is usually called "Processes" / "Workers", same place where queue workers and Reverb are configured) running:

   ```bash
   php artisan inertia:start-ssr
   ```

   Set it to auto-restart on failure. Resource sizing: start small (the SSR process is mostly idle between requests).

3. **`ssr.url` in `config/inertia.php`** — `127.0.0.1:13714` works when web and SSR run on the same host. On Cloud, the SSR process may run on a separate instance reachable via an internal hostname. Check the dashboard for the SSR process's internal address and either:

   - update `config/inertia.php` to read the URL from an environment variable (e.g. `env('INERTIA_SSR_URL', 'http://127.0.0.1:13714')`), and
   - set `INERTIA_SSR_URL` in the Cloud environment to the internal hostname Cloud provides.

### Healthcheck

Use the Cloud dashboard's process logs to confirm SSR is running. There is no `systemctl status` equivalent — rely on:

- the process status indicator in the dashboard,
- application logs showing successful SSR renders (or fallback warnings),
- a manual smoke test: `curl -A "Googlebot" https://tallypal.it/` and confirm the response body contains the page meta tags inside `<head>` rather than only the empty Inertia shell.

### After each deployment

Cloud rebuilds and restarts processes automatically on deploy. No manual restart needed, provided the build step regenerates `bootstrap/ssr/ssr.mjs`.

> If you migrate away from Laravel Cloud in the future, the [Self-hosted (systemd)](#self-hosted-systemd) section above remains valid as the fallback path.
