# Migration Plan — Mobile redesign

Ordered, session-sized phases to bring `Tallypal Mobile.html` into the live app
(Laravel 13 + Inertia v3 + Vue 3.5 + Tailwind v4 + shadcn-vue/reka-ui).

**Read `DESIGN_TOKENS.md` and `COMPONENT_INVENTORY.md` first.**

## ✅ Decided baseline (all gating questions resolved)

- **One system.** The editorial style is **removed completely**; the mobile system is the
  single design language app-wide (marketing, auth, admin, app).
- **Theme.** Default stays **`system`** (`.dark` class via `useAppearance.ts`). Both light and
  dark token sets ship.
- **Fonts.** Sans = Geist (headings too, **no serif**). Mono = **Geist Mono** (drop JetBrains
  Mono; drop Newsreader).
- **Breakpoint.** Chrome switches at **`md` (768px)**: `< md` = bottom tab bar + bottom sheets;
  `≥ md` = **left `ui/sidebar`** shell (rail md–lg, expanded ≥ lg).
- **Shell.** **One** responsive `WebLayout` (no separate mobile layout).
- **Add entry point.** `HomeMobileFab` + sticky bottom "Add" bar are **removed**, replaced by the
  BottomNav FAB (`< md`) and a sidebar/header primary button opening the Add-tx dialog (`≥ md`).
- **Overlays.** Add-tx / New-tally = bottom sheet `< md`, `ui/dialog` modal `≥ md`; full-page
  routes kept as deep-link fallback.
- **Colors.** Category/tally color is user-chosen + persisted (`c1..c6`), deterministic fallback
  (`DESIGN_TOKENS.md §7`).

After **every** session: `composer test`, `npm run test:lint && npm run test:types`, and
`vendor/bin/pint --dirty --format agent` for any PHP.

---

## Phase 1 — Tokens + global styles (1 session)

Goal: the mobile tokens are the canonical palette; existing components keep compiling via alias
remap + a compatibility shim. No page bodies changed yet.

- [x] In `resources/css/app.css`, **replace** the editorial palette in `:root`/`.dark` with the
      mobile tokens and re-point shadcn aliases + add the compatibility shim — copy `DESIGN_TOKENS.md §5.1`.
- [x] **Replace** the color block inside `@theme inline { … }` with the mobile mapping and keep
      the shadcn `--color-*` aliases (`DESIGN_TOKENS.md §5.2`). Verify `npm run build`.
- [x] Fonts: set `--font-mono` → **Geist Mono**, **remove** `--font-serif`. Update the font
      loader (Google `<link>` / `@font-face` — currently loads Newsreader + JetBrains Mono):
      add Geist Mono, drop Newsreader + JetBrains Mono.
- [x] Raise shadcn base `--radius` to `14px`; add `--radius-card`/sheet radius tokens.
- [x] Keep the `.dark`-class mechanism and `system` default (no `useAppearance` change).
- [x] Throwaway swatch/type specimen to eyeball both themes — superseded by the dev-only
      `dev/design-system` route (`resources/js/pages/dev/DesignSystem.vue`), guarded out of production.
      No app views edited.

Exit: `npm run build` clean; utilities `bg-surface-2`, `text-text-3`, `text-c4`, `border-line`,
`rounded-card`, `shadow-fab` resolve; existing pages still render (now in mobile palette via shim).

---

## Phase 2 — Base components (2–3 sessions)

Build/extend atoms + molecules from `COMPONENT_INVENTORY.md §A–B`, each with a tiny usage
example. No page migration.

**Session 2.1 — atoms**
- [x] `ui/button`: add `touch` size; consolidate icon buttons into `variant="outline" size="icon"`.
- [x] `ui/badge`: `pill`, `accent`, `pos`, `neg` (covers `TrendBadge`).
- [x] `ui/avatar`: `xs`/`sm` + `c1..c6` fallback colors; build `AvatarGroup`.
- [x] `Dot`, `MiniBar`, `CategoryGlyph`.

**Session 2.2 — interactive**
- [x] Restyle `ui/segmented-control` to mobile; confirm `v-model` API.
- [x] `NumberStepper` (± %, clamp) — NOT the reka `ui/stepper`.
- [x] `ChoiceChip` + `ChipGroup` (single & multi).
- [x] Filled variant for `ui/input`/`select`/`textarea`; `FormField` wrapper (uses `InputError`).

