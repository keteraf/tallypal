<laravel-boost-guidelines>
=== .ai/custom-guidelines rules ===

# Custom Guidelines

- At the end of every task run the command `composer test` and fix any errors that arise.
- When you need to check/fix lint or phpstan error or run tests you MUST use these scripts in the composer.josn file: 
  ```json
    {
        "lint": "pint",
        "refactor": "rector",
        "test:lint": "pint --test",
        "test:refactor": "rector --dry-run",
        "test:types": "phpstan analyse",
        "test:unit": "pest --colors=always --parallel @additional_args --coverage --min=90",
        "test:mutate": "pest --colors=always --parallel @additional_args --mutate --min=90",
        "test": [
          "@php artisan config:clear --ansi",
          "@test:lint",
          "@test:refactor",
          "@test:types",
          "@test:unit",
          "@test:mutate"
        ]
    }
    ```


=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context
This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.


## Skills Activation
This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

# Laravel Boost

## Tools
- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)
- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.
### Search Syntax
1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules
- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan
- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker
- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`


=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.


=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.


=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.


=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.


=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.


### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.



## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.


## URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.


## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.


=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).


=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.


=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.


=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>

## Domain rule: currencies (owner decision, O-04)

Tallies hold transactions in several currencies. These rules are mandatory for every total,
balance, chart and KPI:

- Amounts in different currencies are **never added together and never converted**.
- **Per-tally figures** list one entry per currency (the O-02 `monthly_totals[]` pattern:
  `{amount, currency_symbol, currency_code, decimals}`, most-used currency first).
- **Cross-tally aggregates** (Home balance hero, In/Out, sparkline, By category; Stats chart,
  KPIs, categories) are computed in the user's **primary currency** only: their default currency
  (`users.default_currency_id`, B-10) if set, otherwise their most-used currency
  (`TransactionStatsRepository::primaryCurrency()`). When other currencies have activity in the
  same range, show **one quiet line** below the figure with each other currency's total in its
  own symbol ("Other currencies: $ 274.50", `common.other_currencies`), using existing tokens and
  components. No conversion.
- Every month or range filter uses **`transaction_date`**, never `created_at`.
- Home cache keys are versioned (`HomeCacheService::KEY_VERSION`): bump it whenever the shape of
  a cached value changes, instead of clearing the cache on deploy.

## Database & migrations

- Production is **PostgreSQL 18.6**.
- Migrations never use models, model constants, enum cases or any `App\` class. Only literal table,
  column and value names. `tests/Architecture/MigrationsUseLiteralNamesTest.php` fails on any `App\`
  reference and names the file and line.
- Where PostgreSQL and SQLite differ, write explicit per-driver SQL, branching on `DB::getDriverName()`.
- Never rely on implicit row order. Always use `orderBy`.
- The PHP and browser tests run on PostgreSQL 18 (mutation testing stays on SQLite). Locally it is a
  podman container, `tallypal-pg-test` (image `postgres:18`), on `127.0.0.1:54329`, user and password
  `postgres`, database `tallypal_test`, its data on a tmpfs at `/var/lib/postgresql` (wiped on stop):
  - `composer db:pgsql:up` starts it and waits until it accepts connections; it does nothing if the
    container is already running.
  - `composer db:pgsql:down` stops it (the container is `--rm`, so it is removed too).
  - Both act on that one container only. Never kill or restart any other process or container.

## Design system

The new UI is being migrated from the Claude Design source. The design system is
documented in three files — read the relevant one before doing any UI work:

- [Design tokens](docs/design/DESIGN_TOKENS.md) — every color, font, size, spacing,
  radius, shadow, transition as a named CSS custom property, plus the Tailwind **v4**
  `@theme` mapping (this repo has no `tailwind.config.js`).
- [Component inventory](docs/design/COMPONENT_INVENTORY.md) — every reusable UI pattern,
  its states, the existing Vue/shadcn-reka component it replaces or extends (or "new"),
  and its props/slots.
- [Migration plan](docs/design/MIGRATION_PLAN.md) — the ordered, session-sized phases.

Rules — these are mandatory for all UI work:

- All UI uses the design tokens and the base components. **No view may hardcode** colors,
  font sizes, spacing, radii, or shadows — use the tokens (Tailwind utilities generated from
  `@theme`, e.g. `bg-surface-2`, `text-text-3`, `rounded-card`) or a base component.
- Before building UI, check `COMPONENT_INVENTORY.md` for an existing/base component to reuse
  or extend; only create a new component when the inventory says "new".
- Tailwind is **v4, config-in-CSS** — extend `resources/css/app.css` (`@theme inline` + the
  `:root`/`.dark` custom properties). Never add a `tailwind.config.js`.
- The app design source (`Tallypal Mobile.html`) is **mobile-only**, but the decisions are
  settled: **one system** (the editorial style is removed), default theme **`system`**, **no
  serif** (Geist + Geist Mono), chrome switches at **`md` (768px)** — bottom tab bar below, left
  `ui/sidebar` above. Follow the responsive strategy table in `MIGRATION_PLAN.md` (Phase 3) for
  every pattern's tablet/desktop behavior rather than inventing your own.
- The **marketing page** has its own source (`Tallypal Site.html`, same palette and fonts):
  tokens in `DESIGN_TOKENS.md §8`, components in `COMPONENT_INVENTORY.md §H`, build order and the
  resolved decisions (#M1–#M8) in `MIGRATION_PLAN.md` Batch 4.3. It keeps a standalone header and
  footer and does **not** use the app shell — no bottom nav, no sidebar. It is a **separate visual
  context**: its kicker, chrome blur and breakpoint ladder deliberately differ from the app's, so
  do not "fix" either side to match the other.

## Verification

- Start the dev stack: `composer run dev` (runs the server, queue listener, logs, and Vite
  together). Base URL: `http://127.0.0.1:8000`.
- Seed demo data — safe to re-run, it tears down and rebuilds its own records each time:
  `php artisan db:seed --class=DemoSeeder`.
- Demo logins:
  - `demo@tallypal.test` / `password` — populated account. Six tallies (a shared household
    split 60/40, a solo tally, a shared weekend trip split 50/50 in USD, an empty tally, a
    mixed EUR/USD city break, and a three-member ski cabin where one member is even and one
    still owes), months of transactions spanning last-12-weeks/this-month/this-year, and a
    pending invitation. There is no settlement record: repayments can't be recorded yet (they
    will get a dedicated flow). Use it to check every screen that has data.
  - `demo-empty@tallypal.test` / `password` — freshly registered account with no tallies. Use it
    to check every empty state.
- Any run that creates, edits or deletes data through the browser re-runs DemoSeeder at the end.
- **No UI item may be marked verified without a screenshot of it.**
- A finding is FIXED only after a side-by-side comparison with its .verify/design/ reference.
- Done means `composer check; echo "exit: $?"` prints `exit: 0`. Run it exactly like that and quote the `exit:` line together with the final summary lines (test count, coverage, mutation score) in your report.
- `npx playwright install chromium` (needed by `composer check`): run it once on a fresh machine; `composer check` ends with the
  Pest browser tests (`test:browser`) and fails without that Chromium. CI installs it in `shared-ci.yml`.
- Per-class mutation scores (`--class`) are informational only. The gate is the full `composer test:mutate` run.