**Session 2.3 — content molecules**
- [x] `SectionHeader`, `BalanceHero`, `InOutCards`, `QuickActions`.
- [x] `TransactionRow` (extract from `HomeRecentTransactions` + `transactions/Index`), `TxDayHeader`.
- [x] `TallyListRow`, `TallyRailCard` + `TallyRail`, `SplitMemberRow`, `SettleBanner`, `DetailHeader`.

Exit: every new component renders in isolation, both themes; lint + types green.

---

## Phase 3 — Layout shell + navigation (1–2 sessions)

Goal: the responsive shell (bottom nav `< md` / sidebar `≥ md`) + sheet/dialog opener exist;
page bodies not yet rebuilt.

- [x] `BottomNav` + `BottomNavItem` (Home / Tallies / FAB / Stats / You), active from
      `usePage().url`, FAB opens Add-tx. `env(safe-area-inset-bottom)` padding. `< md` only.
      Stats is inert until its route lands (Phase 4.9); You → `settings/profile`.
- [x] Repurpose `ui/sidebar` + `AppSidebar` as the **app-wide `≥ md`
      shell** (Home/Tallies/Stats/You + admin when Super Admin), restyled to tokens; rail md–lg,
      expanded ≥ lg. Add its "Add transaction" primary button (opens dialog). (`AppSidebarLayout`
      superseded by `WebLayout`; left in place for Phase 5 removal.)
- [x] Merge into **one** responsive `WebLayout`: `< md` bottom nav + bottom padding, no top header
      on primary tabs; `≥ md` sidebar shell. Remove `HomeMobileFab` usage. (`AppLayout` now
      delegates to `WebLayout`, so admin + settings share the one shell.)
- [x] Bottom-sheet shell: extend `ui/sheet` (`side="bottom"` + grabber + rounded top + `max-h-[88%]`)
      → `ui/sheet/BottomSheet.vue`.
- [x] `useAddTransaction()` / `useNewTally()` opener → bottom sheet `< md`, `ui/dialog` `≥ md`
      (`ResponsiveModal` + `AppOverlays`), full-page route as fallback. Focus trap + scroll lock via
      reka. SSR-safe (`useMediaQuery`, cookie-seeded sidebar state). Placeholder bodies until Phase 4.7.

Exit: narrow viewport shows bottom nav; `≥ md` shows sidebar; FAB/button opens an (empty) sheet/dialog.

### Responsive strategy (resolved — mobile source authoritative for mobile)

| Pattern | Mobile (< md) | Tablet / Desktop (≥ md) |
|---|---|---|
| Primary navigation | Bottom tab bar + center FAB | Left `ui/sidebar` (rail md–lg, expanded ≥ lg) |
| Page container | full-bleed `px-5`, bottom-padded for nav | sidebar shell + centered content |
| Screen header | 28px **sans** `h1` + sub | **sans**, size scales up (no serif) |
| Balance | single big hero + In/Out cards | **single big hero** (+ In/Out beside), not a 3-cell grid |
| "Your tallies" | horizontal `TallyRail` | **grid** (Tallies-card look) |
| Tallies list | full-width `TallyListRow`s | **bordered card grid**, new tokens (1→2→3 cols) |
| Add-tx / New-tally | bottom sheet | **`ui/dialog` modal** (route = fallback) |
| Tally detail header | `DetailHeader` back-arrow | **breadcrumbs** (`Breadcrumbs.vue`) |
| Stats | dedicated tab/screen | new desktop layout in the sidebar shell |
| Quick actions | 2×2 card | **2×2 card** beside the hero (superseded by the Desktop design, F-024); set Add / Split / Settle / New tally (F-025) |
| Chart | @unovis (mobile-styled) | **@unovis** (same, unified) |

---

## Phase 4 — View-by-view migration

One checkbox per existing view, simplest → most coupled, batched to one session each. Every view:
swap hardcoded values for tokens/components, verify both themes + both breakpoints, update/author
tests. **All batches are in scope** (editorial removed → even mock-less screens are re-skinned).

**Batch 4.1 — settings ("You") & error (1 session)**
- [x] `pages/settings/Appearance.vue`
- [x] `pages/settings/Password.vue`
- [x] `pages/settings/Profile.vue`
- [x] `pages/ErrorPage.vue`

**Batch 4.2 — auth (1 session)** *(no mock — re-skin to tokens, touch targets)*
- [x] `pages/auth/Login.vue`
- [x] `pages/auth/Register.vue`
- [x] `pages/auth/ForgotPassword.vue`
- [x] `pages/auth/ResetPassword.vue`
- [x] `pages/auth/ConfirmPassword.vue`
- [x] `pages/auth/VerifyEmail.vue`

**Batch 4.3 — marketing site (3 sessions)**
*Design: `Tallypal Site.html`. Tokens → `DESIGN_TOKENS.md §8`; components → `COMPONENT_INVENTORY.md §H`.*

The landing page keeps **its own shell** — sticky marketing header + footer, no bottom nav, no
sidebar — so `Welcome.vue` moves to a new `MarketingLayout`, not `WebLayout`. `WebHeader.vue` has
no other consumer and is replaced outright. The design's only bottom nav is drawn *inside* the
phone mock.

**#M1 — marketing is a separate visual context.** Where the site and the app disagree (kicker vs
eyebrow, chrome blur, breakpoints), they stay separate: marketing keeps the site values, the app
keeps its own, and neither is retuned to match the other. All eight open questions are resolved
below.

*Session 4.3a — marketing foundation (shared pieces first)*
- [x] Tokens: add `DESIGN_TOKENS.md §8` to `app.css` — `--container-site`, display type scale,
      `--line-soft`, `--surface-inset` (per-theme), `--chrome-bg`/`--chrome-line`, `--glow-hero`,
      `--glow-band`, `--shadow-plan-hi`/`--shadow-float`/`--shadow-device`, marketing spacing,
      `--transition-reveal`/`--transition-tile`. Check both glows in **light** theme.
- [x] `ui/button`: `surface` variant (`.btn-g`) + `pill-lg` size (`.btn-lg`); confirm the
      `hover:bg-accent` mapping noted in §H5 before reusing `outline`/`ghost`.
- [x] `ui/badge`: `status` variant for the hero badge (12.5px + `--pos` dot with halo).
- [x] `MarketingContainer`, `MarketingSection`, `MarketingSectionHead` (kicker + `h2.sec-h` +
      `.sec-p`), `CheckList` — all used by three or more sections.
- [x] `v-reveal` directive: `IntersectionObserver` + `(i mod 3) × 70ms` stagger, **SSR guard**,
      `prefers-reduced-motion` branch.

*Session 4.3b — shell, hero, how-it-works*
- [x] **Marketing shell/nav:** `MarketingLayout` + `MarketingHeader` (replaces `WebHeader.vue`) —
      sticky blurred chrome (site values, #M1), brand + Beta tag, anchor nav, `ThemeToggle`,
      Log in + Get Tallypal. **Signed-in state keeps today's behavior** (#M5): app nav links +
      avatar dropdown, restyled; no redirect away from `/`.
- [x] **Hamburger** (#M4): below `880px` the anchors collapse into an icon button opening
      `ui/sheet` `side="left"` — port the sheet already in `WebHeader.vue` (anchors + app nav +
      user block) instead of writing a new one.
- [x] **Footer:** `MarketingFooter` — rewrite `WelcomeFooterSection.vue` (brand + tagline +
      three link columns + mono bottom bar).
- [x] **Hero:** `MarketingHero` — rewrite `WelcomeHeroSection.vue`: glow, `StatusBadge`, display
      `h1`, lede, CTA pair + fine print. Status badge ships with user-approved copy
      ("Multi-currency · Web app") instead of the mock's false iOS/Android claim.
- [x] **Stats:** build `ProofStats` and **delete `WelcomeStatsSection.vue`** (the design has no
      standalone stats band). **Do not render it** (#M7): the design's figures are invented, so the
      hero ships without the proof row until real numbers exist.
- [x] **Device mock:** `MarketingDeviceFrame` = frame + status bar + a **static screenshot** of
      `/home`, one per theme, captured at 2× and served from `public/` (#M6). No app components
      inside. `FloatingStatCard` stays DOM over the image; floats hidden below `820px`.
      Screenshots captured from a throwaway seeded demo account (created and deleted within this
      session) at `public/marketing/home-light.png` / `home-dark.png`.
- [x] **How it works:** new section (`WelcomeHowItWorksSection.vue`), `StepCard` ×3. No equivalent
      existed before. Copy avoids the mock's unverified receipt-scanning/OCR claim (not an
      implemented feature); the other two steps describe real behavior (per-tally split rules,
      `GetTallyDebtsAction`'s debt-simplification settle).

*Session 4.3c — features, quotes, pricing, faq, cta*
- [x] **Features:** `FeatureBento` + `FeatureTile` — rewrite `WelcomeFeaturesSection.vue`.
      Tile demos reuse `SplitMemberRow` + `SettleBanner`, `ui/chart` (@unovis) + `Dot`,
      `ChipGroup`, `CheckList` (§H13). **Ships 4 tiles (2×2 w3 rows), not the source's 5** —
      per #M7, "Recurring, no nagging" and the "Daily ECB rates" line have no backend behind
      them (grepped for recurring-transaction and FX/exchange-rate code; none exists), so
      that tile is dropped rather than shipped as an invented claim. The privacy tile keeps
      only the two claims that are actually true (`TallyPolicy::view` restricts a tally to
      its owner/members; `DeleteUserAccountAction` is real self-service deletion) and drops
      end-to-end encryption, EU hosting and CSV/PDF export, none of which exist. The
      multi-currency tile keeps the true part (each transaction keeps its own currency) and
      drops the FX-conversion claim. `TransactionRow` wasn't needed once "Recurring" was cut.
- [x] **Quotes:** build `QuoteCard`, but **leave the "In use" section out of the page** (#M7) —
      the three testimonials are invented and cannot ship attributed to named people.
- [x] **Pricing:** `PricingPlanCard` ×2 — rewrite `WelcomePricingSection.vue` (two separate cards,
      the second highlighted by border + `--shadow-plan-hi`).
- [x] **FAQ:** `FaqAccordion`/`FaqItem` on native `<details>`, two-column grid — rewrite
      `WelcomeFaqSection.vue`.
- [x] **CTA band:** `CtaBand` with `--glow-band`, **one** primary button → `register` (#M8: the
      "Open the live demo" CTA is dropped, `#demo` deleted, `#signup`/`#login` → the real routes).
- [x] i18n: `welcome.how`, `welcome.cta_band`, hero `badge`, and per-tile feature keys already
      existed (parallel in `en.ts`/`it.ts`, verified key-for-key). **Not added**: `welcome.quotes`
      and hero `proof` — both stay unrendered per #M7 (invented testimonials/figures), so no copy
      keys exist for them yet. Removed orphaned pre-redesign leftovers from `welcome` in both
      locales: top-level `title`/`my_tallies`/`app_name`/`slogan` (superseded by `AppHead`'s
      backend-driven SEO title), `hero.eyebrow`, `hero.ledger.*` (dead since the device mock became
      a static screenshot, #M6), and `stats.*` (dead since `ProofStats` isn't rendered). The
      `hero.title_lead`/`title_em` pair is **not** orphaned — `MarketingHero.vue` still renders it
      as the accented `<em>` word — so it stays, matching every other page's title_lead/title_em
      pattern (settings, tallies, dashboard, error pages), all confirmed still in use.
- [ ] **Claims audit (#M7) — blocking.** Every factual line in the design is untrue as written.
      Strip or replace: the hero badge ("11 currencies · iOS, Android, web" — there is no mobile
      app), the proof figures, the testimonials, the privacy tile (end-to-end encryption, EU
      hosting), "daily ECB rates", the plan limits, and the FAQ answers that assert features.
      Ship only lines the user confirms. Re-check `AppHead`/`JsonLd` copy and keep
      `tests/Feature/Seo/SeoMetadataTest.php` green.

*Marketing responsive strategy* — the source is **desktop-first** (all its media queries are
`max-width`) and stops adapting below `520px`; the app system is mobile-first at `md`. Per **#M1**
the marketing page **keeps its own breakpoint ladder** — do not force it onto the app's `md`
switch. Implement the source values as arbitrary min-width variants, using `sm`/`md`/`lg` only
where they coincide:

| Source query | Pattern | Implementation |
|---|---|---|
| `≤ 880px` | nav links + Beta tag → hamburger (#M4) | `hidden min-[880px]:flex` / sheet trigger `min-[880px]:hidden` |
| `≤ 900px` | hero grid 2-col → 1-col | `min-[900px]:grid-cols-[1.05fr_.95fr]` |
| `≤ 900px` | bento 6-col → 2-col, all tiles span 2 | `grid-cols-2 min-[900px]:grid-cols-6` + per-tile spans |
| `≤ 820px` | floating cards hidden | `hidden min-[820px]:block` |
| `≤ 820px` | section head 2-col → 1-col | `min-[820px]:grid-cols-2 min-[820px]:items-end` |
| `≤ 760px` | footer 4-col → 2-col | `grid-cols-2 min-[760px]:grid-cols-[1.6fr_repeat(3,1fr)]` |
| `≤ 520px` | device `330px` → `min(300px,86vw)` | `w-[min(300px,86vw)] min-[520px]:w-[330px]` |
| `auto-fit minmax()` | steps 240 / quotes 260 / prices 280 / faq 320 | keep intrinsic, no query |

Everything else clamps (`clamp()` on every section padding and display size), so there is no
second layout to design. Below `520px` the page is a single column: badge, heading, lede, CTAs,
then the device mock.

*✅ RESOLVED — the eight marketing questions*

| # | Question | Decision |
|---|---|---|
| M1 | Eyebrow vs kicker, chrome blur, breakpoints | **Keep marketing separate.** No alignment with the app system in either direction. `--text-kicker` is marketing-only; the header keeps `blur(22px)`/78%; the page keeps its own breakpoint ladder. |
| M2 | Translucent chrome | Covered by M1 — site header and app tab bar stay different. |
| M3 | Marketing breakpoint | Covered by M1 — the site's 880/900/820/760/520 ladder stands; `md` (768) is an app rule only. |
| M4 | Nav below `880px` | **Add a hamburger** — icon button → `ui/sheet` `side="left"` with the anchors (plus app nav + user block when signed in). Port the sheet already in `WebHeader.vue`. |
| M5 | Signed-in visitor at `/` | **Leave as it is** — today's behavior carries over: app nav links + avatar dropdown in the header, no redirect. |
| M6 | How the phone mock is built | **A screenshot.** Static image of `/home`, one per theme, 2×, in `public/`; frame, status bar and floating cards stay DOM. No scaled Vue replica. Re-capture when Home changes. |
| M7 | Are the page's claims true | **No — none of them.** Nothing unverified ships: no proof figures, no testimonials, no "iOS, Android, web", no encryption/EU-hosting/ECB claims, no unconfirmed plan limits or FAQ feature answers. Components get built; claim-bearing sections stay out of the page until the user supplies true copy. |
| M8 | CTA targets | **No demo** — drop the "Open the live demo" button and the `#demo` anchor; the band ships one primary CTA. `#signup` → `register`, `#login` → `login`. |

*Copy the user still owes before the page can go live* (all from #M7): ~~the hero badge line~~
(resolved in 4.3b — "Multi-currency · Web app"), the five feature-tile bodies, the privacy list,
the two plan feature lists with real limits, and the six FAQ answers. Sections built but
deliberately unrendered until then: `ProofStats`, the "In use" testimonials wall.

**Batch 4.4 — Home (1 session)**
- [x] `pages/Home.vue` (+ all `home/sections/*`) → `BalanceHero` + `InOutCards` + `QuickActions`
      + `TallyRail`(< md)/grid(≥ md) + `TransactionRow`s. Remove `HomeMobileFab`.

**Batch 4.5 — Tallies list (1 session)**
- [x] `pages/tallies/Index.vue` → `TallyListRow` < md, restyled card grid ≥ md; empty state.

**Batch 4.6 — Tally detail (1 session)**
- [x] `pages/tallies/transactions/Index.vue` → `DetailHeader`(back < md / breadcrumb ≥ md) +
      `BalanceHero`/`InOutCards` + `SettleBanner` + `TransactionRow`s. `TxDayHeader` skipped:
      the mock's tally-detail screen doesn't group by day.
- [x] **"Split so far" section now ships** (previously deferred): new `GetTallySplitAction`
      computes per-member expense amount + percentage per currency; `TransactionController::index()`
      exposes it as a `splits` merge-prop; `Index.vue` renders `SplitMemberRow` blocks between
      the settle banner and the transactions list.

**Batch 4.7 — Create flows (1 session)**
- [x] `pages/tallies/Create.vue` → New-tally sheet/dialog (`NewTallyForm`, shared by
      `AppOverlays`' `ResponsiveModal` and the full-page fallback): `FormField` + filled
      `Input`/`Textarea` for name + description + **colour/icon `ChipGroup` picker** (now ships
      — `tallies.color`/`glyph` columns added, threaded through Request → DTO → Action).
      **Deliberately not built**: Tally Type (Shared/Private) — `COMPONENT_INVENTORY §E5`
      resolved it as "no switch needed now"; per-tally default currency — there is no per-tally
      currency concept (currency lives on each transaction); members `SplitEditor` — members
      join only via the email-invitation flow, not at creation time.
      **2026-10-11 owner decisions (VERIFICATION.md):** Type and members stay out for good (B-12
      CUT: New tally always creates a solo tally; invites/splits via Share). A per-tally
      **default** currency is now BUILD (B-11): it only prefills new transactions, never restricts
      or converts; each transaction keeps its own currency.
- [x] `pages/tallies/transactions/Create.vue` → Add-tx sheet/dialog body (`AddTransactionForm`,
      shared like `NewTallyForm`): `AmountInput` hero now shows the currency **symbol** (`€`)
      instead of the ISO code (`EUR`) — `TransactionController::create()` and `::edit()` now
      select `symbol`; `AddTransactionForm` and `transactions/Edit.vue` updated accordingly.
- [x] **Global Add-tx FAB now functional**: two new web JSON endpoints (`GET tallies/picker`
      and `GET tallies/{tally}/transactions/form-data`) + `AppOverlays.vue` rewritten with a
      tally-picker step (auto-skipped when the user has exactly one tally, empty-state when
      zero) using Inertia v3 `useHttp` for both fetches.

**Batch 4.8 — Edit flows (1 session)**
- [x] `pages/tallies/Edit.vue` — no mock screen for this view; followed the `Create.vue`/
      `FormField` form pattern instead. Danger-zone delete block reuses the `DeleteUser.vue`
      styling (owner-only), replacing the old fixed bottom bar, which would have stacked on
      top of the Phase-3 `BottomNav`.
- [x] `pages/tallies/transactions/Edit.vue` — no mock screen; reused the `AddTransactionForm`
      field set/layout (AmountInput hero, type SegmentedControl, title, category ChipGroup,
      currency+date, party, description) inline with `useForm`, seeded from the transaction's
      current values. Same PATCH route/payload/validation as before.

**Batch 4.9 — Stats — NEW view (1 session + backend)**
- [x] **New** `pages/Stats.vue` + route + controller (the mobile "Stats" tab has no current
      page). Range `SegmentedControl` (Week/Month/Year) + @unovis chart + category breakdown.
      Reuses `home/types.ts` `WeekHistory`/`CategoryBreakdown` shapes; `StatsService` +
      3 new `TransactionStatsRepository` methods (`expenseTotal`, `dailyHistory`,
      `monthlyHistory`) add server-side week/month/year aggregation — month reuses the
      existing 12-week `weeklyHistory` trend. `BottomNav`/`AppSidebar` Stats tabs are no
      longer inert.

**Batch 4.10 — Admin (1 session)**
- [x] `pages/Dashboard.vue` (+ `AppSidebar*`, `layouts/app/*`) — restyle to tokens; the sidebar
      is now the shared desktop shell. Stat grid rebuilt as `rounded-card bg-surface` cards
      (`InOutCards`/`QuickActions` convention) instead of the old bordered table; charts
      restyled to `--c1`/`--c2`/`--c3` (was `--brand`/`--ink-2`). Also fixed a leftover
      `border-rule-2` shim class in `AppSidebarHeader.vue` → `border-line`.

**Batch 4.11 — Desktop-design verification fixes (ongoing)**
- Tracked in `VERIFICATION.md`. Order of work: its **Build first** list (each new component gets
  its own run before the findings that use it). Tokens added 2026-10-11 are already wired in
  `app.css`.

---

## Phase 5 — Cleanup (1 session)

- [x] **Delete editorial tokens + shim:** remove `--paper*`, `--ink*`, `--rule*`, `--brand*`,
      `--brand-soft`, `--font-serif`, old `--radius: 0.25rem`, and the §5.1 compatibility shim.
- [x] **Purge editorial references in views:** grep for `font-serif`, `bg-paper*`, `text-ink*`,
      `border-rule*`, `text-brand`, `bg-brand-soft`, inline `style="font-style: italic; color: var(--brand)"`,
      serif `text-[clamp(...)]` headers — replace with mobile tokens/utilities. Purged 13 files
      (`ui/card`, `ui/dialog`, `ui/input`, `ui/select`, `ui/textarea`, `ui/segmented-control`,
      `ThemeToggle`, `TallyShareDialog`) to `border-line`/`bg-surface-2`/`text-accent`/`bg-accent`;
      dropped `font-serif`(+`italic`) in favor of sans; `bg-brand-soft` → `bg-accent/14` opacity
      modifier; the dead `bg-pos text-paper` branch in `SegmentedControl` → `text-accent-ink`
      (mirrors the existing `--destructive-foreground` reuse of `--accent-ink` as the generic
      "foreground on solid color" token). Deleted the shim block from both `@theme inline` and
      `:root` in `app.css` now that nothing references the editorial names.
- [ ] **Remove replaced components:** `HomeMobileFab`; inline avatar/icon-button markup folded
      into the kit. ~~reka `ui/stepper` if unused after `NumberStepper`~~ done — confirmed zero
      imports anywhere in the app and deleted (`resources/js/components/ui/stepper/`).
- [ ] **Fonts:** confirm Newsreader + JetBrains Mono no longer loaded anywhere.
- [ ] **Hardcoded-value sweep:** raw hex/oklch, arbitrary `text-[NNpx]`/`rounded-[NNpx]`/`px-[..]`
      → tokens/utilities.
- [ ] Confirm **no `tailwind.config.js`** exists (v4 stays config-in-CSS).
- [ ] Final: `composer test`, `npm run test:lint`, `npm run test:types`, visual QA both themes,
      `< md` and `≥ md`, locale (`vue-i18n`) spot-check.

---

## Cross-cutting dependencies (accepted work, flagged for backend coordination)

- **Backend `color` columns** for tallies + categories (decision #5) — migration + create/edit
  endpoints; frontend uses deterministic fallback until then.
- **Stats route/controller + aggregation** (Batch 4.9) — week/month/year rollups; new feature test.
- **Data the mock shows that controllers may not yet return:** month-over-month delta %,
  settle "who owes whom" amount, per-tally In/Out split, running daily totals. Verify each
  against existing controllers / `home/types.ts` before building the UI; extend props as needed.
- **i18n:** all mock copy is hardcoded English; route every string through `vue-i18n`
  (`home.*`, `tallies.*`, `transactions.*`, `navigation.*`), adding keys for new screens (Stats,
  bottom-nav labels, empty/error states).
- **SSR:** bottom sheets / client-only chrome must guard `window` (as `useAppearance` does).
- **Testing gates:** `composer test` includes Pest mutation ≥ 90% + PHPStan; front-end work must
  keep the PHP suite green and add tests for the new Stats route.
- **Empty / loading / error states** (decision #20): design + build for Home, Tallies, Stats,
  Tally-detail; use `ui/skeleton` for Inertia v3 deferred props.
- **Marketing content** (Batch 4.3, #M7): the design's claims — user counts, an App Store rating,
  named testimonials, platform, encryption and hosting statements, plan limits — are **not true**.
  None of them ship. The user supplies real copy line by line; until then the claim-bearing
  sections stay out of the page. Also needed: two `/home` screenshots (light + dark) for the hero
  device mock (#M6), re-captured whenever Home changes.
