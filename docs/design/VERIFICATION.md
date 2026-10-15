# Verification — `Tallypal Desktop.html` vs the app

Verification pass (batches 1–7; 5–7 added 2026-10-11), followed by an incremental fix pass. Each fix session picks
the next 1–3 unresolved `VISUAL`/`MISSING-UI`/frontend-only `DEAD-UI` findings, re-screenshots the
affected viewport(s), and logs the result in **Fixes applied** below and inline on the finding.

## Fixes applied

| Date | F-ids | Files | Viewport(s) re-shot |
|---|---|---|---|
| 2026-09-15 | F-003, F-011 | `resources/js/components/NavMain.vue` | `desktop/hover-sidebar-nav.png` |
| 2026-09-15 | F-041 | `resources/js/components/ui/transaction-row/TransactionRow.vue`, `resources/js/pages/home/sections/HomeRecentTransactions.vue`, `resources/js/pages/tallies/transactions/Index.vue` | `desktop/hover-tx-row.png` |
| 2026-09-15 | F-001, F-002, F-008 | `resources/js/components/AppSidebar.vue`, `resources/js/components/NavMain.vue`, `resources/js/components/ui/sidebar/index.ts`, `resources/js/components/ui/sidebar/utils.ts`, `resources/js/types/index.d.ts`, `resources/css/app.css` | `desktop/home-0.png`, `desktop/hover-sidebar-nav.png` |
| 2026-09-15 | F-031, F-040, F-064 | `resources/js/pages/home/sections/HomeYourTallies.vue`, `resources/js/pages/home/sections/HomeCategoriesBreakdown.vue`, `resources/js/pages/tallies/transactions/Index.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/home-0.png`, `desktop/home-1.png`, `desktop/detail-0.png`, `mobile/home-1.png` |
| 2026-09-15 | F-012 | `resources/js/components/BottomNav.vue`, `resources/js/components/BottomNavItem.vue`, `resources/js/layouts/WebLayout.vue` | `mobile/home-0.png` |
| 2026-09-15 | F-013 | `resources/js/layouts/WebLayout.vue` | `desktop/kbd-shortcut-n.png` (interaction, no design ref) |
| 2026-09-15 | F-014, F-017, F-018 | `resources/js/pages/home/sections/HomeHeader.vue` | `desktop/home-0.png`, `mobile/home-0.png` |
| 2026-09-15 | F-044, F-045 | `resources/js/pages/tallies/Index.vue` | `desktop/tallies-0.png`, `mobile/tallies-0.png` |
| 2026-09-15 | F-030, F-035 | `resources/js/components/ui/tally-rail/TallyRailCard.vue`, `resources/js/components/ui/tally-rail/TallyRail.vue`, `resources/js/pages/home/sections/HomeYourTallies.vue` | `desktop/home-0.png`, `mobile/home-0.png`, `mobile/home-rail-end.png` |
| 2026-09-15 | F-042 | `resources/js/pages/Home.vue`, `resources/js/pages/home/types.ts` (deleted `HomeWeeklyHistory.vue`, `HomeSharedTally.vue`) | `desktop/home-1.png`, `mobile/home-1.png` |
| 2026-09-15 | F-079 | `resources/js/pages/stats/sections/StatsSpendingChart.vue` | `desktop/stats-bar-hover.png` |
| 2026-10-10 | F-004 (primary tabs), F-005 · F-006 blocked (missing `9px` mark-radius token) | `resources/js/layouts/WebLayout.vue`, `resources/js/components/AppSidebar.vue`, `resources/js/pages/Home.vue`, `resources/js/pages/Stats.vue`, `resources/js/pages/tallies/Index.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/home-0.png`, `desktop/tallies-0.png`, `desktop/stats-0.png`, `desktop/home-light-0.png`, `desktop/home-rail-0.png`, `mobile/home-0.png` |
| 2026-10-10 | F-009, F-043 (header; sub shows ledger count only) · F-007 blocked | `resources/js/components/AppSidebar.vue`, `resources/js/pages/tallies/Index.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/tallies-0.png`, `mobile/tallies-0.png` |
| 2026-10-10 | F-019, F-020, F-023 · F-021 blocked (missing spark-bar radius token) | `resources/js/components/ui/balance-hero/BalanceHero.vue`, `resources/js/components/ui/in-out-cards/InOutCards.vue`, `resources/js/pages/home/sections/HomeBalanceGrid.vue` | `desktop/home-0.png`, `mobile/home-0.png` |
| 2026-10-10 | F-024 (card + 2×2 layout, beside hero; action set/subs open → F-025..F-028) | `resources/js/components/ui/quick-actions/{QuickActions.vue,index.ts}`, `resources/js/pages/home/sections/{HomeQuickActions,HomeBalanceGrid}.vue`, `resources/js/pages/Home.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/home-0.png`, `mobile/home-0.png` |
| 2026-10-11 | F-038 (rows no longer wrap mid-string) · F-036, F-039 blocked (no documented in-card header component) · F-037 blocked (no category→glyph-character mapping) | `resources/js/components/ui/transaction-row/TransactionRow.vue`, `resources/js/pages/home/sections/HomeRecentTransactions.vue` | `mobile/home-2.png`, `desktop/home-1.png` |
| 2026-10-11 | F-026, F-072 · F-070 (header; Filter → B-09) | `resources/js/pages/home/sections/HomeQuickActions.vue`, `resources/js/pages/Stats.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/home-0.png`, `desktop/stats-0.png`, `mobile/stats-0.png` |
| 2026-10-11 | F-048 (full-width rows on desktop too; type pill/avatars/tx count/status → B-06/B-04/B-05, DO-03) | `resources/js/pages/tallies/Index.vue`, `resources/js/components/ui/tally-list-row/TallyListRow.vue` | `desktop/tallies-0.png`, `mobile/tallies-0.png` |
| 2026-10-11 | F-056, F-055 (mobile sticky bar removed) · F-073, F-074 (plot height + card no longer stretched; mono axis, bar radius, 6-tick axis blocked: no `--text-axis`/bar-radius token wired in `app.css`) | `resources/js/pages/tallies/transactions/Index.vue`, `resources/js/pages/stats/sections/StatsSpendingChart.vue`, `resources/js/pages/Stats.vue` | `mobile/detail-0.png`, `desktop/stats-0.png`, `mobile/stats-0.png` |
| 2026-10-11 | F-052, F-053, F-061 (settle banner: no Remind → DO-05; no second line; Edit kept as an icon button so tally edit isn't stranded) | `resources/js/pages/tallies/transactions/Index.vue`, `resources/js/components/ui/settle-banner/SettleBanner.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/detail-0.png`, `mobile/detail-0.png` |
| 2026-10-11 | F-029 (verified, already covered by F-024), F-057, F-060 (one card, per-currency blocks, inline In/Out), F-074, F-076 (top 5 + Other; placement beside a KPI stack/All activity → B-09) | `resources/js/pages/tallies/transactions/Index.vue`, `resources/js/pages/Stats.vue`, `resources/js/pages/stats/sections/{StatsSpendingChart,StatsCategoryBreakdown}.vue`, `resources/js/components/ui/mini-bar/MiniBar.vue`, `resources/js/i18n/locales/{en,it}.ts` | `mobile/home-0.png`, `desktop/detail-0.png`, `mobile/detail-0.png`, `desktop/stats-0.png`, `mobile/stats-0.png` |
| 2026-10-11 | F-089 (every dialog fits the viewport: header fixed, body scrolls, actions stay visible; Share dialog too) | `resources/js/components/{ResponsiveModal,tallies/TallyShareDialog,tallies/AddTransactionForm,tallies/NewTallyForm}.vue`, `resources/js/components/ui/{dialog/DialogContent,sheet/BottomSheet}.vue` | `desktop/modal-addTx-form-0.png` (1440×900, 1280×720), `mobile/sheet-addTx-0.png` |
| 2026-10-11 | F-088 (Sign out on You, mobile + desktop; row layout waits on F-085) | `resources/js/pages/settings/Profile.vue`, `resources/js/i18n/locales/{en,it}.ts` | `mobile/profile-signout.png` |
| 2026-10-11 | O-03 (tally name `max:50` everywhere + column widened) | `database/migrations/*_widen_name_on_tallies_table.php`, `app/Http/Requests/Tally/{Store,Update}TallyRequest.php`, `resources/js/{components/tallies/NewTallyForm,pages/tallies/Edit}.vue`, `tests/Feature/{TallyCreateTest,Http/TallyValidationTest}.php` | — (backend) |
| 2026-10-11 | Harness: ES-04 (DemoSeeder "Fresh Start" tally with no transactions) · LE-02 (`/dev/error-page`, dev-only) | `database/seeders/DemoSeeder.php`, `routes/web.php`, `tests/Feature/{DemoSeederTest,DevPreviewRoutesTest}.php` | `desktop/tally-empty-0.png`, `mobile/tally-empty-0.png`, `desktop/error-404-page.png`, `mobile/error-404-page.png` |
| 2026-10-11 | F-036, F-037, F-039 (Home Recent = `CardHeader` + `DataTable` + glyph map; By category = `CardHeader`; tally pill stays neutral: no shared flag in payload) | `resources/js/pages/home/sections/{HomeRecentTransactions,HomeCategoriesBreakdown,HomeYourTallies}.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/home-1.png`, `mobile/home-1.png`, `mobile/home-2.png` |
| 2026-10-11 | F-006 (sidebar brand block, wordmark hidden in the md rail), F-021 (10-bar sparkline in the Home hero from `weekly_history`) | `resources/js/components/AppSidebar.vue`, `resources/js/components/ui/balance-hero/BalanceHero.vue`, `resources/js/pages/Home.vue`, `resources/js/pages/home/sections/HomeBalanceGrid.vue` | `desktop/home-0.png`, `desktop/home-rail-0.png`, `mobile/home-0.png` |
| 2026-10-11 | F-015 (disabled search pill + tooltip, ≥1001px), F-025 (You dropped from Quick actions; Settle → B-04), F-091 (desktop Cancel, hidden < md) | `resources/js/pages/home/sections/{HomeHeader,HomeQuickActions}.vue`, `resources/js/components/{AppOverlays,tallies/AddTransactionForm}.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/home-0.png`, `desktop/home-search-tooltip.png`, `desktop/modal-addTx-form-1.png`, `mobile/home-0.png` |
| 2026-10-11 | F-073 (mono 10.5px axis from `--text-axis`, `--radius-bar` end radius, D2 labels), F-076 heading (`CardHeader`), F-063 (one `CardHeader` card, per-currency blocks) | `resources/js/pages/stats/sections/{StatsSpendingChart,StatsCategoryBreakdown}.vue`, `resources/js/pages/tallies/transactions/Index.vue` | `desktop/stats-0.png`, `mobile/stats-0.png`, `desktop/detail-0.png`, `mobile/detail-1.png` |
| 2026-10-11 | F-090 (dialog shell: 500px, `22px 24px 14px` header / `24px` body, `shadow-modal`, 40px outline close; scrim left open: `--scrim` not wired in `app.css`), F-104 (Stats empty CTA opens Add transaction) | `resources/js/components/{ResponsiveModal.vue,ui/dialog/DialogContent.vue}`, `resources/js/pages/stats/sections/StatsEmptyState.vue` | `desktop/modal-addTx-form-0.png`, `desktop/stats-empty-0.png`, `mobile/stats-empty-0.png` |
| 2026-10-11 | F-065, F-069 (Transactions = `CardHeader` + `DataTable` + glyph map, stacked ≤ 640px; Paid by → B-07, Export CSV cut; `category_id` added to the payload) | `resources/js/pages/tallies/transactions/Index.vue`, `app/Http/Controllers/TransactionController.php`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/detail-0.png`, `mobile/detail-1.png` |
| 2026-10-13 | F-080, F-081 (You header: 26/24px h1 + 13px sub, no eyebrow/rule, no header bar; Profile/Password/Appearance stop passing breadcrumbs, the Sections nav stays until F-082) | `resources/js/layouts/settings/Layout.vue`, `resources/js/pages/settings/{Profile,Password,Appearance}.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/profile-0.png`, `mobile/profile-0.png` |
| 2026-10-13 | F-103 (Home/Tallies/Stats empty = `EmptyState`, no trailing period), F-105 (New tally: native `required` removed so the `FormField` error shows; Add-tx keeps its disabled-submit gating) | `resources/js/pages/{home/sections/HomeEmptyState,stats/sections/StatsEmptyState,tallies/Index}.vue`, `resources/js/components/tallies/NewTallyForm.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/{home,tallies,stats}-empty-f103.png`, `mobile/{home,tallies,stats}-empty-f103.png`, `desktop/modal-newTally-error-f105.png` |
| 2026-10-13 | F-097, F-098, F-099 (Share = `ResponsiveModal`: 19px title, intro, filled mono invite + Send, inline Members incl. invitees, `SplitBar`/`SegmentedControl sm`/`NumberStepper sm`/lock split editor with Equal/Custom + rebalance, Cancel + Done; bottom sheet < md) | `resources/js/components/tallies/TallyShareDialog.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/modal-shareTally-f097.png`, `mobile/sheet-shareTally-f098.png` |
| 2026-10-13 | F-082, F-083, F-084 (You hub at `/settings`, route `you`: `Avatar xl` identity card + Edit → profile page, Preferences/Account/Data cards with `CardHeader` + `SettingRow`; Theme `PillSelect` Light/Dark/System, Password link row, Sign out, Delete account. Default currency → B-10; sidebar user block → F-007) | `app/Http/Controllers/Settings/YouController.php`, `routes/settings.php`, `resources/js/pages/settings/You.vue`, `resources/js/components/{DeleteAccountDialog,DeleteUser,BottomNav}.vue`, `resources/js/i18n/locales/{en,it}.ts`, `tests/Feature/Settings/YouControllerTest.php` | `desktop/you-hub-0.png`, `mobile/you-hub-0.png` |
| 2026-10-13 | F-007 (sidebar user block = plain link to You: `Avatar md` c1, name, email, `--line` top border; `NavUser` dropdown dropped from the sidebar, avatar only in the md rail), F-094 (New tally: one row of combined dot + glyph chips, first preselected; Description removed) | `resources/js/components/AppSidebar.vue`, `resources/js/components/tallies/NewTallyForm.vue` | `desktop/home-f007.png`, `desktop/home-rail-f007.png`, `desktop/modal-newTally-f094.png`, `mobile/sheet-newTally-f094.png` |
| 2026-10-13 | F-106, F-107 (`/dev/design-system`: sans 12/600 section labels, default `SegmentedControl` + underline `Input` demos and Transfer/Scan/Remind/bell/date-range/invoices demos dropped; settle-banner space before the amount, tally-card amount `whitespace-nowrap`; the `variant="default"` prop itself stays) | `resources/js/pages/dev/DesignSystem.vue`, `resources/js/components/ui/{settle-banner/SettleBanner,tally-rail/TallyRailCard}.vue` | `desktop/design-system-f106.png`, `mobile/design-system-f106.png` (no design ref: dev page) |
| 2026-10-13 | F-085, F-086, F-088 (row layout) — already implemented with the You hub (F-082); verified and logged, no code change. Default currency row still → B-10 | `resources/js/pages/settings/You.vue` (unchanged) | `desktop/you-hub-f085.png`, `mobile/you-hub-f085.png` |
| 2026-10-13 | F-092 (Tally + Date inline row: Tally select in the form, 2-col in the dialog / 1-col in the sheet, picker step removed; currency half → B-10/B-11) · F-016 / DO-01 ACCEPTED (bell left out, no placeholder) | `resources/js/components/{AppOverlays,tallies/AddTransactionForm}.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/modal-addTx-f092-1440.png`, `-1280.png`, `-filled.png`, `mobile/sheet-addTx-f092.png` |
| 2026-10-13 | F-108, F-109, F-110 (empty tally detail: Transactions card = `EmptyState` with title, body and an Add expense CTA to the same `transactions.create` URL as the header button; balance card not rendered with no transactions; "above" copy dropped) | `resources/js/pages/tallies/transactions/Index.vue`, `resources/js/i18n/locales/{en,it}.ts` | `desktop/tally-empty-f108.png`, `mobile/tally-empty-f108.png`, `desktop/detail-f108-regress.png`, `mobile/detail-f108-regress.png` |
| 2026-10-13 | F-111 (title `text-hero` + `-0.045em`, body `text-lede`), F-112 (title periods dropped en/it; wordmark half ACCEPTED) · `/dev/error-page/{403,404,500,503}` · owner decision: error page = public context | `resources/js/pages/ErrorPage.vue`, `routes/web.php`, `resources/js/i18n/locales/{en,it}.ts`, `tests/Feature/DevPreviewRoutesTest.php` | `desktop/error-{403,404,500,503}-f111.png`, `mobile/error-{403,404,500,503}-f111.png` |
| 2026-10-13 | F-113 (status-specific ErrorPage copy: 403 "You can't open this", 503 "Back in a few minutes", 404/500 unchanged; en + it; copy keyed per status as `errors.{not_found,forbidden,unavailable,generic}.{eyebrow,title,description}`, `title_lead`/`title_em` merged into `title`) | `resources/js/pages/ErrorPage.vue`, `resources/js/i18n/locales/{en,it}.ts`, `tests/Feature/DevPreviewRoutesTest.php` | `desktop/error-{403,404,500,503}-f113.png`, `mobile/error-{403,404,500,503}-f113.png`, `{desktop,mobile}/error-{403,503}-f113-it.png` |
| 2026-10-13 | O-04 (currency rule: Home hero/In/Out/sparkline/By category and Stats chart/categories in the primary currency = B-10 default else most-used, other currencies on one quiet "Other currencies" line, never summed or converted; tally card month filter on `transaction_date`; Home cache keys versioned `home:v2:…`; DemoSeeder "City Break" EUR+USD tally. Tallies index → B-06) | `app/Repositories/{TransactionStatsRepository,TallyRepository}.php`, `app/Contracts/Repositories/{TransactionStatsRepositoryInterface,TallyRepositoryInterface}.php`, `app/Services/{Home,HomeCache,Stats}Service.php`, `app/Http/Controllers/Settings/PreferencesController.php`, `database/seeders/DemoSeeder.php`, `resources/js/components/ui/balance-hero/BalanceHero.vue`, `resources/js/pages/{Home,Stats}.vue`, `resources/js/pages/home/{types.ts,utils.ts,sections/{HomeBalanceGrid,HomeCategoriesBreakdown}.vue}`, `resources/js/pages/stats/{types.ts,sections/{StatsSpendingChart,StatsCategoryBreakdown}.vue}`, `resources/js/i18n/locales/{en,it}.ts`, `CLAUDE.md`, `tests/Feature/{HomeControllerTest,StatsControllerTest,DemoSeederTest,Settings/PreferencesUpdateTest,Cache/HomeCacheInvalidationTest}.php`, `tests/Unit/Repositories/{TransactionStatsRepository,TallyRepository}Test.php`, `tests/Unit/Services/{Home,HomeCache,Stats}ServiceTest.php`, flake fixes in `tests/Feature/{Http/WebTransactionControllerTest,Transactions/IndexTest}.php` | `{desktop,mobile}/home-o04-0.png`, `{desktop,mobile}/home-o04-city-break-card{,-crop}.png`, `desktop/home-o04-categories.png`, `{desktop,mobile}/stats-o04-0.png` |
| 2026-10-13 | B-05 backend (unblocks F-032, now OPEN for UI; Home `shared_tally` removed; `HomeCacheService::KEY_VERSION` → `v4`) | `app/Repositories/TallyRepository.php`, `app/Services/{HomeService,HomeCacheService}.php`, `resources/js/pages/home/{types.ts,sections/HomeYourTallies.vue}`, `tests/…` | `{desktop,mobile}/home-b05.png` |
| 2026-10-13 | B-06 backend (unblocks F-043, F-046, F-048, F-049, now OPEN for UI; Tallies index `totals[]` per currency replaces `income_total`/`expense_total`; Edit tally accepts the stored legacy glyph) | `app/Repositories/TallyRepository.php`, `app/Contracts/Repositories/TallyRepositoryInterface.php`, `app/Http/Controllers/TallyController.php`, `app/Http/Requests/Tally/UpdateTallyRequest.php`, `resources/js/pages/tallies/Index.vue`, `tests/…` | `{desktop,mobile}/tallies-b06.png` |
| 2026-10-13 | F-032 (Home card avatar stack + split label verified to spec: 22px, `-7px` overlap, `2px --surface` ring, "60 / 40 split"; no code change) · F-043 (header sub "N ledgers · N shared"; "€ tracked" ACCEPTED, O-04) · F-046 (segmented All / Shared / Private + outline "Sort: Recent" menu with Recent / Name, client-side; no Archived DO-04, no Amount O-04) | `resources/js/pages/tallies/Index.vue`, `resources/js/i18n/locales/{en,it}.ts`, `docs/design/VERIFICATION.md` | `desktop/{home-f032,tallies-f046}.png`, `mobile/{home-f032,tallies-f046,tallies-f046-sortmenu}.png` |
| 2026-10-13 | F-048 (type pill + "N transactions" cells, hidden below `md`) · F-049 (type pill "Shared · 60/40" / "Private", "N transactions", per-currency amount column from `totals[]`, most-used first, O-04; status line left for B-04/F-050) | `resources/js/components/ui/tally-list-row/{TallyListRow.vue,index.ts}`, `resources/js/pages/tallies/Index.vue`, `resources/js/i18n/locales/{en,it}.ts`, `docs/design/VERIFICATION.md` | `desktop/tallies-f048.png`, `mobile/tallies-f048.png` |
| 2026-10-13 | B-07 backend (unblocks F-054, F-055, F-058, F-066, now OPEN for UI; no UI built) | `app/Models/{Tally,User}.php`, `app/Actions/Transaction/GetTallyPerPersonAction.php`, `app/Http/Controllers/TransactionController.php`, `app/Repositories/TallyRepository.php`, `app/Services/HomeCacheService.php`, `resources/js/pages/tallies/transactions/Index.vue` (types only), `tests/…` | `{desktop,mobile}/detail-b07-{household,city}.png` |
| 2026-10-13 | O-02 (Home tally card total kept per currency: `tallies.*.monthly_total` → `monthly_totals[]` `{amount, currency_symbol, currency_code, decimals}`, one entry per currency the tally has used, most-used first; card shows each non-zero currency, or 0 in the tally's main currency on a quiet month) | `app/Repositories/TallyRepository.php`, `app/Contracts/Repositories/TallyRepositoryInterface.php`, `app/Services/Home{,Cache}Service.php`, `resources/js/components/ui/tally-rail/{TallyRailCard.vue,index.ts}`, `resources/js/pages/home/{types.ts,sections/HomeYourTallies.vue}`, `tests/Feature/HomeControllerTest.php`, `tests/Unit/Repositories/TallyRepositoryTest.php`, `tests/Unit/Services/Home{,Cache}ServiceTest.php` | `desktop/home-o02-tally-currency.png`, `mobile/home-o02-tally-currency.png` |
| 2026-10-13 | F-066 (Paid by column), F-054/F-055 partly (member-count sub, desktop + mobile; 48px/40px glyph blocked: no `CategoryGlyph` size for it) · F-058 left (hero is Net Balance, not Total spent) | `resources/js/pages/tallies/transactions/Index.vue`, `resources/js/i18n/locales/{en,it}.ts` | `{desktop,mobile}/detail-f054-f066.png` |
| 2026-10-15 | Transactional emails restyled to the new system (invitation existing/new user, verify email, reset password): Geist, `--bg`/`--surface`/`--line` hex, indigo pill buttons, 20px card, new `x-mail::tally-card`, brand lockup as hosted PNG, `prefers-color-scheme: dark` block; terracotta/Newsreader removed. Dev preview at `/dev/mail/{template}` | `resources/views/vendor/mail/html/{layout,header,tally-card}.blade.php`, `resources/views/vendor/mail/html/themes/default.css`, `resources/views/mail/tally/*.blade.php`, `routes/web.php`, `public/mail-logo.png` | `mail/invitation-existing-light.png`, `mail/invitation-existing-dark.png`, `mail/invitation-existing-mobile.png`, `mail/invitation-new-light.png`, `mail/verify-email-light.png`, `mail/reset-password-light.png` |
| 2026-10-13 | F-054, F-055 (glyph 48px/40px + mobile actions row), F-058 (shared hero), F-090 (scrim) · F-066 re-compared, matches · side-by-side re-check of the previous run: F-054/F-055 were still open (no glyph; mobile actions inline, title truncated) | `resources/js/components/ui/{category-glyph/CategoryGlyph.vue,detail-header/DetailHeader.vue,dialog/{DialogOverlay,DialogScrollContent}.vue,sheet/SheetOverlay.vue}`, `resources/js/pages/tallies/transactions/Index.vue`, `resources/css/app.css`, `resources/js/i18n/locales/{en,it}.ts` | `{desktop,mobile}/detail-f058-{household,city,solo,weekend}.png` vs `design/{desktop,mobile}/detail-0.png` |
| 2026-10-13 | F-010 (sidebar tally list; symbols of other non-zero currencies after the amount, "· $", title lists all; replaced `+N`) | `resources/js/components/AppSidebar.vue`, `resources/js/components/ui/category-glyph/CategoryGlyph.vue`, `app/Services/HomeService.php`, `app/Observers/{Transaction,Tally}Observer.php` | `desktop/home-0.png`, `desktop/hover-sidebar-nav.png`, `desktop/home-light-0.png` |
| 2026-10-13 | B-03 (Home month-over-month delta data in the primary currency; F-022 stays open) | `app/Services/{HomeService,HomeCacheService}.php`, `resources/js/pages/home/{types.ts,sections/HomeBalanceGrid.vue}`, `tests/Feature/HomeBalanceDeltaTest.php`, `tests/Unit/Services/{HomeService,HomeCacheService}Test.php` | `{desktop,mobile}/home-b03-0.png` |
| 2026-10-13 | F-022 delta caption FIXED (`balance.previous_period`) · O-05 shared-delete dialog + success flash (member notice ACCEPTED out) · O-07 auth inputs `name`/`autocomplete` FIXED | `app/Http/Controllers/TallyController.php`, `app/Services/HomeService.php`, `resources/js/pages/{auth,settings/Password,tallies/Edit,tallies/Index,home}`, `resources/js/i18n/locales/{en,it}.ts`, tests | `{desktop,mobile}/home-f022.png`, `desktop/tally-deleted-flash.png` |
| 2026-10-13 | B-03 amended (delta = period-to-date vs the same span of the previous period, clamped; `previous_net` cached per day, `KEY_VERSION` `v6`) · harness login recipe (inputs have no `name`) · O-06 recorded | `app/Services/{HomeService,HomeCacheService}.php`, `tests/Feature/HomeBalanceDeltaTest.php`, `tests/Unit/Services/{HomeService,HomeCacheService}Test.php`, `docs/design/VERIFICATION.md` | — (backend) |
| 2026-10-13 | B-11 amended: the Add-tx currency prefill re-applies on a tally switch only until the user picks a currency themselves (a manual pick survives later switches). Playwright: GBP/USD picked then tally switched → stays; untouched form follows Household EUR ↔ Weekend Trip USD (failed before the fix: GBP → USD) | `resources/js/components/tallies/AddTransactionForm.vue`, `docs/design/VERIFICATION.md` | — (behaviour, no layout change) |
| 2026-10-13 | B-10 / B-11 amended (owner decision, rung 4): app default currency `config('app.default_currency')` = EUR is the last prefill rung instead of an empty select; registration stores it as the new user's default, existing null users keep null. `Currency::appDefault()` | `config/app.php`, `app/Models/{Currency,Tally}.php`, `app/Http/Controllers/Auth/RegisteredUserController.php`, `tests/Feature/{Auth/RegistrationTest,TallyDefaultCurrencyTest}.php`, `docs/design/VERIFICATION.md` | — (backend) |
| 2026-10-13 | F-087 FIXED (You → Preferences "Default currency" = `SettingRow` + `PillSelect` "EUR €" + None, PATCH `preferences.update` on change; Home primary flips EUR → USD on next load, verified) · F-095 FIXED (New tally + Edit tally: filled `Select` "Default currency" with hint "Default for new transactions", prefilled with the user default, None clears; full width as Type is cut; supported currencies shared as an Inertia once prop `currencies`) · **F-092 currency half STOPPED: the design has no currency control in Add transaction (only the `€` mark in the amount entry), so the existing Currency select stays until the owner decides** | `resources/js/pages/settings/You.vue`, `resources/js/components/tallies/NewTallyForm.vue`, `resources/js/pages/tallies/Edit.vue`, `resources/js/types/index.d.ts`, `app/Http/Middleware/HandleInertiaRequests.php`, `resources/js/i18n/locales/{en,it}.ts`, `tests/Feature/Settings/{YouController,PreferencesUpdate}Test.php` | `{desktop,mobile}/you-f087.png`, `desktop/home-f087-usd.png`, `desktop/modal-newTally-f095.png`, `mobile/sheet-newTally-f095.png`, `{desktop,mobile}/tally-edit-f095.png` vs `design/{desktop,mobile}/profile-0.png`, `design/{desktop/modal,mobile/sheet}-newTally-0.png` |
| 2026-10-13 | **B-04** (per-currency `my_balance` on Home `tallies[]` and the Tallies index, `settle_suggestion`, `settled_members` on the tally detail; `GetTallyDebtsAction` nets in integer minor units with largest-remainder rounding, former members at 0%; `KEY_VERSION` `v7`; share changes flush every member) · O-06 fixed for `GetTallyDebtsAction` + `GetTallySplitAction` · O-08 recorded · money-column and old-vs-new balance checks (report only) · F-025/F-027/F-033/F-050/F-061 stay OPEN (data only, no UI) | `app/Actions/Transaction/{GetTallyDebtsAction,GetTallySplitAction,GetBalanceSummaryAction}.php`, `app/Models/Tally.php`, `app/Repositories/TallyRepository.php`, `app/Services/{Home,HomeCache}Service.php`, `app/Http/Controllers/{Tally,Transaction}Controller.php`, `app/Observers/TallyUserObserver.php`, `app/Console/Commands/CompareBalancesCommand.php`, `resources/js/pages/{Home.vue,home/types.ts,home/sections/HomeQuickActions.vue,tallies/Index.vue,tallies/transactions/Index.vue}`, tests (see B-04) | — (backend; no UI change) |
| 2026-10-14 | F-050 FIXED (Tallies row status from `my_balance`, `statusTone`/`statusExtra`) · F-092 currency half FIXED (pill currency picker in the amount entry, Currency field removed) · F-061 second line BUILT, not verifiable on demo data (stays OPEN) | `resources/js/components/ui/{tally-list-row/TallyListRow,amount-input/AmountInput,settle-banner/SettleBanner}.vue`, `resources/js/components/tallies/AddTransactionForm.vue`, `resources/js/pages/{tallies/Index,tallies/transactions/Index,dev/DesignSystem}.vue`, `resources/js/i18n/locales/{en,it}.ts` | `{desktop,mobile}/tallies-f050.png`, `{desktop,mobile}/detail-f061.png`, `{desktop,mobile}/addtx-f092-currency.png` vs `design/{desktop,mobile}/tallies-0.png`, `detail-0.png` |
| 2026-10-14 | **B-09** (Stats `?tally=` filter: 403 foreign / 422 invalid, figures in the tally's own currency when filtered; `daily_average`, `previous_daily_average`, `previous_period`, `delta_percentage` on daily averages over the B-03 same span; `shared_total_out`/`shared_percentage`; `owed_to_me {amount_minor, open_balances}` from B-04; latest-20 `activity[]`; `filter`, `tallies[]`. B-03 span + formula moved to `PeriodComparison`; `HomeCacheService::balanceSummary($userId)` derives every tally itself; no new cache key, `KEY_VERSION` stays `v7`) · F-070/F-071/F-075/F-076/F-077 stay OPEN (data only, no UI) | `app/Services/{PeriodComparison,StatsService,HomeService,HomeCacheService}.php`, `app/Http/Controllers/StatsController.php`, `app/Repositories/{TransactionStatsRepository,TallyRepository}.php`, `app/Contracts/Repositories/{TransactionStatsRepositoryInterface,TallyRepositoryInterface}.php`, `resources/js/pages/{Stats.vue,stats/types.ts}`, `tests/Feature/{StatsKpisTest,StatsControllerTest}.php`, `tests/Unit/Services/{PeriodComparisonTest,StatsServiceTest,HomeCacheServiceTest}.php`, `tests/Unit/Repositories/{TransactionStats,Tally}RepositoryTest.php` | — (backend; props only, no UI change) |
| 2026-10-14 | F-070, F-071, F-075, F-076 (c5/c7 placement), F-077 | `resources/js/pages/Stats.vue`, `resources/js/pages/stats/sections/{StatsKpiStack,StatsActivity,StatsCategoryBreakdown}.vue`, `resources/js/pages/stats/types.ts`, `resources/js/pages/home/{utils.ts,sections/HomeBalanceGrid.vue}` (shared `deltaCaption`), `resources/js/i18n/locales/{en,it}.ts`, `app/Services/StatsService.php` (`tallies[].is_shared`), `tests/{Feature/StatsControllerTest,Unit/Services/StatsServiceTest}.php` | `desktop/stats-0.png`, `desktop/stats-1.png`, `mobile/stats-0.png`, `mobile/stats-1.png` |
| 2026-10-14 | F-076 empty slot (By category keeps its card position and shows `EmptyState` when the filtered range has no categories) · F-075 / F-077 gaps ACCEPTED (no "Other currencies" on KPI cards; no time on activity rows) | `resources/js/pages/Stats.vue`, `resources/js/pages/stats/sections/StatsCategoryBreakdown.vue`, `resources/js/i18n/locales/{en,it}.ts`, `docs/design/VERIFICATION.md` | `desktop/stats-f076-empty-1440.png`, `mobile/stats-f076-empty-375.png` |
| 2026-10-15 | Dialog shell: `DialogHeader` reserves `pr-18` for the 40px close button, so a long title wraps before it (Delete tally / Delete transaction / Delete account confirmations); verified at 375x812 and 360x800 with a stress title — text right edge 286 / 256 vs close button left 303 / 287, no overlap. The Add transaction, Share and picker sheets (`BottomSheet`, flex header) were checked at both sizes, no overlap; New tally is a full page on phones, not a dialog | `resources/js/components/ui/dialog/DialogHeader.vue` | 375x812, 360x800 |
| 2026-10-14 | **FP-09 FIXED:** Home By category = top five + a real Other row computed on the server, shared with Stats (`CategoryBars`; `categoriesBreakdown()` returns every category, `limit` removed; `KEY_VERSION` v10 → v11) · FP-07: `md:-bottom-6` closes the 4px gap under the sticky footer, blur check recorded · FP-08: the rail lists a glyph tile per tally (wording corrected) | `app/Services/{CategoryBars,HomeCacheService,StatsService}.php`, `app/Repositories/TransactionStatsRepository.php`, `resources/js/{components/{AppSidebar.vue,tallies/{AddTransactionForm,NewTallyForm}.vue},pages/home/sections/HomeCategoriesBreakdown.vue}`, `tests/…` | `desktop/fp09-*.png`, `fp09b-*.png`, `mobile/fp09-home-categories-375.png` vs `design/desktop/{home-1,modal-addTx-0,modal-newTally-0}.png`, `design/mobile/home-2.png`, 860px render of `tallypal-desktop.html` |
| 2026-10-14 | **FP-01 FIXED, decision replaced (owner, 2026-10-14):** every per-tally amount means money SPENT per currency, neutral `--text`, no sign, no `--pos`; owed/owing only via the `my_balance` lines and pills. Sidebar prints `monthly_totals[].amount` (the `net` field is removed, `KEY_VERSION` v9 → v10); Home card caption "spent in Oct" (`home.tallies.spent_in`, `current_month_start`); Tallies row = all-time spending captioned "total spent" (`tallies.total_spent`); `positive`/`amountPositive` props removed, `amountCaption` added · **F-075 reversed in part (owner, 2026-10-14):** (owner, 2026-10-14):** Owed to you keeps its KPI-currency amount and count and adds the quiet line "Also $ 105.85" (`owed_to_me.other_currencies[]` `{amount_minor, currency_symbol, currency_code, decimals}` from `GetBalanceSummaryAction`, scoped to the filtered tally) · Totals: Observations row recounted | `app/Repositories/TallyRepository.php`, `app/Contracts/Repositories/TallyRepositoryInterface.php`, `app/Services/{HomeService,HomeCacheService,StatsService}.php`, `resources/js/components/AppSidebar.vue`, `resources/js/pages/home/types.ts`, `resources/js/types/index.d.ts`, `resources/js/pages/stats/{types.ts,sections/StatsKpiStack.vue}`, `resources/js/i18n/locales/{en,it}.ts`, `tests/…` | `desktop/fp01-{citybreak,household,weekendtrip}-1440.png`, `desktop/stats-owed-1440.png`, `mobile/stats-owed-375.png` vs `design/desktop/{home,stats}-0.png` |
| 2026-10-13 | F-025, F-027 (Settle tile: 3rd of Add / Split / Settle / New; sub "Noah Whitfield owes 105.85" opens that tally, "All settled" opens Tallies; `ArrowRightLeft`) · F-033 (desktop card footer pill from the first `my_balance` entry: `--pos` chip owed to you, `--text-2` you owe, `--text-3` settled, `· $` for other currencies; hidden on the mobile rail as in the design; cards stretch so footers align; no transactions yet reads "Settled"). `balanceStatus()` shared with F-050, `Badge variant="ok"` (`.pill.ok`) added · en/it singular + plural checked in vue-i18n | `resources/js/pages/home/{utils.ts,sections/{HomeQuickActions,HomeYourTallies}.vue}`, `resources/js/components/ui/{badge/index.ts,tally-rail/TallyRailCard.vue}`, `resources/js/i18n/locales/{en,it}.ts`, `docs/design/COMPONENT_INVENTORY.md` | `desktop/home-f033-1.png`, `mobile/home-f033-0.png` vs `design/{desktop,mobile}/home-0.png` (desktop/mobile other viewport unchanged: no mobile rail footer) |
| 2026-10-14 | O-01 (`public/favicon.svg`, no console error on /home) · F-037 accent tally pill on Home Recent when the tally is shared (`is_shared` in the recent payload, `KEY_VERSION` `v8`) | `public/favicon.svg`, `app/Repositories/TransactionStatsRepository.php`, `app/Services/HomeCacheService.php`, `resources/js/pages/home/{types.ts,sections/HomeRecentTransactions.vue}`, `tests/Unit/{Repositories/TransactionStatsRepositoryTest,Services/HomeCacheServiceTest}.php` | `{desktop,mobile}/home-f037.png` vs `design/{desktop/home-1,mobile/home-2}.png` |

## Summary of Decisions

Owner triage of the BACKEND and DESIGN-ONLY items: batches 1–4 on 2026-09-15, batches 5–7 on
2026-10-11. Full decision, status and action for each item: **Triage decisions** below, plus a
Decision line on every B-/DO- entry.

**BACKEND items to implement (10):** B-01, B-03, B-04, B-05, B-06, B-07, B-08 (bug fix), B-09,
B-10, B-11 (default only).
**BACKEND items to stub (0):** none. B-02 was re-decided as a UI-only stub, see below.
**BACKEND items cut (1):** B-12.

**DESIGN-ONLY items to remove from UI (7):** DO-02, DO-03, DO-06, DO-07, DO-08, DO-09, DO-10.
**DESIGN-ONLY items to stub in UI (3):** DO-01, DO-04, DO-05.

### Owner decision — 2026-10-13 (O-04, currencies)

- **Currency rule** (also in `CLAUDE.md` → "Domain rule: currencies"): amounts in different
  currencies are never added together and never converted. Per-tally figures list one entry per
  currency; cross-tally aggregates are in the user's primary currency (B-10 default, else
  most-used) with one quiet line for each other currency's total; every month/range filter uses
  `transaction_date`. B-01, B-03, B-06 and B-09 mini-specs rewritten for it. Details: **O-04**.

### Owner decisions — 2026-10-14 (B-09, Stats)

- Filtered to one tally, Stats figures use **that tally's currency** (B-11 order); unfiltered, the
  primary currency. Deltas compare **daily averages** over the B-03 same span (week, month, year).
  `owed_to_me` comes from B-04 as `amount_minor` (the only minor-unit field). `shared_percentage` is
  `null` with no expenses. Activity = latest 20 in range, each in its own currency.
  `HomeCacheService::balanceSummary()` always covers every tally of the user. Details: **B-09**.

### Owner decisions — 2026-10-13 (B-04 UI: status text, Settle action, settled members)

- **Status text (F-033 Home card footer, F-050 Tallies row status)** is based on the tally's **first
  `my_balance` entry** (the most-used currency):
  - `owed_to_me`, one counterparty → "Luca owes 76.30" in `--pos`; several → "2 people owe you 76.30"
    in `--pos`.
  - `i_owe` → "You owe Luca 20.00" (or "You owe 2 people 20.00"), neutral `--text-2`.
  - `settled` → "Settled" in `--text-3`.
  - If other currencies have a non-zero balance, append their symbols in `--text-3` ("· $"): the same
    rule as the sidebar (F-010).
- **Settle quick action (F-025/F-027):** with a `settle_suggestion` the sub is "Luca owes 76.30" and the
  tile opens that tally. Without one the sub is "All settled" and the tile opens the Tallies list.
- **F-061:** the banner's second line lists the settled members in that currency ("Sara is settled" /
  "Sara and Marco are settled"), shown only when at least one member is settled and at least one isn't.

### Owner decisions — 2026-10-11 (batches 5–7)

- **B-10 → BUILD.** Scope: a nullable `users.default_currency_id`, the You → Preferences "Default
  currency" select, and prefilling the currency in Add transaction. Nothing else.
- **B-11 → BUILD AS DEFAULT ONLY.** Tallies keep holding multiple currencies. The tally currency
  only prefills new transactions; it never restricts or converts. Mini-spec rewritten.
- **B-12 → CUT.** New tally creates a solo tally; invites and splits happen afterwards through the
  Share dialog. "Members & split" and "Invite someone" are removed from the New tally dialog.
- **DO-07, DO-08, DO-09, DO-10 → CUT.**
- **Tokens** (`Tallypal Desktop.html` is the source of truth), in `DESIGN_TOKENS.md` and wired in
  `app.css`: `--radius-mark` 9px + `--text-brand` 16/600/`-.02em` (F-006); `--radius-spark`
  `3px 3px 1px 1px` (F-021); `--radius-bar` `6px 6px 2px 2px` + `--text-axis` 10.5px mono
  `.04em` (F-073); `--shadow-modal` (F-090). `Avatar` gains `xl` 56px / 18px (F-083); no 34px size,
  F-007 uses `md` (32px).
- **Components** added to `COMPONENT_INVENTORY.md`: `CardHeader` (B15), `SettingRow` (B16),
  `DataTable` (B17), `PillSelect` (E6), `EmptyState` (F1), `FormField` error state (E1), split
  editor extras (E3, Share dialog only). `Switch` (E5): **not needed**, no surviving finding uses
  it.
- **Mappings:**
  - D1 F-037 category → glyph: **decided** (owner approval, same day). Keyed on `category_id`, not
    the colliding `category_code`; Travel ✈, Household Supplies ▦, Housing + Rent ⌂, Transportation
    ◎; U+FE0E on every glyph with an emoji form; glyphs render as CSS-tinted text.
  - D2 F-073 axis: ≤ 7 buckets label every bar; > 7 → 6 labels at evenly spaced indexes, first and
    last included.
  - D3 F-082/F-084: You is a hub page; settings pages stay. Edit → profile page; "Password" row →
    password page; "Delete account" (neg) at the bottom of Data, below Sign out, opening the
    existing confirmation.
  - D4 F-086: Light / Dark / System via `PillSelect` in a "Theme" row.
  - D5 F-025: Quick actions = Add / Split / Settle / New tally. You dropped, Import cut, Settle
    waits on B-04.
  - D6 F-007: after F-088 (done), the sidebar user block is a plain link to You (32px `md`
    avatar, name, email); the dropdown is removed.
**Stubbed in UI as DESIGN-ONLY (1):** B-02: search box rendered but disabled with the tooltip
"Search coming soon". No endpoint, no route; real search deferred.

**UI elements to delete** (none are in the app today; all are design-only, so "delete" means
leave them out when porting the design, locations per the design file):
- Notifications bell icon: Home top bar, desktop + mobile. The design draws no dropdown.
- Import button: Home → Quick actions card, 4th tile "Import · CSV or receipt". The design has no
  upload UI.
- Trip date ranges, recurrence, invoices, per-tally metrics:
  - Home "Your tallies" card meta and footer.
  - Tallies list row sub, cells and status line.
  - Tally detail header sub and the "Days" figure.
- Archived filter segment and faded row styling: Tallies list.
- "Remind" button: Tally detail settle banner.
- CSV export buttons:
  - Tally detail Transactions card.
  - Stats All activity card.
  - You → Data → "Export all data · CSV".
- Added 2026-10-11:
  - "Transfer" segment: Add transaction type control (DO-07).
  - Per-member role select (Can edit / Can view): Share dialog (DO-08).
  - "Invite link" field + Copy, and the "Link requires approval" toggle: Share dialog (DO-09).
  - "Members can settle up" toggle: Share dialog (DO-10).
  - "Members & split" block, "+ Invite someone" and the Type (Shared / Private) select: New tally
    dialog (B-12).

### Notes on the triage text (recorded as corrected; confirm)

1. **Count.** The brief said "BACKEND items to implement (9)" but listed 8 IDs. After the B-02
   re-decision: 8 BACKEND to build, 0 BACKEND to stub.
2. **B-08 direction.** The brief said "replace instead of append". The audit (F-068) shows the list
   **already replaces** page 1 with page 2, so the fix is to **append** (cumulative `data`).
   Recorded as append.
3. **Delete list locations.**
   - The brief placed Import on the Tallies page; in the design it's a Home quick action.
   - The brief placed tally meta in the detail/settings UI; the design shows it on Home, the Tallies
     list and the detail header, and has no settings UI for it.
   - The brief mentioned a notifications dropdown and an archive checkbox/button. Neither exists in
     the design or the app.
4. **B-02 — resolved.** Of the two alternatives, the owner picked the disabled search box with the
   tooltip "Search coming soon". There is no dummy endpoint and no route. Status moves from
   BACKEND (stubbed) to DESIGN-ONLY/STUBBED.
5. **DO-05 "implement … in Batch 5".** Batch 5 in this file is the *You* verification batch (no code).
   Recorded as a later implementation phase, to be scheduled.

- **Design source (single source of truth, responsive):** `Tallypal Desktop.html`, project
  `019dc420-c153-725e-9f8b-4ee4e47b22df`, etag `1789474137049721`, saved verbatim (77,470 bytes)
  to `.verify/design/tallypal-desktop.html`. `Tallypal Mobile.html` / `Tallypal Site.html` ignored.
- **Design breakpoints (from the file):** `≤640px` phone (tab bar + FAB, bottom sheets), `641–860px`
  sidebar rail, `≤1000px` search hidden, `≤1100px` grids stack; `>1100px` full desktop. Checked at
  **375×812** (phone) and **1440×900** (desktop) only.
- **Reference shots:** `.verify/design/{mobile,desktop}/*.png` — viewport-sized at scroll offsets.
  Design default theme is **dark**; the app is compared in dark (`appearance=dark`) unless a row says
  light. Light references: `desktop/home-light-0.png`, `desktop/detail-light-0.png`,
  `mobile/home-light-0.png`.
- **App shots:** `.verify/app/{mobile,desktop}/*.png`. Seeded account `demo@tallypal.test`; empty
  states `demo-empty@tallypal.test`. Base URL `http://127.0.0.1:8000`.
- **Judged:** layout, hierarchy, spacing, typography, color, component usage, presence of every
  element. **Ignored:** content (names, numbers, avatars, copy wording).

## Classes

| Class | Meaning |
|---|---|
| `PASS` | Matches the design |
| `VISUAL` | Describable style/layout mismatch |
| `MISSING-UI` | Element in design, absent in app |
| `DEAD-UI` | Rendered but non-functional, fix is frontend wiring only |
| `BACKEND` | Needs Laravel work — mini-spec on the item |
| `DESIGN-ONLY` | Looks like invented placeholder functionality — owner decision, not assumed buildable |

A row may carry several findings; each finding gets its own class tag. Row status = `[ ]` pending,
`[x]` verified (screenshot taken).

Finding status **REMOVED** (from 2026-10-11): the finding existed only for an item the owner CUT.
It keeps its class tag for history but has nothing to build.

**Blocker tags** (added from Batch 5; not classes, they sit next to the class): `BLOCKED-TOKEN`
(needs a value `DESIGN_TOKENS.md` doesn't document), `BLOCKED-COMPONENT` (needs a component
`COMPONENT_INVENTORY.md` doesn't specify), `BLOCKED (mapping)` (needs an owner call on how a design
concept maps to the app). Each blocked finding records the exact design values it needs. All
blocked items across batches 1–7: **Blocked items** below.

## Progress

| Batch | Scope | Status |
|---|---|---|
| 1 | Shell (sidebar, tab bar) + Home | **done** — 42 findings |
| 2 | Tallies list | **done** — 9 findings |
| 3 | Tally detail | **done** — 18 findings |
| 4 | Stats | **done** — 10 findings |
| 5 | You (profile / preferences) | **done** — 9 findings |
| 6 | Modals: Add transaction, New tally, Share tally | **done** — 14 findings |
| 7 | Empty / loading / error states + base components (`/dev/design-system`) | **done** — 11 findings (F-103..F-107 at the first pass; F-108..F-113 after the ES-04 / LE-02 harness runs) |
| Categories | Income categories + category picker (`Tallypal Categories.html`) | **approved** 2026-10-14 — see **Categories batch** at the end |

---

## Resume notes (harness)

- Design ref is already captured, so don't re-capture it. To reopen it: the Playwright MCP
  `browser_navigate` blocks `file:`, so use `browser_run_code_unsafe` with
  `page.goto('file:///var/www/vhosts/tallypal/.verify/design/tallypal-desktop.html')`. The
  in-page helpers `go('<screen>')`, `openSheet('addTx'|'newTally'|'shareTally')`, `closeSheets()`
  and `setTheme('dark'|'light')` drive it.
- Login form: wait for `networkidle` before filling (the form isn't hydrated earlier), and submit
  with `form.requestSubmit()` — the Debugbar overlay can swallow the button click. Clear cookies after
  a re-seed (the old session points at a deleted user). The auth inputs carry `name` + `autocomplete`
  since O-07 (before it, `input[name=email]` timed out and was misread as a failed login), so select by
  name. Working, cookie-free recipe (`browser_run_code_unsafe`):
  `await page.context().clearCookies(); await page.goto('http://127.0.0.1:8000/login',
  {waitUntil:'networkidle'}); await page.fill('input[name=email]','demo@tallypal.test');
  await page.fill('input[name=password]','password');
  await page.evaluate(() => document.querySelector('input[name=email]').form.requestSubmit());`
  then `waitForTimeout(3000)` → lands on `/home`. (The `login` limiter is 5/min per email+IP, so
  don't loop failed attempts.)
- App login: `page.context().addCookies([{name:'appearance',value:'dark',url:'http://127.0.0.1:8000'}])`
  + `localStorage.setItem('appearance','dark')`, then fill `/login`. Hide Debugbar with
  `page.context().addInitScript(...)`, injecting `.phpdebugbar{display:none!important}`.
- Demo tallies have fixed UUIDs (`DemoSeeder::*_UUID`), so reseeding never changes these URLs:
  Household (shared, settlement, EUR+USD): `/tallies/0d3e0000-0000-7000-8000-000000000001/transactions`.
  Solo Savings: `0d3e0000-0000-7000-8000-000000000002`. Weekend Trip (default currency USD):
  `0d3e0000-0000-7000-8000-000000000003`. Fresh Start (empty, ES-04): `0d3e0000-0000-7000-8000-000000000004`.
  City Break (EUR + USD in the current month, O-04): `0d3e0000-0000-7000-8000-000000000005`.
  Ski Cabin (3 members 40/30/30, EUR only; Noah Whitfield is settled, Priya Shah owes you 40.00, F-061):
  `0d3e0000-0000-7000-8000-000000000006`.
  (All earlier ids, e.g. `01a136a8-…`, are gone and 404.)
  Since R4 the demo user also has income categories (Salary, Side work, Refunds & returns, Gifts, Investments, Sold items),
  one uncategorised income, uncategorised expenses (an "Uncategorised" ◌ row ranks 3rd in October's By category), and a
  learned frequent row (groceries, dining_out, transportation / salary, side_work), all dated relative to now.
- Overlays: `n` opens Add transaction (tally picker step first); the sidebar `+` (`aria-label="New
  tally"`) opens New tally on desktop, the dashed "New tally" rail card on mobile Home; Share opens
  from the detail header.
- Scroll step used: desktop 800px / mobile 652px viewport shots; `.verify/app/{desktop,mobile}/`.

## Master checklist

_Historical snapshot from the verification pass: the Result column keeps the status each row had when it was verified. The current status of every finding is the first line of its entry under **Findings**; counts are in **Totals**._

`D` = desktop 1440×900, `M` = mobile 375×812. Design ref path is relative to `.verify/design/`.

### Batch 1 — Shell + Home

| ID | Vp | Item | Design ref | State | Result |
|---|---|---|---|---|---|
| SH-01 | D | Sidebar: logo, Home/Tallies/Stats nav + kbd hints, "Tallies" section + `+`, per-tally list w/ glyph + amount, Theme button, user block → You | `desktop/home-0.png` | default | [x] VISUAL · MISSING-UI · BACKEND (B-01) — F-001, F-002, F-003, F-004 (primary tabs), F-005, F-008 **FIXED**, rest open (F-006, F-007 unblocked 2026-10-11; F-009 **FIXED**; F-010/B-01) |
| SH-02 | D | Sidebar hover + active (nav item, tally item, user block) | `desktop/hover-sidebar-nav.png` | hover/active | [x] VISUAL — F-011 **FIXED** |
| SH-03 | M | Bottom tab bar: Home/Tallies/FAB/Stats/You, active tint, blur bg | `mobile/home-0.png` | default/active | [x] VISUAL — F-012 **FIXED** |
| SH-04 | D | Keyboard shortcuts `1`/`2`/`3` (tabs), `n` (Add tx), `Esc` (close) | script | interaction | [x] MISSING-UI — F-013 **FIXED** (`1`/`2`/`3`/`n`; `Esc` → Batch 6, Radix already closes dialogs/sheets natively) |
| HO-01 | D | Home top bar: greeting h1 + sub, search pill w/ ⌘K, notifications icon button, primary "Add transaction" | `desktop/home-0.png` | default | [x] VISUAL · MISSING-UI (B-02 stub) · DESIGN-ONLY/STUBBED (DO-01) — F-014 **FIXED**, rest open (F-015/B-02); F-016/DO-01 **ACCEPTED** (bell left out) |
| HO-01 | M | Home top bar: h1 + sub, bell icon button, round 44px primary `+` | `mobile/home-0.png` | default | [x] VISUAL · MISSING-UI · DESIGN-ONLY/STUBBED (DO-01) — F-017, F-018 **FIXED**; F-016/DO-01 **ACCEPTED** (bell left out) |
| HO-02 | D | Balance hero card (c8): label, 64px mono amount w/ faded cur/dec, delta pill, 10-bar sparkline, In/Out on the right | `desktop/home-0.png` | default | [x] VISUAL · MISSING-UI · BACKEND (B-03) |
| HO-02 | M | Balance hero: 48px amount, delta, sparkline, In/Out below | `mobile/home-0.png` | default | [x] VISUAL · MISSING-UI · BACKEND (B-03) |
| HO-03 | D | Quick actions card (c4): 2×2 buttons, icon + title + sub; Add/Split/Settle/Import | `desktop/home-0.png` | default | [x] VISUAL · MISSING-UI · BACKEND (B-04) · DESIGN-ONLY/CUT (DO-02) |
| HO-03 | M | Quick actions: 2×2 vertical buttons, no sub | `mobile/home-0.png`, `home-1.png` | default | [x] VISUAL (+ HO-03 D items) |
| HO-04 | D | "Your tallies" section title + See all; auto-fill card grid (glyph, avatar stack, name/meta, amount, footer date + status pill) + dashed New tally card | `desktop/home-0.png` | default | [x] VISUAL · MISSING-UI · BACKEND (B-04, B-05) · DESIGN-ONLY/CUT (DO-03) — F-030, F-031 **FIXED**, rest open (F-032/B-05, F-033/B-04, F-034/DO-03) |
| HO-04 | M | "Your tallies": horizontal snap rail 156px cards, no footer, New tally card | `mobile/home-1.png` | default | [x] VISUAL · MISSING-UI · BACKEND (B-05) — F-031, F-035 **FIXED** (= HO-04 D), rest open |
| HO-05 | D | Recent card (c8): card header + "All activity"; table Transaction/Tally/Date/Amount; tally pill | `desktop/home-0.png`, `home-1.png` | default | [x] VISUAL |
| HO-05 | M | Recent: stacked rows, tally pill inline in sub | `mobile/home-1.png`, `home-2.png` | default | [x] VISUAL |
| HO-06 | D | By category card (c4): dot + name + bar + value + pct; "Details" link | `desktop/home-1.png` | default | [x] VISUAL · MISSING-UI — F-040 **FIXED**, F-039 (heading style) open |
| HO-06 | M | By category card | `mobile/home-2.png` | default | [x] VISUAL · MISSING-UI — F-040 **FIXED** (= HO-06 D), rest open |
| HO-07 | D | Hover: tally card → `surface-2`; table row → `surface-2` band | `desktop/hover-tally-card.png`, `hover-tx-row.png` | hover | [x] PASS (card) · VISUAL (row) — F-041 **FIXED** |
| HO-08 | D | Light theme Home | `desktop/home-light-0.png` | light | [x] PASS (palette) |
| HO-08 | M | Light theme Home | `mobile/home-light-0.png` | light | [x] PASS (palette) |
| HO-09 | D / M | App-only Home sections not in the design ("Last 12 weeks" chart card, "Shared tally" card) | — | default | [x] VISUAL — F-042 **FIXED** |

### Batch 2 — Tallies list

| ID | Vp | Item | Design ref | State | Result |
|---|---|---|---|---|---|
| TA-01 | D | Top: h1 + sub (ledgers · shared · tracked), primary "New tally" | `desktop/tallies-0.png` | default | [x] VISUAL |
| TA-01 | M | Top: h1 + sub, round `+` | `mobile/tallies-0.png` | default | [x] VISUAL · MISSING-UI — F-045 **FIXED**, F-044 **FIXED**, F-043 (header structure) open |
| TA-02 | D | Filter segmented All/Shared/Private/Archived + outline "Sort: Recent" | `desktop/tallies-0.png` | default/active | [x] BACKEND (B-06) · DESIGN-ONLY/STUBBED (DO-04) |
| TA-02 | M | Full-width segmented + Sort button stacked | `mobile/tallies-0.png` | default | [x] BACKEND (B-06) · DESIGN-ONLY/STUBBED (DO-04) (= TA-02 D) |
| TA-03 | D | Row grid: glyph 44 · name + avatars + dates · type pill · tx count · amount + status (owes in pos) | `desktop/tallies-0.png` | default | [x] VISUAL · BACKEND (B-04, B-05, B-06) |
| TA-03 | M | Row: glyph · name/sub · amount + status (cells hidden) | `mobile/tallies-0.png` | default | [x] PASS (anatomy) · BACKEND (B-04, B-06) |
| TA-04 | D | Archived row at 60% opacity | `desktop/tallies-0.png` | archived | [x] DESIGN-ONLY/STUBBED (DO-04) |
| TA-04 | M | Archived row | `mobile/tallies-0.png` | archived | [x] DESIGN-ONLY/STUBBED (DO-04) (= TA-04 D) |
| TA-05 | D | Row hover → `surface-2` | css | hover | [x] PASS |

### Batch 3 — Tally detail

| ID | Vp | Item | Design ref | State | Result |
|---|---|---|---|---|---|
| TD-00 | D | Page layout: 2 columns (5/12 stack: hero, settle, split · 7/12: transactions) | `desktop/detail-0.png` | default | [x] VISUAL |
| TD-01 | D | Header: back icon button, 48px glyph, h1 + sub, outline Share, primary "Add expense" | `desktop/detail-0.png` | default | [x] VISUAL · BACKEND (B-07) |
| TD-01 | M | Header: back, 40px glyph, 20px h1, round Share + Add icon buttons | `mobile/detail-0.png` | default | [x] VISUAL · MISSING-UI · BACKEND (B-07) |
| TD-02 | D | "Total spent" hero (c5 stack) + Per person / Days | `desktop/detail-0.png` | default | [x] VISUAL · BACKEND (B-07) · DESIGN-ONLY/CUT (DO-03) |
| TD-02 | M | Total spent hero | `mobile/detail-0.png` | default | [x] VISUAL |
| TD-03 | D | Settle banner: "X owes you €Y" + secondary line + Remind | `desktop/detail-0.png` | owes | [x] VISUAL · DESIGN-ONLY/STUBBED (DO-05) |
| TD-03 | M | Settle banner, full-width Remind | `mobile/detail-0.png` | owes | [x] VISUAL (= TD-03 D) |
| TD-04 | D | "Split so far" card + "Edit split" link; avatar + bar + value + pct rows | `desktop/detail-0.png` | default | [x] VISUAL · MISSING-UI — F-064 **FIXED**, F-063 (heading style, per-currency repeat) open |
| TD-04 | M | Split so far | `mobile/detail-1.png` | default | [x] VISUAL · MISSING-UI (= TD-04 D) — F-064 **FIXED**, rest open |
| TD-05 | D | Transactions card (c7): Item/Paid by/Date/Amount table + "Export CSV" | `desktop/detail-0.png` | default | [x] VISUAL · BACKEND (B-07, B-08 bug fix) · DESIGN-ONLY/CUT (DO-06) |
| TD-05 | M | Transactions stacked rows | `mobile/detail-1.png` | default | [x] VISUAL · BACKEND (B-08 bug fix) |
| TD-06 | D | Light theme detail | `desktop/detail-light-0.png` | light | [x] PASS (palette) |

### Batch 4 — Stats

| ID | Vp | Item | Design ref | State | Result |
|---|---|---|---|---|---|
| ST-01 | D | Top: "Spending" h1 + sub, segmented Week/Month/Year, outline Filter | `desktop/stats-0.png` | default/active | [x] VISUAL · BACKEND (B-09) |
| ST-01 | M | Top: h1 + sub; seg inline top-right (Filter hidden on phone) | `mobile/stats-0.png` | default | [x] VISUAL |
| ST-02 | D | Chart card (c8): KPI line (label ↔ mono value), 12 bars active accent, mono axis | `desktop/stats-0.png` | default | [x] PASS (KPI line) · VISUAL |
| ST-02 | M | Chart card 160px | `mobile/stats-0.png` | default | [x] VISUAL |
| ST-03 | D | KPI stack (c4): Daily average, Shared portion, Owed to you (+ sub lines) | `desktop/stats-0.png` | default | [x] BACKEND (B-09, B-03, B-04) |
| ST-03 | M | KPI cards stacked | `mobile/stats-0.png` | default | [x] BACKEND (B-09) (= ST-03 D) |
| ST-04 | D | By category card (c5) incl. "Other" | `desktop/stats-0.png`, `stats-1.png` | default | [x] VISUAL |
| ST-04 | M | By category | `mobile/stats-1.png`, `stats-2.png` | default | [x] VISUAL (= ST-04 D) |
| ST-05 | D | All activity card (c7): Transaction/Tally/Amount + Export CSV | `desktop/stats-1.png` | default | [x] BACKEND (B-09) · DESIGN-ONLY/CUT (DO-06) |
| ST-05 | M | All activity rows | `mobile/stats-2.png` | default | [x] BACKEND (B-09) · DESIGN-ONLY/CUT (DO-06) (= ST-05 D) |
| ST-06 | D | Chart bar hover → `accent-dim`; seg switch changes data | css/script | hover/active | [x] PASS (seg) · VISUAL (hover) — F-079 **FIXED** |

### Batch 5 — You

| ID | Vp | Item | Design ref | State | Result |
|---|---|---|---|---|---|
| YO-01 | D | Top: "You" h1 + "Account & preferences" | `desktop/profile-0.png` | default | [x] VISUAL — F-080 **FIXED**, F-082 |
| YO-01 | M | Top | `mobile/profile-0.png` | default | [x] VISUAL — F-081 **FIXED**, F-082 · PASS (tab bar "You" active) |
| YO-02 | D | Identity card (c5): 56px avatar, name, email, outline Edit | `desktop/profile-0.png` | default | [x] MISSING-UI · VISUAL — F-083, F-084 (unblocked 2026-10-11: `Avatar xl`; Edit → profile page) |
| YO-02 | M | Identity card | `mobile/profile-0.png` | default | [x] (= YO-02 D) |
| YO-03 | D | Preferences card: Dark theme toggle, Default currency select, Balance reminders toggle | `desktop/profile-0.png` | default | [x] MISSING-UI · VISUAL · BACKEND (B-10, BUILD) — F-085, F-086, F-087 (unblocked 2026-10-11: `CardHeader`, `SettingRow`, `PillSelect`); Balance reminders → DO-05 (left out) |
| YO-03 | M | Preferences card | `mobile/profile-0.png` | default | [x] (= YO-03 D) |
| YO-04 | D | Data card: Export all data (CSV), Sign out (neg) | `desktop/profile-0.png` | default | [x] MISSING-UI — F-088; Export all data → DO-06 (left out) |
| YO-04 | M | Data card | `mobile/profile-0.png` | default | [x] MISSING-UI — F-088 **FIXED** (button; row layout → F-085) |

### Batch 6 — Modals / sheets

| ID | Vp | Item | Design ref | State | Result |
|---|---|---|---|---|---|
| MO-01 | D | Add transaction dialog: big amount + hint, Expense/Income/Transfer seg, Title, Category chips, Tally + Date (2-col), Paid by, Cancel + Add | `desktop/modal-addTx-0.png` | open | [x] DEAD-UI · VISUAL · MISSING-UI · VISUAL · DESIGN-ONLY/CUT (DO-07) — F-089 **FIXED**, F-090 (unblocked: `--shadow-modal`), F-091, F-092 **Tally + Date FIXED, currency → B-10/B-11**, F-093 **REMOVED** |
| MO-01 | M | Add transaction bottom sheet: grabber, 1-col fields, no Cancel | `mobile/sheet-addTx-0.png`, `-1.png` | open | [x] PASS (sheet shell, field labels, no Cancel) · VISUAL — F-092 (Tally + Date 1-col **FIXED**; currency → B-10/B-11), F-093 |
| MO-02 | D | New tally dialog: Name, Colour & icon chips, Type + Currency, Members & split (Equal/Custom, total, split bar, rows w/ pct stepper + lock, hint), Invite someone, Create | `desktop/modal-newTally-0.png`, `modal-newTally-custom-split.png` | open/custom | [x] VISUAL · BACKEND (B-11 BUILD as default; B-12 CUT) — F-090, F-094, F-095, F-096 **REMOVED** |
| MO-02 | M | New tally bottom sheet | `mobile/sheet-newTally-0.png`, `-1.png` | open | [x] PASS (sheet shell) · (= MO-02 D) |
| MO-03 | D | Share tally dialog: copy, Invite link + Copy, Invite by email + Send, Members w/ role select, Split editor, 2 toggles, Cancel + Done | `desktop/modal-shareTally-0.png`, `-1.png` | open | [x] VISUAL · DESIGN-ONLY/CUT (DO-08, DO-09, DO-10) — F-097, F-099 open; F-100, F-101, F-102 **REMOVED** |
| MO-03 | M | Share tally bottom sheet | `mobile/sheet-shareTally-0.png`, `-1.png` | open | [x] VISUAL — F-098 (+ MO-03 D) |
| MO-04 | D | Scrim click + Esc close; focus trap | script | interaction | [x] PASS (`desktop/modal-focus-trap.png`) |

### Batch 7 — States not drawn in the design + base components

The design draws **no** empty, loading or error state. These rows are judged against the design's
tokens/components (consistency), not a reference shot.

| ID | Vp | Item | Design ref | State | Result |
|---|---|---|---|---|---|
| ES-01 | D / M | Home, empty account | — | empty | [x] VISUAL — F-103 (unblocked 2026-10-11: `EmptyState`) |
| ES-02 | D / M | Tallies, empty account | — | empty | [x] VISUAL — F-103 (unblocked 2026-10-11: `EmptyState`) |
| ES-03 | D / M | Stats, empty account | — | empty | [x] VISUAL — F-103, F-104 |
| ES-04 | D / M | Tally detail, tally with no transactions | `desktop/tally-empty-0.png`, `mobile/tally-empty-0.png` | empty | [x] verified 2026-10-11 against the new `DemoSeeder` tally "Fresh Start" (demo@tallypal.test) — see ES-04 notes · re-shot and judged 2026-10-13: VISUAL — F-108, F-109, F-110 **FIXED** |
| ES-05 | D / M | Add-tx opener with zero tallies | — | empty | [x] PASS ("No tallies yet" + New tally, `desktop/empty-addtx.png`, `mobile/empty-addtx.png`) |
| LE-01 | D / M | Deferred-prop skeletons (Home / Stats / detail) | — | loading | [x] PASS (overlay skeletons) — no deferred props exist (see LE-01 notes) |
| LE-02 | D / M | Error page (404) | `desktop/error-404-page.png`, `mobile/error-404-page.png` | error | [x] verified 2026-10-11 via the dev-only `/dev/error-page` route (`dev.error-page`) — see LE-02 notes · re-shot and judged 2026-10-13: VISUAL — F-111, F-112 **FIXED** (403/500/503 checked, copy → F-113) |
| LE-03 | D / M | Form validation error in Add-tx / New tally | — | error | [x] VISUAL — F-105 (unblocked 2026-10-11: `FormField` error state) |
| BC-01 | D | `/dev/design-system` base components | — | default | [x] VISUAL — F-106, F-107 |
| BC-01 | M | `/dev/design-system` base components | — | default | [x] PASS (no horizontal overflow at 375px) · (= BC-01 D) |

### App screens with no design counterpart (not compared)

Auth (`auth/*`), `settings/password`, `settings/appearance`, `tallies/Edit`,
`tallies/transactions/Edit`, admin `Dashboard`, marketing `Welcome`.

---

## Findings

**Status convention (2026-10-14).** Every F-, O-, B- and DO- entry starts with exactly one current status: FIXED (date, commit if known), ACCEPTED (with the decision it rests on), REMOVED, or OPEN (with what it waits on). Earlier status lines stay below it as history, prefixed `(superseded)`. Commits are listed only where a commit subject names the item; earlier fixes (2026-09-15 to 2026-10-12) have no commit of their own.

Each finding has one class. `App shot` paths are relative to `.verify/app/`.
Harness note: the Laravel Debugbar bar is hidden in app shots with an injected
`.phpdebugbar{display:none}` style (Playwright only, no code change).

### Batch 1 — Shell + Home

App shots: `desktop/home-0.png`, `home-1.png`, `hover-sidebar-nav.png`, `hover-tally-card.png`,
`hover-tx-row.png`, `home-light-0.png`; `mobile/home-0..3.png`, `home-light-0.png`.

**SH-01 D — sidebar**
- F-001 `VISUAL` **Status: FIXED** (2026-09-15) Sidebar uses `variant="inset"`: its background is `--bg` with no right border, and
  the main area is an inset panel (8px margin, 18px radius, shadow, visible in light). Design: sidebar
  `color-mix(--surface 55%, --bg)` + `1px --line` right border, main flush, no panel. → **FIXED**
  (2026-09-15): `AppSidebar.vue` drops `variant="inset"` (defaults to `sidebar`), so `SidebarInset`'s
  peer-`inset` margin/radius/shadow no longer applies (main is flush) and the primitive's own
  `border-r` renders at the sidebar edge, inheriting the base `border-border` reset (`--line`).
  `app.css` repoints `--sidebar-background` to `color-mix(in oklch, var(--surface) 55%, var(--bg))`,
  the exact design formula. Verified via computed style: background `oklch(0.1825 0.0191 265)`
  (between `--surface` 0.205 and `--bg` 0.155), `border-right: 1px solid oklch(0.29 0.02 265)`
  (= `--line`). Re-screenshotted `desktop/home-0.png`.
- F-002 `VISUAL` **Status: FIXED** (2026-09-15) Width 256px (design `--side: 236px`). Nav items 32px tall, 8px padding, 13px radius;
  design `10px 12px` padding (~40px), `--r-sm` 14px, 19px icons, 11px gap. → **FIXED** (2026-09-15):
  `ui/sidebar/utils.ts` sets `SIDEBAR_WIDTH` to `236px`. `ui/sidebar/index.ts`
  `sidebarMenuButtonVariants` base/default-size classes become `px-3 py-2.5` (12px/10px padding),
  `h-10` (40px), `gap-[11px]`, `rounded-(--radius)` (14px, referencing the existing `--radius`
  token rather than a new literal), `[&>svg]:size-5` (20px; no scale utility hits the design's
  19px exactly, 20px is the closest token). The icon-collapsed rail state keeps a centered icon via
  `p-1.5` (unaffected outside this desktop check). Verified via computed style on the Home button:
  `border-radius: 14px`, `padding: 10px 12px`, `height: 40px`, `gap: 11px`; sidebar width `235.3px`.
  Re-screenshotted `desktop/home-0.png`.
- F-003 `VISUAL` **Status: FIXED** (2026-09-15) Active item icon stays `--text`; design tints the active icon `--accent`. → **FIXED**
  (2026-09-15): `NavMain.vue` adds `data-[active=true]:[&>svg]:text-accent`. Re-screenshotted
  `desktop/hover-sidebar-nav.png`; active icon now `oklch(0.7 0.17 262)` (`--accent` dark),
  confirmed against the design reference.
- F-004 `VISUAL` **Status: FIXED** (2026-10-10; primary tabs; detail/forms deliberately keep the header) Main area starts with a 56px header bar (sidebar trigger + "HOME" breadcrumb). The
  design has no header bar: the page top bar (h1 + actions) is the first thing in `main`. →
  **FIXED for the primary tabs** (2026-10-10): `WebLayout.vue` now renders `AppSidebarHeader` only
  when the page passes a non-empty `breadcrumbs` (`v-if="breadcrumbs.length > 0"`), and
  `Home.vue`, `Stats.vue`, `tallies/Index.vue` stop passing their single-item breadcrumb (and drop
  the now-unused `BreadcrumbItem` import, plus `Home.vue`'s unused `useI18n`). Home / Tallies /
  Stats are now flush: `main` top `0`, no `header.border-b` in the DOM at 1440px; the h1 is the
  first thing in the content area. **Deliberately not removed everywhere:** tally detail, tally
  create/edit, transaction create/edit, settings and admin pages still pass breadcrumbs and keep the
  header, because at `≥ md` it is their only back navigation (verified on the Household detail page:
  header "Tallies › Household" still renders). Its removal there belongs to F-053 (back icon button
  inline with the title) and is left to that fix, so nothing is stranded. The sidebar trigger is
  therefore absent on the primary tabs, same as the design (sidebar width stays media-query driven).
  Re-screenshotted `desktop/home-0.png`, `desktop/tallies-0.png`, `desktop/stats-0.png`;
  `mobile/home-0.png` unchanged (header was already `< md`-hidden).
- F-005 `VISUAL` **Status: FIXED** (2026-10-10) Sidebar layout differs: app puts a full-width accent "Add transaction" button at the
  top and the theme icon button next to the logo; design has no add button in the sidebar (it lives
  in each page's top bar) and a "Theme" nav row at the bottom, above the user block. App also lists
  "You" as a nav item; design reaches You only through the user block. → **FIXED** (2026-10-10):
  `AppSidebar.vue` removes the accent "Add transaction" group (its opener import goes with it; Add
  stays in each page's top bar and on the `n` shortcut, F-013), drops `ThemeToggle` from the logo
  row (the component stays, `MarketingHeader.vue` still uses it), removes the "You" item from
  `mainNavItems` (Home / Tallies / Stats only; You is reached via the user block's Settings link
  until F-007 makes the block link straight to it), and adds a "Theme" row (`Sun` icon,
  `SidebarMenuButton` styled like `NavMain` items: `text-text-2 hover:bg-surface`) in
  `SidebarFooter` between `NavFooter` and `NavUser`, i.e. directly above the user block. It flips
  light/dark via the existing `useAppearance().updateAppearance`, reading the resolved theme from
  `<html class="dark">` so it also works while the preference is `system`. New i18n key
  `navigation.theme` ("Theme" / "Tema") in `en.ts` / `it.ts`. Verified at 1440px: sidebar buttons
  are exactly Home, Tallies, Stats, Theme, user block; clicking Theme goes dark → light → dark and
  persists `localStorage.appearance`; at 900px (md rail) the row collapses to a centered icon above
  the avatar. Re-screenshotted `desktop/home-0.png`, `desktop/home-light-0.png`,
  `desktop/home-rail-0.png`.
- F-006 `VISUAL` **Status: FIXED** (2026-10-11) Brand: lowercase "tallypal" wordmark with accent "pal"; design is a 28px accent mark
  (radius 9px) + "Tallypal" 16px/600 in `--text`. (superseded) **BLOCKED — not fixed (2026-10-10).** Two blockers:
  (1) the mark's `9px` radius has no documented token (`DESIGN_TOKENS.md §4`: `--radius-glyph` is
  `11/14/15px`, `--radius-sm` 14, `--radius-card` 20, no 9), so building it would mean hardcoding
  a radius or inventing a token, which the rules forbid; (2) `AppLogo.vue` is shared with the
  marketing header/footer, auth layouts and the error page, whose logo is a separate visual context
  (`CLAUDE.md`), so the sidebar brand has to be its own component, not an edit to `AppLogo`. Needs an
  owner decision: add a documented `--radius-mark: 9px` (and a `--text-brand` 16/600/`-.02em` step)
  to `DESIGN_TOKENS.md` + `app.css`, then build a sidebar-only brand block. Related, seen while
  verifying F-005: in the md rail (900px) the wordmark is not hidden and spills past the 72px
  column; the design hides `.logo span` there. Fix both together.
  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses `--radius-mark` (9px, `rounded-mark`) and
  `--text-brand` (16px/600/`-.02em`, `text-brand`), both in `DESIGN_TOKENS.md §2.2/§4.1` and wired
  in `app.css`. Build a sidebar-only brand block (not `AppLogo`) and hide the wordmark in the md rail.
  Leftover check (2026-10-11, read-only grep of the repo minus `vendor`/`node_modules`/build output):
  **no `text-brand` usages remain** from the Phase 5 purge; the only hits are the new token in
  `app.css` and these docs. Nothing to repoint, so no finding was opened.
- F-007 `VISUAL` **Status: FIXED** (2026-10-13) User block: app shows initials avatar + name + up/down chevron opening a dropdown
  (`NavUser`); design shows a 34px c1-tinted avatar + name + email (ellipsis) above a `--line` top
  border and navigates straight to You. (superseded) **BLOCKED — not fixed (2026-10-10).** (1) `ui/avatar` has no
  34px size (xs 22 / sm 26 / md 32 / lg 38) and no documented token for it; (2) the dropdown
  (`UserMenuContent`) is the only desktop path to Sign out, so replacing it with a plain link would
  strand logout until the You page (Batch 5, YO-04) carries it. Do after Batch 5 + an owner call on
  the avatar size.
  (superseded) → **UNBLOCKED (2026-10-11), open.** Owner decision D6: no 34px size, use the existing `Avatar
  size="md"` (32px). F-088 has already put Sign out on You, so the sequencing condition is met. The
  user block becomes a plain link to You (avatar, name, email, `--line` top border) and the
  `NavUser` dropdown is removed (`COMPONENT_INVENTORY.md` C2a). The link targets the **new You hub
  route** (F-082), not `settings/profile`.
  → **FIXED** (2026-10-13): `AppSidebar.vue` renders a `Link` to `you.url()` (`Avatar size="md"` with the c1 tint, 13px name, 11.5px email, ellipsis, `border-t border-line`) in place of `NavUser`; in the md rail only the avatar shows. Re-screenshotted `desktop/home-f007.png`, `desktop/home-rail-f007.png`. `NavUser`/`UserMenuContent` stay for `AppHeader`.
- F-008 `MISSING-UI` **Status: FIXED** (2026-09-15) Kbd hint badges (`1`/`2`/`3`, mono 10.5px, bordered) on the nav items. →
  **FIXED** (2026-09-15): `AppSidebar.vue` adds an optional `kbd: '1'|'2'|'3'` to the
  Home/Tallies/Stats nav items (new `NavItem.kbd` field); `NavMain.vue` renders a trailing
  `ml-auto` badge (`font-mono text-[10.5px]`, `bg-surface`, `border-line`, `rounded-[5px]`,
  `px-[5px] py-px`, `text-text-3`) when `item.kbd` is set. "You" has no shortcut in the design, so
  it renders no badge. Verified via computed style: `Geist Mono` `10.5px`, `background-color:
  oklch(0.205 0.02 265)` (`--surface`), `border: 1px solid oklch(0.29 0.02 265)` (`--line`),
  `border-radius: 5px`, `padding: 1px 5px`, `color: oklch(0.6 0.015 265)` (`--text-3`).
  Re-screenshotted `desktop/home-0.png`. (The actual `1`/`2`/`3`/`n` key bindings are still
  unimplemented — see F-013, unchanged.)
- F-009 `MISSING-UI` **Status: FIXED** (2026-10-10) "Tallies" section eyebrow with a `+` link opening New tally (opener
  `useNewTally()` exists, frontend only). → **FIXED** (2026-10-10): `AppSidebar.vue` adds the eyebrow
  row under the primary nav (12px/600/`0.06em` uppercase `text-text-3`, same classes as
  `SectionHeader`; `mx-3 mt-[22px] mb-2` = design `margin:22px 12px 8px`) with a `Plus` icon button
  (`aria-label` "New tally") calling `useNewTally().open()`; hidden in the md icon rail
  (`group-data-[collapsible=icon]:hidden`, design hides `.sec-t` there). Verified at 1440px: font
  `12px`, `text-transform: uppercase`, click opens the New tally dialog; at 900px the row is not
  visible. The per-tally list below it is still F-010/B-01. Re-screenshotted `desktop/tallies-0.png`.
- F-010 `MISSING-UI` **Status: FIXED** (2026-10-13, 9abacf0, 8f7f03f) Per-tally list in the sidebar (22px glyph, name, mono amount, `--pos` when positive).
  **B-01** below. **Owner decision (2026-10-13):** each sidebar tally row shows its first `monthly_totals`
  entry (most-used currency); when a tally has more than one currency, a `+N` marker in `--text-3`
  (N = number of other currencies) is appended and all per-currency amounts go in the row's `title`.
  **Owner decision (2026-10-13, replaces the `+N` marker):** `+N` collides with the `+` sign on
  amounts. After the first amount the row shows the symbols of the OTHER currencies in `--text-3`
  ("+118.40 · $"), only for currencies with a non-zero amount this month; no marker when all others
  are zero. The `title` still lists every currency. (superseded) → **FIXED** (2026-10-13, marker): verified on
  City Break ("+118.40 · $") and Household (no marker, its $ is 0.00) at 1440x900, compared with
  `design/desktop/home-0.png` (`app/desktop/home-f010-marker.png`).
  → **FIXED** (2026-10-13): `AppSidebar.vue` renders the list from `sidebar_tallies` under the Tallies
  eyebrow (rows in Tallies "recent" order, links to the tally detail; hover `--surface`, current tally
  `--surface-2` via `aria-current`; mono 12px amount, `--pos` with a `+` when positive, `--text-3`
  otherwise, bare number as in the design). New `CategoryGlyph` size `xs` (22px / r7 / 11px). Hidden in
  the md icon rail; empty account renders no rows. Verified at 1440×900 dark + light against
  `desktop/home-0.png` and `hover-sidebar-nav.png` (active + hover), Household shows `+1,279.40 +1`
  with title `€ +1,279.40 · $ 0.00`, 900×800 list hidden, `demo-empty` no rows. Screenshots in
  `.verify/app/f010-*.png`. Also fixed the sidebar cache: observers now flush every tally member.

**SH-02 D — hover/active**
- F-011 `VISUAL` **Status: FIXED** (2026-09-15) Nav hover fills `--surface-2` (same as active); design hover is `--surface`, active
  `--surface-2`. Tally-item and user-block hovers can't be checked (F-007, F-010). → **FIXED**
  (2026-09-15): `NavMain.vue` adds `hover:bg-surface` alongside the existing `data-[active=true]:bg-surface-2`
  (twMerge keeps both; verified an inactive hovered item resolves to `--surface`
  (`oklch(0.205 0.02 265)`) and the active+hovered item stays `--surface-2`
  (`oklch(0.255 0.022 265)`), matching the design reference. Re-screenshotted
  `desktop/hover-sidebar-nav.png`.

**SH-03 M — bottom tab bar**
- F-012 `VISUAL` **Status: FIXED** (2026-09-15) Bar is 88px on `--bg 82%` + `blur(24px)`; design is `--surface 88%` + `blur(18px)`,
  `8px` padding around 48px buttons (~64px + safe area). Active tab colours the label `--accent`;
  design keeps the label `--text` and tints only the icon. The Tallies icon is tally marks; design
  uses the same table icon as the sidebar. FAB (52px, accent, glow) matches. → **FIXED** (2026-09-15):
  `BottomNav.vue` background becomes `color-mix(in oklch, var(--surface) 88%, transparent)` with
  `blur(18px)` (was `--bg 82%` / `blur(24px) saturate(160%)`), padding `8px` (`px-2 pt-2` +
  `paddingBottom: calc(8px + env(safe-area-inset-bottom))`, was `12px`/`10px`) and the fixed 88px
  height removed in favour of natural content height (`~68-69px` measured, matching the design's
  `8+48+8` formula); `align-items:center`/`justify-around` added and the FAB's `-mt-1.5` nudge
  removed (no longer needed once the row centers itself), its margin tightened to `4px` a side
  (`mx-1`, was `6px`) to match `.mnav .fab{margin:0 4px}`. `BottomNavItem.vue` now colours the
  wrapper (label) `--text` when active (was `--accent`) and only the icon `--accent`
  (`:class="active ? 'text-accent' : undefined"` on the icon), matching `.mnav button.on{color:
  var(--text)} .mnav button.on svg{color:var(--accent)}`. The tally icon already matched (`Tally5`,
  same as the sidebar) — no change needed there. Verified via computed style on the active Home
  item: label `color: oklch(0.975 0.004 265)` (`--text` dark), icon `color: oklch(0.7 0.17 262)`
  (`--accent` dark), nav `background-color: oklch(0.205 0.02 265 / 0.88)` (`--surface` 88%), bar
  height `69.4px`; inactive items `color: oklch(0.6 0.015 265)` (`--text-3`) on both label and icon.
  Re-screenshotted `mobile/home-0.png` (also `.verify/app/mobile/bottom-nav-crop.png` for a close
  crop).

**SH-04 D — keyboard shortcuts**
- F-013 `MISSING-UI` **Status: FIXED** (2026-09-15) No shortcuts: `2` on `/home` doesn't navigate, `n` opens nothing. Design binds
  `1`/`2`/`3` → Home/Tallies/Stats and `n` → Add transaction, ignored inside inputs. → **FIXED**
  (2026-09-15): `WebLayout.vue` (the single app shell, already hosting the Add-tx/New-tally overlay
  state) adds a `window` `keydown` listener via `useEventListener` (`@vueuse/core`, already a
  dependency): `1`/`2`/`3` call `router.visit()` to `/home`/`tallies.index.url()`/`stats.url()`, `n`
  calls the existing `useAddTransaction().open()`. Ignored when a modifier key is held, the event
  target is an input/textarea/select/`contenteditable`, or either overlay (`useAddTransaction`,
  `useNewTally`) is already open, so it never fights typed digits or in-sheet content. Caught during
  verification: `home.url()` resolves to `/` (the marketing route, `routes/web.php:15`), not the
  dashboard — `1` must use the literal `/home` path, same as `AppSidebar.vue`'s nav item (fixed
  before landing; an earlier version briefly sent `1` to the marketing page). Verified via Playwright
  by pressing `1`/`2`/`3` from other pages and asserting `page.url()` lands on `/home`, `/tallies`,
  `/stats` respectively, and that `n` opens the Add-transaction overlay (`dialogVisible: true`) while
  typing a digit into a text field leaves its value intact and does not navigate. `Esc` was already
  out of scope for this batch (Batch 6) but Radix's own `Escape` handling already closes the
  dialog/sheet, so no regression there. Re-screenshotted `desktop/kbd-shortcut-n.png` (interaction,
  no design reference image exists for this row).

**HO-01 D — top bar**
- F-014 `VISUAL` **Status: FIXED** (2026-09-15) Header order and size: app puts an eyebrow ("Tally · Month") above a 30px h1; design
  has a 26px h1 with the 13px `--text-3` sub below it. The primary "Add transaction" pill is present
  and right-aligned (matches). → **FIXED** (2026-09-15): `HomeHeader.vue` swaps the order (`h1` first,
  the tally/month line now a `<p>` below it), drops the eyebrow styling, and sizes the `h1`
  `text-[24px] sm:text-[26px]` (was `text-[28px] md:text-3xl`) with the sub `mt-[3px] text-[13px]
  text-text-3` (design: `margin:3px 0 0`), matching the exact px values measured on the design's own
  `.top h1`/`.top .sub` rules (`tallypal-desktop.html`). The outer row also changes from
  `flex-col sm:flex-row` to `flex-wrap items-center justify-between` so the header stays a single row
  with the action button vertically centered beside the text block at every width (`.top{align-items:
  center;justify-content:space-between}` in the design), instead of stacking under it on narrow
  screens. Verified via computed style: desktop `h1` `font-size:26px`, sub `font-size:13px`,
  `margin-top:3px`. Re-screenshotted `desktop/home-0.png`.
- F-015 `MISSING-UI` **Status: FIXED** (2026-10-11) _(reclassified from `BACKEND`, 2026-10-11)_ Search pill "Search transactions"
  with a ⌘K hint (`260px`, `--surface`, `--line`, pill). **B-02** is a UI-only stub: build the pill
  **disabled**, with the tooltip "Search coming soon". No endpoint, no route. Frontend only.
  → **FIXED** (2026-10-11): `HomeHeader.vue` adds a disabled pill (`h-10`, `w-[min(260px,22vw)]`, `bg-surface`, `border-line`, `rounded-full`, ⌘K kbd) with the tooltip, hidden below 1001px like the design. New keys `home.search.*`. Re-screenshotted `desktop/home-0.png`, `desktop/home-search-tooltip.png`; `mobile/home-0.png` unchanged.
- F-016 `DESIGN-ONLY` **Status: ACCEPTED** (2026-10-13, DO-01: the bell is left out, no placeholder) Notifications bell icon button. The app has no notification system; the only
  candidate source is pending tally invitations. **DO-01**.
  → **ACCEPTED** (2026-10-13): the bell is left out entirely, with no placeholder and no
  "coming soon" tooltip. Nothing to build.

**HO-01 M — top bar**
- F-017 `VISUAL` **Status: FIXED** (2026-09-15) Eyebrow above a 28px h1; design has a 24px h1 with the sub below. → **FIXED**
  (2026-09-15): same `HomeHeader.vue` change as F-014 (`text-[24px] sm:text-[26px]` resolves to 24px
  below the `sm` (640px) breakpoint). Verified via computed style: `h1` `font-size:24px`, sub
  `font-size:13px` at 375px. Re-screenshotted `mobile/home-0.png`.
- F-018 `MISSING-UI` **Status: FIXED** (2026-09-15) Round 44px accent `+` button top-right (design keeps it next to the bell on
  phones; app hides the header actions below `md`). → **FIXED** (2026-09-15): `HomeHeader.vue`'s
  button wrapper drops `hidden md:flex` (previously invisible below 768px) so the button always
  renders; below the design's own `640px` breakpoint (`.top .rt .btn-p` in the design file) it
  collapses to a `size-11 rounded-full p-0` icon-only circle (`Plus` at `18px`, label hidden), and at
  `sm:` and up returns to the existing pill (`sm:h-12 sm:w-auto sm:px-6`, label shown). Verified the
  button renders and links to `addTransactionUrl` at 375px. Re-screenshotted `mobile/home-0.png`.
- (bell → F-016 / DO-01)

**HO-02 D — balance hero**
- F-019 `VISUAL` **Status: FIXED** (2026-10-10) No hero card: label and amount sit on the page background, and In/Out are two
  separate full-width cards below. Design: one `--surface` card (8 of 12 columns, `26px 28px`
  padding) with the amount on the left and In/Out inline on the right, bottom-aligned. → **FIXED**
  (2026-10-10; single card, the 8-of-12 width waits on F-024): `HomeBalanceGrid.vue` wraps
  `BalanceHero` + `InOutCards` in one `rounded-card bg-surface` card, padding `22px 20px` → `26px 28px`
  at `sm`, grid `1fr` → `minmax(0,1fr) auto` (`gap-8`, `items-end`) from `min-[1101px]` (design
  `.hero` stacks at `≤1100px`). New props: `BalanceHero size="card"` (no outer padding; the tally
  detail's default hero is unchanged) and `InOutCards inline` (card-less pair). The card stays
  full-width until the Quick actions card (F-024) sits beside it. Verified computed style: desktop
  `padding: 26px 28px`, `grid-template-columns: 827.7px 209px`, bg `--surface`
  (`oklch(0.205 0.02 265)`); mobile `22px 20px`, one column. Re-screenshotted `desktop/home-0.png`,
  `mobile/home-0.png`.
- F-020 `VISUAL` **Status: FIXED** (2026-10-10) Amount 52px (design 64px desktop); In/Out values 22px (design 26px). → **FIXED**
  (2026-10-10): `size="card"` amount `text-[64px]` at `sm` (48px below), currency mark `34px` (26px
  below), `mr-2`; inline In/Out value `26px` (20px below), `mt-2`. Verified computed: `64px` /
  `34px` / `26px` at 1440px.
- F-021 `MISSING-UI` **Status: FIXED** (2026-10-11) 10-bar sparkline inside the hero (`--surface-3` bars, current bar `--accent`).
  Data already ships (`weekly_history`), so this is frontend only. **BLOCKED — not fixed
  (2026-10-10).** The bars use a `3px 3px 1px 1px` radius with no documented token
  (`DESIGN_TOKENS.md §4`), so building it would hardcode a radius or invent a token. Needs an owner
  decision: add e.g. `--radius-spark` to `DESIGN_TOKENS.md` + `app.css`. Also: re-add the
  `weekly_history` prop to `Home.vue` (dropped in F-042; the backend still sends 12 weeks, the design
  draws 10, so slice the last 10).
  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses `--radius-spark` (`3px 3px 1px 1px`, `rounded-spark`,
  wired in `app.css`). The `weekly_history` re-add still applies.
  → **FIXED** (2026-10-11): `Home.vue` re-adds `weekly_history`; `HomeBalanceGrid` passes the last 10
  weeks to a new `BalanceHero` `bars` prop (card size only): `flex` row, `gap-1`, `h-[72px]` (56px below
  `sm`), `mt-[22px]` (16px), `bg-surface-3` bars with `rounded-spark`, last bar `bg-accent`, heights
  proportional to the max. Verified 10 bars, radius `3px 3px 1px 1px`, last bar `--accent`; no
  horizontal overflow at 375px. Re-screenshotted `desktop/home-0.png`, `mobile/home-0.png`.
- F-022 `BACKEND` **Status: FIXED** (2026-10-13, 5215b2f) Delta pill "▲ 12.4% vs March" (`--pos` 16% bg). **B-03**.
  **FIXED** (2026-10-13). `balance.previous_period {start,end}` (not cached, so no `KEY_VERSION`
  bump; `null` on the empty payload) feeds a caption via `home.balance.delta_caption` (en `vs Sep 1–13`,
  it `vs 1–13 set`; `_single` when the span is one day). The design puts the caption **inside** the pill
  (`12.4% vs March` in one `.delta`), so it uses `TrendBadge`'s `label`; pill and caption are hidden when
  `delta_percentage` is null. Side-by-side with `.verify/design/` at 1440x900 and 375x812: same pill
  (13px/500, `--pos`/`--neg` 16% fill, 5px 11px padding). Screenshots `{desktop,mobile}/home-f022.png`.

**HO-02 M — balance hero**
- F-023 `VISUAL` **Status: FIXED** (2026-10-10) Same missing card as F-019. Amount 52px (design 48px), In/Out 22px (design 20px).
  At 375px each In/Out value wraps, putting "€" on its own line above the number. → **FIXED**
  (2026-10-10): same change as F-019/F-020. Verified at 375px: amount `48px`, currency `26px`,
  In/Out `20px`, card `22px 20px`, values no longer wrap. Re-screenshotted `mobile/home-0.png`.
- (sparkline → F-021, delta → F-022 / B-03)

**HO-03 D — quick actions**
- F-024 `VISUAL` **Status: FIXED** (2026-10-10) App: a full-width row of 4 vertical tiles (icon over label), no card and no
  heading. Design: a card (4 of 12 columns, beside the hero) titled "Quick actions" with a 2×2 grid
  of horizontal `--surface-2` buttons (34px icon circle, title 13.5px/600, 11.5px sub). →
  **FIXED** (2026-10-10): `QuickActions` gains `variant="card"` (+ `title`, optional per-action `sub`;
  default `row` unchanged for `/dev/design-system`): `rounded-card bg-surface` card, 15px/600 title,
  2×2 `bg-surface-2` buttons (`rounded-(--radius)` 14px, hover `surface-3`, 34px circle), horizontal
  `16px 14px` from `sm`, column `14px 12px` below. `Home.vue` puts it in a 12-col grid beside the hero
  (8/4 at `≥1101px`, stacked below, gap 16/12). New key `home.quick.title`. Subs are not shown: no
  action has one yet (set → F-025). Verified computed: title `15px`, buttons `16px 14px` / radius
  `14px` / `--surface-2`; at 375px `14px 12px`, column. Note: Migration Plan #16 ("keep 4-up") is
  superseded by the Desktop design here. Re-screenshotted `desktop/home-0.png`, `mobile/home-0.png`.
- F-025 `VISUAL` **Status: FIXED** (2026-10-13, 89125b9) Action set: app has Add / New tally / Tallies / You; design has Add / Split /
  Settle / Import.
  (superseded) → **Decided (2026-10-11), open.** Owner decision D5: the final set is **Add / Split / Settle /
  New tally**. Drop You now. Import is cut (DO-02). Settle stays blocked on B-04 and is added when
  B-04 lands (`COMPONENT_INVENTORY.md` B6).
  (superseded) → **FIXED for the current set** (2026-10-11): You removed from `HomeQuickActions.vue` (Add / New tally / Split); Settle is added with B-04. Re-screenshotted `desktop/home-0.png`, `mobile/home-0.png`.
  → **FIXED** (2026-10-13): Settle is the 3rd tile (Add / Split / Settle / New, D5) with the swap icon, same as the design. Compared with `design/{desktop,mobile}/home-0.png`: desktop shows the sub, mobile none, as the design.
- F-026 `MISSING-UI` **Status: FIXED** (2026-10-11) "Split — With a tally" opens Add transaction (frontend only, same opener). → **FIXED** (2026-10-11): `HomeQuickActions.vue` replaces the redundant "Tallies" action with "Split" (`Split` icon, sub "With a tally" via new `home.quick.split`/`split_sub` keys) calling `useAddTransaction().open()`. Verified the tile renders with its sub at 1440px. Re-screenshotted `desktop/home-0.png`. F-025 stays open (Settle → B-04, Import → DO-02).
- F-027 `BACKEND` **Status: FIXED** (2026-10-13, 89125b9) "Settle" with a live sub ("Luca owes 76.30") that opens the tally with the largest
  open balance. **B-04**. (superseded) → **OPEN** (2026-10-13): **B-04 DONE**; Home `settle_suggestion` `{tally_uuid,
  counterparty_name, amount (minor units), currency}` (owner rule: largest single debt owed to the user
  in the primary currency, else the most recently active tally), no UI yet.
  → **FIXED** (2026-10-13), owner rule: with a suggestion the sub is "Noah Whitfield owes 105.85" and the tile opens that tally (verified: Weekend Trip); without one the sub is "All settled" and the tile opens the Tallies list (wired, not screenshotted: the demo data always has a debt, the empty account shows the Home empty state).
- F-028 `DESIGN-ONLY` **Status: REMOVED** (DO-02 CUT) "Import — CSV or receipt". No import exists, and receipt scanning was already
  ruled an unverified claim (#M7). **DO-02**. → **REMOVED** (DO-02 CUT): nothing to build.

**HO-03 M — quick actions**
- F-029 `VISUAL` **Status: FIXED** (2026-10-11) A single 4-up row with no card; design is a card titled "Quick actions" with 2×2
  column-direction buttons and no subs. (Action set → F-025..F-028.) → **FIXED** (2026-10-11): verified, `HomeQuickActions` already renders the titled card with a 2×2 column grid and no subs at 375px (F-024). Re-screenshotted `mobile/home-0.png` vs design.

**HO-04 D — your tallies**
- F-030 `VISUAL` **Status: FIXED** (2026-09-15) Grid is 3 wide columns (~355px cards); design is `auto-fill, minmax(210px,1fr)`
  (5 slots at 1440px, including the New card). Cards have no top row (glyph left, avatars right)
  and no footer row. → **FIXED** (2026-09-15): the card's own top row (glyph + `AvatarGroup`) already
  existed in `TallyRailCard.vue`; the footer (date range / status pill) stays out per DO-03/B-04, so
  only the **grid** was wrong. `HomeYourTallies.vue`'s desktop wrapper changes from
  `hidden grid-cols-2 gap-3 md:grid lg:grid-cols-3` to `hidden gap-3.5 md:grid
  md:grid-cols-[repeat(auto-fill,minmax(210px,1fr))]`, matching the design's own
  `.tallies{grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px}` exactly (Tailwind
  `gap-3.5` = 14px). `TallyRailCard.vue`'s default-variant padding/gap/type also move to the
  measured desktop values (`.tally{padding:18px;gap:16px}` / `.tally .nm{font-size:15.5px}` /
  `.tally .mem{font-size:12px}` / `.tally .v{font-size:22px}`), responsive down to the mobile values
  at `<md` (see F-035). The `new` card's circle grows `34px→38px` and gap `8px→10px`
  (`.tally.new{gap:10px}`), matching `.tally .p{width:38px;height:38px}`. Verified computed style on
  a desktop grid card: `padding:18px`, `gap:16px`, name `font-size:15.5px`, amount `font-size:22px`;
  grid `grid-template-columns` resolves to 5 equal ~214px tracks at 1440px, gap `14px`. Re-screenshotted
  `desktop/home-0.png`.
- F-031 `MISSING-UI` **Status: FIXED** (2026-09-15) Dashed "New tally" card (38px circle `+`, hover `--accent` border and text) at
  the end of the grid, opening New tally. → **FIXED** (2026-09-15): `HomeYourTallies.vue` appends a
  `TallyRailCard` with `variant="new"` (already built, documented in `COMPONENT_INVENTORY.md` B7,
  previously unused anywhere) after the real tally cards in both the mobile rail and the desktop
  grid, labelled `t('tallies.new_tally')` ("New tally") and wired to the existing
  `useNewTally().open()` opener. Verified the dashed card renders at the end of both layouts and
  a click opens the New tally sheet/dialog. Re-screenshotted `desktop/home-0.png`,
  `mobile/home-1.png`.
- F-032 `BACKEND` **Status: FIXED** (2026-10-13, 27cfa4f) Avatar stack (22px, `-7px` overlap, `2px --surface` ring) and split meta
  ("60 / 40 split"). Only `member_count` ships today. **B-05** (superseded) → **DONE** (2026-10-13): Home `tallies[]` carries `members_preview` (max 3, owner first, `{initials, color}`) and `split_label`. **FIXED** (2026-10-13): the data is wired into the existing `TallyRailCard` (`members`, `meta`) and shows the stack + "60 / 40 split" at 1440 and 375; measured 22px avatars, `-7px` overlap, `2px --surface` ring and "60 / 40 split" at 1440 and 375 against `design/{desktop,mobile}/home-0.png`; the tally-footer is F-033/F-034 and the Tallies index row is F-048/F-049.
- F-033 `BACKEND` **Status: FIXED** (2026-10-13, 89125b9) Footer status pill: "Luca owes 76.30" (`pill ok`) or "Settled". **B-04** (same
  per-tally balance). (superseded) → **OPEN** (2026-10-13): **B-04 DONE**; each Home `tallies[]` item carries
  `my_balance[]` (one entry per currency), `TallyRailCard` has no status pill yet.
  → **FIXED** (2026-10-13), owner rule on the first `my_balance` entry: Household "You owe Priya Shah 4,727.74 · $" (neutral, `--text-2`, `$` in `--text-3`), Weekend Trip "Noah Whitfield owes 105.85" (`--pos` chip), Fresh Start / City Break / Solo Savings "Settled". Desktop grid only, as the design hides the footer on the phone rail. Compared with `design/desktop/home-0.png`, `design/mobile/home-0.png`; left meta stays cut (DO-03).
- F-034 `DESIGN-ONLY` **Status: REMOVED** (DO-03 CUT) Footer left meta and the other pills: date ranges ("Apr 04—11", "8 days"),
  "Monthly", "3 invoices" / "1 unpaid", "24 this month" / "−8% vs Mar". Trip dates, recurrence and
  invoices don't exist in the domain. **DO-03**. → **REMOVED** (DO-03 CUT): nothing to build.

**HO-04 M — rail**
- F-035 `VISUAL` **Status: FIXED** (2026-09-15) Rail cards are ~172px wide with 16px padding; design `flex: 0 0 156px`, `14px`
  padding, 18px amount, snap scrolling, bleeding to the screen edge. → **FIXED** (2026-09-15):
  `HomeYourTallies.vue`'s mobile `<Link>` wrapper (and the `new` card) now carry `w-[156px] shrink-0
  snap-start` instead of leaving the width to `TallyRailCard`'s own `min-w`; the component's root
  dropped its fixed `min-w-[172px]`/`min-w-[112px]` in favour of `min-w-0` so the two call sites
  (mobile rail vs. the `w-full` desktop grid cell) each control width independently.
  `TallyRailCard.vue`'s base padding/gap become responsive (`p-[14px] md:p-[18px]`, `gap-3
  md:gap-4`), and the amount responsive (`text-[18px] md:text-[22px]`), matching
  `.tally{padding:14px;gap:12px}.tally .v{font-size:18px}` at ≤640px vs. the desktop values (F-030).
  `TallyRail.vue`'s track gap moves `11px→10px` (design mobile `.tallies{gap:10px}`) and gains
  `snap-x snap-mandatory` (design `scroll-snap-type:x mandatory`; each card's own `snap-start`
  matches `scroll-snap-align:start`). Verified `rail.scrollLeft = rail.scrollWidth` lands past the
  last (`new`) card with no clipping, and each card's bounding box is exactly `156×…`. Re-screenshotted
  `mobile/home-0.png` and the scrolled rail end (`mobile/home-rail-end.png`, not a design-ref frame).
- (New card → F-031, avatars → F-032)

**HO-05 D — recent**
- F-036 `VISUAL` **Status: FIXED** (2026-10-11) Structure: app has an uppercase eyebrow outside a list card; design is a card with a
  15px/600 "Recent" h3 and an "All activity" link in its header, then a 4-column table (Transaction
  / Tally / Date / Amount) with uppercase column headers and `--line` dividers.
  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses `CardHeader` (B15, "Recent" + "All activity") and
  `DataTable` (B17, 4 columns, collapsing to stacked rows ≤ 640px).
  → **FIXED** (2026-10-11): Home Recent is one `rounded-card` with `CardHeader` ("Recent" + "All activity") and a 4-column `DataTable` (stacked ≤ 640px, Tally column hidden ≤ 1100px, tally pill inline on phone). Re-shot `desktop/home-1.png`, `mobile/home-1.png`, `mobile/home-2.png`.
- F-037 `VISUAL` **Status: FIXED** (2026-10-14; glyph 2026-10-11, accent "shared" tally pill 2026-10-14: `is_shared` added to the Home recent payload, `KEY_VERSION` `v8`; compared with `design/{desktop/home-1,mobile/home-2}.png` at 1440×900 and 375×812: shared = accent pill, private = plain text on desktop / neutral pill on mobile) Row content: the icon is a two-letter initials square ("GR"); design uses a
  category glyph character. The sub line reads "date · tally + category pill"; design reads
  "category" with the tally in its own column (accent `pill sh` when shared).
  (superseded) → **DECIDED (owner, 2026-10-11), open.** Category → glyph map, **keyed on `categories.category_id`**
  (the primary key). It is not keyed on the derived two-letter `category_code`: that code collides
  (`HO`, `TR`) and comes from the display name. `category_id` is the only stable key: the table has
  no slug or enum, and `uuid` is random per environment (backfilled with `Str::uuid()` in
  `2026_03_29_185413_add_uuid_to_categories_table`), while ids 1–16 come from the fixed insert order
  in `2025_10_31_102705_create_categories_table`. The payload needs `category_id` next to
  `category_code` (today only the name and code are sent).

  `U+FE0E` = VARIATION SELECTOR-15 (text presentation), appended to every glyph that has an emoji
  form (`Emoji=Yes` in Unicode `emoji-data.txt`).

  | `category_id` | Category | Glyph | Code point(s) | Source |
  |---|---|---|---|---|
  | 1 | Bills | `▤` | U+25A4 | proposed |
  | 2 | Clothing | `✂︎` | U+2702 U+FE0E | proposed |
  | 3 | Dining Out | `☕︎` | U+2615 U+FE0E | design (Restaurants) |
  | 4 | Education | `✎` | U+270E | proposed |
  | 5 | Entertainment | `★` | U+2605 | proposed |
  | 6 | Gifts & Donations | `♥︎` | U+2665 U+FE0E | proposed |
  | 7 | Groceries | `◈` | U+25C8 | design |
  | 8 | Healthcare | `✚` | U+271A | proposed |
  | 9 | Household Supplies | `▦` | U+25A6 | owner |
  | 10 | Insurance | `▣` | U+25A3 | proposed |
  | 11 | Personal Care | `✿` | U+273F | proposed |
  | 12 | Rent | `⌂` | U+2302 | design |
  | 13 | Subscriptions | `♪` | U+266A | design |
  | 14 | Transportation | `◎` | U+25CE | design ("Tram passes") |
  | 15 | Travel | `✈︎` | U+2708 U+FE0E | owner (design tally glyph) |
  | 16 | Utilities | `↯` | U+21AF | proposed |
  | — | Housing | `⌂` | U+2302 | design — see note |
  | any, `type = income` | — | `↓` | U+2193 | design (Salary) |
  | none / unmapped id | — | `◌` | U+25CC | proposed |

  - **Housing has no stable id.** It exists only in the local database (`category_id` 17, created
    2026-09-12). No migration, seeder or code creates it, so it doesn't exist in other
    environments and its id isn't guaranteed. Until a migration seeds it, it falls back to `◌`
    like any unmapped id. When it is seeded, map its new id to `⌂`.
  - Rendering: glyphs render as **text**, tinted by CSS `color` (`--cN`), never as colour emoji
    (`COMPONENT_INVENTORY.md` A11). Glyph colour keeps today's `categoryColorFromString()`.
  - The map lives in one frontend module next to `CategoryGlyph`; it has no backend or schema
    change.

**HO-05 M — recent**
  → **FIXED** (2026-10-11): rows use `categoryGlyph(category_id, type)`; sub reads category only, tally in its own column. The accent `pill sh` for shared tallies is not done: the payload has no shared flag.
- F-038 `VISUAL` **Status: FIXED** (2026-10-11) Rows wrap badly at 375px: the date wraps mid-string ("2026-09-" / "07 ·"), the tally
  name drops to a second line, and the category pill breaks onto two lines ("Personal / Care").
  Design sub is one line of category + tally pill, wrapping only the pill. Header is an eyebrow
  outside the card, not an in-card h3 (F-036).
  → **FIXED** (2026-10-11): `TransactionRow.vue`'s sub line is `flex-wrap` with `gap-y-1`; subtitle and pill
  are `whitespace-nowrap`, so only the pill drops to a second line. `HomeRecentTransactions.vue` now
  sends `date · category` as subtitle and the tally name as the pill (design: category + tally pill).
  Verified at 375px: date and category stay on one line. Re-screenshotted `mobile/home-2.png`,
  `desktop/home-1.png`. **Still open:** F-036/F-039 (in-card h3 header) are **BLOCKED** — neither
  `COMPONENT_INVENTORY.md` nor `ui/` has a card-header component (`SectionHeader` is the eyebrow row);
  F-037 is **BLOCKED** — the design's glyph characters (◈ ⌂) have no documented category mapping.
  _(2026-10-11: F-036/F-039 unblocked by `CardHeader`; F-037's glyph map decided, keyed on `category_id`.)_

**HO-06 D/M — by category**
- F-039 `VISUAL` **Status: FIXED** (2026-10-11) Heading is an uppercase eyebrow outside the card with a month label; design puts a
  "By category" h3 inside the card header. Row anatomy (dot, name, 5px bar, mono value, pct)
  matches.
  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses `CardHeader` (B15): "By category" h3 + the existing
  "Details" link (F-040) in the card.
  → **FIXED** (2026-10-11): `HomeCategoriesBreakdown` is one card with `CardHeader` ("By category", month label + "Details"). Re-shot `desktop/home-1.png`.
- F-040 `MISSING-UI` **Status: FIXED** (2026-09-15) "Details" link to Stats in the card header. → **FIXED** (2026-09-15):
  `HomeCategoriesBreakdown.vue`'s `SectionHeader` `#action` slot now renders the existing month
  label alongside a `Link` to `stats.url()` labelled `t('home.categories.details')` ("Details"),
  styled like the other `SectionHeader` action links (`text-primary`, hover `text-text`). The
  eyebrow-vs-card-h heading style mismatch (rest of this finding's row, F-039) is unchanged — no
  documented desktop `card-h` component exists yet in `COMPONENT_INVENTORY.md` to replace it, so
  only the missing link was addressed. Verified the link navigates to `/stats`. Re-screenshotted
  `desktop/home-1.png`.

**HO-07 D — hover**
- Tally card hover → `--surface-2`: PASS.
- F-041 `VISUAL` **Status: FIXED** (2026-09-15) Transaction row hover shows no fill; design paints a `--surface-2` band across the
  row. → **FIXED** (2026-09-15): `ui/transaction-row/TransactionRow.vue` adds
  `-mx-4 px-4 transition-colors hover:bg-surface-2` when `clickable`, bleeding to the parent
  card's edge; both `bg-surface px-4` wrappers (`HomeRecentTransactions.vue`,
  `tallies/transactions/Index.vue`) gain `overflow-hidden` so the hover band respects the card's
  rounded corners. Applies wherever `TransactionRow` is used as a link (Home Recent, Tally detail
  Transactions). Re-screenshotted `desktop/hover-tx-row.png`.

**HO-08 D/M — light theme**
- PASS: light tokens (`--bg`, `--surface`, `--surface-2`, `--accent`, `--pos`, category hues) match
  `body[data-theme=light]`. Layout gaps are the dark-theme findings above.

**HO-09 D/M — app-only sections**
- F-042 `VISUAL` **Status: FIXED** (2026-09-15) Home also renders a "Last 12 weeks" chart card and a "Shared tally" split card. The
  design has neither: weekly history is the hero sparkline and the split lives on Tally detail. →
  **FIXED** (2026-09-15): `Home.vue` drops both `<HomeWeeklyHistory>` and `<HomeSharedTally>` (and
  their now-unused `weekly_history`/`shared_tally` props and imports); the two-column row below
  "Your tallies" is just `HomeRecentTransactions` beside `HomeCategoriesBreakdown`, matching HO-05/
  HO-06's design placement with nothing in between. Both section components were unused anywhere
  else, so they and their now-orphaned `WeekHistory`/`SharedTally`/`SharedTallyMember` types (minus
  `WeekHistory`, still reused by `pages/stats/types.ts`) were deleted rather than left dead. This
  also removes O-02 (the USD "Weekend Trip" showing `€ 0.00` on the now-deleted Shared tally card).
  The backend still computes and sends both props (`HomeService`/`HomeController` untouched — out of
  scope for a frontend-only pass); they're simply unused by the page now. Re-screenshotted
  `desktop/home-1.png`, `mobile/home-1.png`.

**Observations (outside the visual diff, not classified)**
- O-01 **Status: FIXED** (2026-10-14; `public/favicon.svg` added from `resources/images/logo.svg`, it serves 200 `image/svg+xml`, and /home logs no console error or 4xx response; it was linked in `app.blade.php` and the JSON-LD logo but missing) `GET /favicon.svg` returns 404 on every app page (console error).
- O-02 **Status: FIXED** (2026-10-13, 8806ad0; aggregates and the `created_at` filter closed by O-04, Tallies index by B-06) "Weekend Trip" (seeded in USD) shows `€ 0.00` on its Home card and in the "Shared tally"
  card. Home totals may be dropping non-EUR transactions. Not investigated. (superseded) → **FIXED** (2026-10-13). **Mislabelled + netted, not dropped.**
  Root cause: `TallyRepository::tallySummaries` returned one `monthly_total` per tally, summing
  `amount / 10^decimals` across every currency with no currency attached; `HomeYourTallies` then
  rendered it with the Home-wide `balance.currency_symbol` (the user's most-used currency, EUR).
  USD rows were counted, but labelled `€` and netted with EUR in mixed tallies (Household has
  EUR + USD). Weekend Trip's `0.00` itself is correct: its seeded expenses fall last month.
  Fix: `monthly_totals[]` per currency (never netted); `TallyRailCard` gains `amounts[]`. Demo now
  shows Weekend Trip `$ 0.00`. Not screenshotted: the two-line card (mixed currencies spent in the
  same month) — not reachable in the seed today; covered by `HomeControllerTest`.
  **Same bug, still open:** Tallies index (`withIncomeAndExpenseTotals` → all-time income/expense
  summed across currencies, rendered by `TallyListRow` with its `€` default); Stats and the Home
  balance hero / weekly sparkline / By category (`TransactionStatsRepository` sums all currencies,
  labels with `primaryCurrency`). Also noted, unchanged: the card's month filter uses
  `created_at`, while every other Home total uses `transaction_date`.
  → Home + Stats aggregates and the card's `created_at` filter **FIXED by O-04** (2026-10-13); the
  two-line card is now seeded (DemoSeeder "City Break") and screenshotted under O-04. Tallies index
  still open → **B-06**.
- O-04 **Status: FIXED** (2026-10-13, cdece3a) **Currency rule (owner decision, 2026-10-13)** — recorded as a domain rule in `CLAUDE.md`.
  - Amounts in different currencies are **never added together and never converted**.
  - **Per-tally figures** list one entry per currency (the O-02 `monthly_totals[]` pattern).
  - **Cross-tally aggregates** — Home hero, In/Out, sparkline and By category; Stats chart, KPIs
    and categories — are computed in the user's **primary currency** only: their default currency
    (B-10) if set, otherwise their most-used currency. When other currencies have activity in the
    same range, one quiet line below the figure lists each other currency's total in its own
    symbol, with existing tokens and components. No conversion.
  - Every month or range filter uses `transaction_date`.
  → **FIXED** (2026-10-13). **Root causes:** (1) every `TransactionStatsRepository` aggregate
  (`monthlyBalance`, `weeklyHistory`, `categoriesBreakdown`, `expenseTotal`, `dailyHistory`,
  `monthlyHistory`) summed `amount / 10^decimals` over all currencies with no currency filter, then
  labelled the sum with `primaryCurrency()`'s symbol; (2) `primaryCurrency()` only knew the
  most-used currency, so the B-10 default was ignored; (3) the Home tally card filtered its month on
  `created_at` (entry time) instead of `transaction_date`.
  **Fix:** `primaryCurrency($tallyIds, $preferredCurrencyId)` returns the user default when it
  exists, else the most-used currency; every aggregate takes the primary `currency_id` and filters
  on it; new `otherCurrencyNets()` / `otherCurrencyExpenses()` return one
  `{amount, currency_symbol, currency_code, decimals}` per other currency with activity in the
  range (most-used first). Payload: Home `balance.other_currencies[]` (net per currency, shown under
  the hero figure), Home `categories_other_currencies[]` (month expenses, foot of By category),
  Stats `other_currencies[]` (range expenses, under the chart figure and at the foot of By
  category). UI: `BalanceHero` gains a `#note` slot; the line is 12px `text-text-3` mono
  (`num`), "Other currencies: $ 274.50" (`common.other_currencies`, en + it), net amounts signed
  `−`. The sparkline has no figure, so it gets no line (the hero line above it covers the month).
  The card filter now uses `transaction_date` between month start and today, like every other Home
  total. Changing the default currency (`PreferencesController`) now forgets the Home cache.
  **Cache:** keys are versioned (`home:v2:user:{id}:…`, `HomeCacheService::key()` /
  `KEY_VERSION`) instead of a `cache:clear` before deploy: v1 entries (old balance shape without
  `other_currencies`) are never read and expire on their TTL (≤ 30 min). Bump `KEY_VERSION` on any
  cached-shape change.
  **Not changed:** Tallies index (`accessibleTalliesWithTotals()`, B-06); the unrendered
  `shared_tally` Home prop (`sharedTallySummary()` still sums currencies; no view reads it since
  F-042 — remove it or make it per-currency before any reuse); `TallyRailCard` "N members" counts the
  owner twice (owner is in `tally_user` *and* `+1`), pre-existing, visible on every card.
  **Tests (written first, failing):** `HomeControllerTest` (mixed EUR/USD month for hero, In/Out,
  sparkline, By category; user default vs most-used; no line when single-currency; tally card dated
  last month but entered this month), `StatsControllerTest` (mixed month on month/week/year; user
  default), `TransactionStatsRepositoryTest` (primary selection incl. unknown/unused default; each
  aggregate's currency filter; other-currency nets/expenses), `TallyRepositoryTest`
  (`transaction_date` bounds), `PreferencesUpdateTest` (cache dropped), `Home{,Cache}ServiceTest`,
  `StatsServiceTest`, `DemoSeederTest` ("City Break" spends EUR + USD this month).
  **Harness:** DemoSeeder adds **"City Break"** (`c2` ◎): EUR 86.40 + USD 210.00 on the 1st, EUR
  32.00 + USD 64.50 today, so the card shows `€ 118.40` / `$ 274.50` whatever the day.
  Screenshots (dark, 375×812 and 1440×900): `desktop/home-o04-0.png`, `mobile/home-o04-0.png` (hero
  `€ 1,239.21` + "Other currencies: $ −274.50"), `{desktop,mobile}/home-o04-city-break-card.png` +
  `-crop.png` (two-currency card), `desktop/home-o04-categories.png` (By category footer line),
  `{desktop,mobile}/stats-o04-0.png` (chart figure + By category footer, "$ 274.50").
  **Done check (2026-10-13):** `npm run build`, `vue-tsc`, `composer test` (579 tests, coverage
  97.9%) and `composer test:mutate` (1026 tested, 28 uncovered, 3 timeout, **score 97.35%**) all pass.
  Two pre-existing flaky assertions fixed on the way (`WebTransactionControllerTest`,
  `Transactions/IndexTest`: a whole-number factory amount comes back as JSON `int`, failing a strict
  `float` compare; now compared numerically). Note: after changing method signatures the `--tia`
  coverage replay reported a false 88.2%; `composer test:unit -- --fresh` re-records the graph.
- O-05 **Status: FIXED** (2026-10-13, 5215b2f; notifying other members ACCEPTED) **Deleting a shared tally as its owner (report only, 2026-10-13, no fix)** — tried through the
  UI as `demo@tallypal.test` on the shared Household tally (Tally edit → Delete tally → confirm).
  **Outcome: it works, no 500.** The dialog confirms, `DestroyTallyAction` deletes the transactions,
  detaches the members, deletes the tally in one DB transaction, and the user lands on `/tallies`
  (4 ledgers · 1 shared); no orphan `transactions` / `tally_user` rows. `tally_invitations.tally_id`
  is `on delete cascade`; `transactions.tally_id` is `no action`, hence the explicit delete first.
  **Gaps noticed (not fixed):** the other members get no notice and lose their data silently; the
  dialog copy does not say the tally is shared or that members lose access; there is no success
  flash after the redirect; the action's `catch` turns any failure into `abort($e->getCode(), …)`
  (code `0` for a DB error, an invalid HTTP status, so a real FK failure would surface as a 500 with
  the raw message, not a clear one). Screenshot: `app/desktop/tally-delete-shared-o05.png` (confirm
  dialog). DemoSeeder re-run afterwards.
  **Follow-up (2026-10-13):** the dialog now says so when the tally has other members ("Household is
  shared with 2 other members. Deleting it removes it for everyone.", en + it, plural) via the new Edit
  prop `other_members_count`; deleting redirects with `status=tally-deleted` and `/tallies` shows
  "Tally deleted." in the existing `status` style (`text-pos`; no global flash exists). Screenshots
  `desktop/tally-delete-shared-o05-fixed.png`, `desktop/tally-deleted-flash.png`.
  **ACCEPTED:** notifying the other members stays out (no notification system; DO-01/DO-05).
  The `abort($e->getCode())` gap remains O-06.
- O-07 **Status: FIXED** (2026-10-13, 5215b2f) **Auth form inputs had no `name` attribute — FIXED** (2026-10-13). Login, Register, Confirm
  password and the settings password form had `id` + `autocomplete` but no `name` (Forgot/Reset already
  had it); password managers and the harness `input[name=…]` selectors missed them. Added `name` to all,
  and Forgot password's email is `autocomplete="email"` (was `off`). Matrix: login `email` /
  `current-password`; register `name` / `email` / `new-password`; forgot + reset `email` /
  `new-password`; settings password `current-password` / `new-password`. Resume-notes login recipe is back
  on `input[name=email]` / `input[name=password]` and logs in to `/home`.
- O-06 **Status: FIXED** (14 of 14 actions fixed: 2026-10-13 `GetTallyDebtsAction`, `GetTallySplitAction`; 2026-10-14 the other 12) **`abort($e->getCode(), …)` in action catch blocks (record only, 2026-10-13, no fix; fixed after
  B-09)** — 14 actions wrap their body in `try { … } catch (Throwable $e) { abort($e->getCode(),
  $e->getMessage()); }`. `getCode()` is only an HTTP status when a deliberate `HttpException`-style
  throw set one, so every other failure is mis-mapped. Actions (all `catch (Throwable)`):
  - `Tally/StoreTallyAction`, `Tally/UpdateTallyAction`, `Tally/DestroyTallyAction` (inside `DB::transaction`)
  - `Transaction/StoreTransactionAction`, `UpdateTransactionAction`, `DestroyTransactionAction` (inside `DB::transaction`)
  - `Transaction/GetTallyTransactionsAction`, `GetTallyTotalsAction`, `GetTallySplitAction`, `GetTallyDebtsAction` (reads)
  - `TallyShare/CreateTallyShareAction`, `UpdateTallySharesAction`, `DeleteTallyShareAction`, `AcceptTallyInvitationAction`

  **What a database error returns, from each of the 14:** a `QueryException` carries the PDO
  SQLSTATE *string* as its code, so `abort()` never produces a clean status. Checked with `abort()` in
  tinker on SQLite: `HY000` (generic, missing table…) → `TypeError` ("HttpException::__construct():
  Argument #1 ($statusCode) must be of type int, string given") → **500**, original message lost.
  A numeric SQLSTATE such as `23000` (integrity violation, MySQL) is coerced to int and builds an
  `HttpException` with status `23000`, an invalid HTTP status (not exercised end to end; expect a
  500 raised while building the response). Either way the client never sees a 409/422/503, and with
  `APP_DEBUG` the raw SQL leaks in the message. A non-DB `Throwable` with code `0` (e.g.
  `RuntimeException`, `ModelNotFoundException`) becomes `HttpException(0)`, also an invalid status.
  Corrects O-05, which said code `0` for a DB error. Also: any `HttpException` thrown inside the try
  (403/404) has `getCode() === 0`, so it is downgraded the same way.
  **Target fix:** `catch (Throwable $e) { report($e); abort(500); }` — the original exception is logged
  by the handler, the client gets a plain 500 with no SQL in the message.
  **Done (2026-10-13, with B-04):** `GetTallyDebtsAction` (the catch is in `ledgers()`) and
  `GetTallySplitAction`; tests drop a table, then assert an `HttpException` 500 whose message does not
  name it and `Exceptions::assertReported(QueryException)`. Before the fix the same test got a
  `TypeError` (SQLSTATE `HY000` passed to `abort()`).
  **Done (2026-10-14):** `Tally/StoreTallyAction`, `UpdateTallyAction`, `DestroyTallyAction`,
  `Transaction/StoreTransactionAction`, `UpdateTransactionAction` (each: `report($e); abort(500);`; none
  throws a deliberate 4xx, so none needed one kept). Each has a test that drops `tallies` or
  `transactions` and asserts an `HttpException` 500 without the table name plus
  `Exceptions::assertReported(QueryException)`; all five failed before the fix with the same `TypeError`.
  **Done (2026-10-14, last 7):** `Transaction/DestroyTransactionAction`, `GetTallyTransactionsAction`,
  `GetTallyTotalsAction`, `TallyShare/CreateTallyShareAction`, `UpdateTallySharesAction`,
  `DeleteTallyShareAction`, `AcceptTallyInvitationAction` (each: `report($e); abort(500);`).
  `AcceptTallyInvitationAction` keeps its deliberate 404: `firstOrFail()` on a missing tally
  (`ModelNotFoundException`) is caught first and aborts 404, unreported. Test method: from here on the
  error is forced portably (O-06 tests no longer drop a table, which only rolls back on SQLite): the
  `databaseFailure()` helper in `tests/Pest.php` builds a `QueryException`; transactional actions get
  `DB::partialMock()->shouldReceive('transaction')->andThrow(...)`, the others a partial-mocked model
  whose relation throws. Each asserts a 500 `HttpException` without the SQL and
  `Exceptions::assertReported` of that same exception; all 8 new tests failed before the fix (status 0 ≠ 500/404).
  **Deliberate behaviour change (2026-10-14):** `AcceptTallyInvitationAction` now returns **404** for a
  missing or already used invitation (its tally gone → `ModelNotFoundException`), where it previously
  returned **500** (the old `abort($e->getCode(), …)` turned every failure into a 500/`TypeError`). This is
  a fix, not a regression; the 404 is unreported.
  **Follow-up (2026-10-14):** `GetTallySplitAction` and `GetTallyDebtsAction` tests no longer drop a
  table: they use `databaseFailure()` with a partial-mocked `Tally::users()` / `DB::table()`, and assert
  the same reported exception. Store/Update/DestroyTallyAction and GetTallyTotalsAction got happy-path
  tests: the mocked failure tests no longer ran their bodies, which left their mutants uncovered.
- O-08 **Status: ACCEPTED** (deferred follow-up; the 0% rule keeps balances summing to zero) **Leaving a tally loses the member's share history (follow-up, record only, 2026-10-13, no
  fix)** — `DeleteTallyShareAction` detaches the member and redistributes their
  `default_share_percentage` to the others; nothing keeps the share they had. Their past transactions
  stay, so B-04's netting counts them at a 0% share: a member who leaves is owed everything they paid
  net, and the others now carry the whole cost of the period they shared. Their share should be
  preserved when they leave (e.g. kept on a left-at pivot or a share history), so their open balance is
  computed with the share they actually had.
- **Status: not raised** (report only; no O-09 was opened) **Money column check (report only, 2026-10-13) — no O-09.** The only money column is
  `transactions.amount`, `bigInteger` (integer minor units, `2025_11_01_182448_create_transactions_table`);
  no money column is `float`/`double` (or `decimal`). `tally_user.default_share_percentage` is
  `decimal(5,2)` (a percentage, not money). Note: `Transaction`'s `amount` mutator writes `amount ×
  10^decimals` as a PHP float (e.g. `33.33 × 100 = 3332.9999999999995`) and relies on the column to store
  an integer; MySQL rounds it on insert, SQLite may keep a REAL. B-04's ledger reads `ROUND(amount)`, so
  this cannot move a cent there. The demo DB (SQLite) holds 84 rows, all stored as integers.
- **Balance comparison (report only, 2026-10-13)** — `php artisan tallypal:compare-balances` (read-only,
  `app/Console/Commands/CompareBalancesCommand.php`) runs, for every tally, a verbatim copy of the
  pre-B-04 `handle()` and the new `ledger()`, and compares each user's net implied by the settlements
  (minor units). Demo data (8 tallies): **0 tallies change, 0 settlement blocks change**, largest
  difference `€ 0.00` and `$ 0.00`. The owner runs it on production before deploying; expected sources
  of difference there: members who left with transactions (now a 0% creditor or debtor), owners without
  a `tally_user` row, shares not summing to 100, sub-cent rounding.

### Batch 2 — Tallies list

App shots: `desktop/tallies-0.png`, `desktop/tallies-hover.png`, `mobile/tallies-0.png`.

**TA-01 D — top**
- F-043 `VISUAL` **Status: FIXED** (2026-10-13, 27cfa4f; "€ tracked" part ACCEPTED, O-04) (superseded) **FIXED for the header (2026-10-10)** — `tallies/Index.vue` drops the eyebrow, the
  "Your tallies." h1, the paragraph and the divider rule for a "Tallies" h1 (24px, 26px ≥ `sm`) with a
  13px `text-text-3` sub (`mt-[3px]`), one flex-wrap row with the button right-aligned/vertically
  centered. The sub shows only "N ledgers" (new `tallies.ledgers` plural key); "· N shared · € tracked"
  needs member counts and a cross-currency total the index doesn't send → B-06 ((superseded) **DONE** 2026-10-13, F-043 sub stats **FIXED** 2026-10-13).
  → **FIXED** (2026-10-13): `tallies/Index.vue` sub reads "5 ledgers · 2 shared" (`tallies.shared_count`, en/it); compared with `design/{desktop,mobile}/tallies-0.png`, the other viewport unchanged. **Owner decision (2026-10-13):** the sub is "N ledgers · N shared". The design's "€ tracked" total is dropped because it adds currencies together (O-04): that part is **ACCEPTED** (design-only, not built). Verified h1 `26px`
  (1440) / `24px` (375), no `border-b` rule. Re-screenshotted `desktop/tallies-0.png`,
  `mobile/tallies-0.png`. Original finding: Header: app shows a mono eyebrow, a 30px "Your tallies." h1, a two-line description
  paragraph and a divider rule. Design: a 26px "Tallies" h1 with a one-line 13px stats sub ("5
  ledgers · 2 shared · € 5,282 tracked") and no rule. The primary "New tally" pill matches.

**TA-01 M — top**
- F-044 `VISUAL` **Status: FIXED** (2026-09-15) Same header difference (F-043). App adds a sticky full-width "New tally" bar above
  the tab bar that the design doesn't have. The plan removed sticky bottom add bars in Phase 3. →
  **FIXED** (2026-09-15): `pages/tallies/Index.vue` removes the `fixed inset-x-0 bottom-[...] md:hidden`
  bar entirely; the page's existing `pb-28` bottom padding (shared with every other page, for the
  bottom tab bar) already accounted for its own clearance, so no layout shift elsewhere.
- F-045 `MISSING-UI` **Status: FIXED** (2026-09-15) Round 44px accent `+` top-right, opening New tally. → **FIXED** (2026-09-15):
  the header's button wrapper drops `hidden md:block`; the `Button` now always renders (guarded by
  the existing `tallies.length > 0` check, moved from the `Button` to its wrapper `div`), collapsing
  to a `size-11 rounded-full p-0` icon-only circle below `sm` (640px) and the existing pill
  (`sm:h-12 sm:w-auto sm:px-6`) at `sm:` and up — same pattern as `HomeHeader.vue` (F-018), still
  linking to `tallyRoutes.create.url()` (the full-page route, consistent with this page's other
  "New tally" entry points, none of which use the `useNewTally()` sheet opener). The header's own
  column-stack layout (F-043, unchanged) means the button sits below the text block rather than
  strictly top-right at narrow widths — full row positioning is deferred to the F-043 fix, which also
  restructures the eyebrow/divider. Verified the round button renders, links correctly, and the
  sticky bar is gone at 375px. Re-screenshotted `desktop/tallies-0.png`, `mobile/tallies-0.png`.

**TA-02 D/M — filter + sort**
- F-046 `BACKEND` **Status: FIXED** (2026-10-13, 27cfa4f) Segmented "All / Shared / Private" plus an outline "Sort: Recent" button (full
  width and stacked on mobile). They're absent, and the payload can't drive them: the index sends
  neither member count nor last activity. **B-06**.
  → **FIXED** (2026-10-13): `tallies/Index.vue` renders `SegmentedControl` (`variant="mobile"`, inline from `sm`, full width below) All / Shared / Private plus an outline `Button` "Sort: Recent" opening a `DropdownMenuRadioGroup` (Recent / Name), stacked full width on mobile. Client-side on `is_shared`, `last_activity_at`, `name`. Verified All → Shared (Household, Weekend Trip) → Private (Fresh Start, City Break, Solo Savings) → All (5), and Name sort. Compared with `design/{desktop,mobile}/tallies-0.png`.
  **Owner decision (2026-10-13):** the Sort menu offers **Recent** and **Name** only. **Amount** is dropped: it would rank tallies across currencies (O-04).
- F-047 `DESIGN-ONLY` **Status: ACCEPTED** (DO-04: no Archived segment) "Archived" segment. Tallies can't be archived today. **DO-04**.

**TA-03 D — rows**
- F-048 `VISUAL` **Status: FIXED** (2026-10-13, 91d30fe) Layout: app renders a bordered 3-column card grid (square corners, IN/OUT totals per
  card, description, share icon button on each card, dashed "Start a new tally" cell). Design
  renders full-width `--surface` rows (`--r` 20px, `16px 20px`, 10px gap) on a 5-column grid: 44px
  glyph · name + avatars + meta · type pill · transaction count · amount + status. Neither the
  per-card share button nor the "Start a new tally" cell is on the design's Tallies screen.
  (superseded) → **FIXED** (2026-10-11, rows only): `tallies/Index.vue` drops the bordered 3-column card grid, the per-card IN/OUT totals, description, share button and the dashed "Start a new tally" cell, and renders the existing `TallyListRow` at every width (`TallyListRow` padding `16px 20px` from `md`, was `15px 16px`). Verified at 1440px: rows 1140px wide, `padding: 16px 20px`, radius `20px`; 375px unchanged. Unused `TallyShareDialog` wiring removed from the page. Type pill, avatars, tx count and status → F-049/F-050 (B-06, B-04, B-05). **B-06 DONE** (2026-10-13) — F-048 **OPEN** for the type pill and tx-count cells (data in the payload). Re-screenshotted `desktop/tallies-0.png`, `mobile/tallies-0.png`.
  → **FIXED** (2026-10-13): `TallyListRow` gains `typeLabel`/`typeShared`/`detail` (`Badge` `pill`/`accent`, fixed `w-40` cells, `hidden md:`) so the desktop row is glyph · name + avatars + sub · type pill · "N transactions" · amount column (`md:w-36`); mobile keeps glyph · name/sub · amount. Compared with `design/{desktop,mobile}/tallies-0.png` at 1440 and 375 (`app/{desktop,mobile}/tallies-f048.png`); mobile unchanged. Status line → F-050.
- F-049 `BACKEND` **Status: FIXED** (2026-10-13, 91d30fe) Every tally reads "just you", including shared ones (Household is 3 members on
  Home). `TallyController::index()` never sends `users_count`/`users`, so `memberLabel()` always
  falls back. The row also needs the avatar stack, the type pill ("Shared · 60/40" / "Private") and
  "N transactions". **B-06** (+ B-05 for avatars and split label).
  (superseded) → **B-06 DONE** (2026-10-13) — **OPEN** (unblocked): the row now gets the member count, avatar stack and "60 / 40 split" via the existing `TallyListRow` props; the type pill and transaction-count cells are still to build (`transactions_count`, `is_shared` are in the payload).
  → **FIXED** (2026-10-13): pill reads "Shared · 60/40" (from `split_label`), "Shared" or "Private"; "N transactions"; the amount column renders one line per currency from `totals[]` (net, most-used first, same as `TallyRailCard` `amounts[]`, O-02/O-04). Verified on Household (€ 6,825.35 + $ 320.00) and City Break at 1440 and 375; Shared → Household, Weekend Trip; Private → Fresh Start, City Break, Solo Savings; All → 5.
- F-050 `BACKEND` **Status: FIXED** (2026-10-14, e6cee5b) Right-column status line ("Luca owes 76.30" in `--pos`, "Settled"). **B-04**.
  (superseded) → **OPEN** (2026-10-13): **B-04 DONE**; Tallies index items carry `my_balance[]`; `TallyListRow`'s
  existing `status`/`statusOwe` props are not wired yet (copy and multi-currency choice pending).
  → **Owner decision** (2026-10-14): below 640px (`sm`) the Tallies row status uses a short form without the
  name: "Owes you 76.30" / "Ti deve 76.30", "You owe 20.00" / "Devi 20.00", "Settled" / "In pari", same tones and
  the "· $" marker; desktop (≥ `sm`) keeps the full text ("Luca owes 76.30"). Multi-person statuses ("2 people owe
  you …") carry no name, so they stay as is.
  (superseded) → **FIXED** (short form, 2026-10-14): `balanceStatus().shortLabel` + `TallyListRow` `statusShort` (`sm:hidden` /
  `hidden sm:inline`), keys `tallies.status.short_owes_you` / `short_you_owe` (en + it). Verified at 375×812: Household
  "You owe 4,692.24 · $" stays on one line (row height 16.5px for all six rows); 1440×900 shows the full text.
  Screenshots `.verify/app/mobile/tallies-status-short.png`, `.verify/app/desktop/tallies-status-full.png`.
  → **FIXED** (2026-10-14): `TallyListRow` takes `statusTone` (`pos` / `neutral` `--text-2` / `settled` `--text-3`) and `statusExtra` ("· $") in place of `statusOwe`; `tallies/Index.vue` feeds it `balanceStatus()` (same helper as F-033). Verified at 1440 and 375 on Weekend Trip ("Noah Whitfield owes 105.85", `--pos`), Household ("You owe Priya Shah 4,692.24 · $", neutral, wraps in a `max-w-36` cell on mobile), private tallies ("Settled"). en/it singular + plural in `tallies.status.*`.

**TA-03 M — rows**
- Row anatomy (44px glyph, name, sub, mono amount right) and spacing: PASS.
- (Sub shows "just you" → F-049; status line → F-050. The dashed "Start a new tally" row isn't in
  the design → F-048.)

**TA-04 D/M — archived**
- F-051 `DESIGN-ONLY` **Status: ACCEPTED** (DO-04: no archived row styling) Archived row at `opacity: .6` with an "Archived" pill. No archive state
  exists. **DO-04**.

**TA-05 D — hover**
- Card/row hover → `--surface-2`: PASS.

### Batch 3 — Tally detail

Checked on the shared "Household" tally (2 members + settlement, EUR + USD). App shots:
`desktop/detail-0..2.png`, `desktop/detail-light-0.png`, `mobile/detail-0..3.png`.

**TD-00 D — page layout**
- F-052 `VISUAL` **Status: FIXED** (2026-10-11) App stacks everything in one full-width column (hero(s) → In/Out cards → settle
  banners → split blocks → transaction list). Design uses the 12-column grid: a 5-column stack
  (Total spent card, settle banner, Split so far card) beside a 7-column Transactions card. → **FIXED** (2026-10-11): `tallies/transactions/Index.vue` wraps the body in a 12-col grid (`min-[1101px]:grid-cols-12`, gap 16): left `col-span-5` stack (hero(s), In/Out, settle banners, Split so far), right `col-span-7` Transactions section; stacks ≤1100px like the design. Verified 1440px: 12 tracks, two columns. Re-screenshotted `desktop/detail-0.png`.

**TD-01 D — header**
- F-053 `VISUAL` **Status: ACCEPTED** (2026-10-11; the Edit/gear button is the only path to tally edit, design has none) Back is a "TALLIES › HOUSEHOLD" breadcrumb in the 56px header bar (plan decision
  #15); design uses a 40px round back icon button inline before the title at every width. App's
  h1 is 30px with a divider rule below (design 26px, no rule). Actions: app shows ghost "Share",
  ghost "Edit" and primary "Add transaction". Design shows an **outline** (`btn-o`, `--line`
  border) "Share" and primary "Add expense", with no Edit. → **FIXED** (2026-10-11): desktop header is one row: 40px round back `Link` (`bg-surface`, `border-line`, chevron) to Tallies, 26px h1 + 13px one-line sub (`mt-[3px]`), no rule, outline Share, primary "Add expense" (new `transactions.add_expense`, new `common.back`). Edit stays as an outline icon button (design has none, but it is the only path to tally edit). `WebLayout` no longer gets breadcrumbs here, so the 56px header bar is gone (verified: no `header.border-b`). Glyph and member sub wait on B-07. Re-screenshotted `desktop/detail-0.png`.
- F-054 `BACKEND` **Status: FIXED** (2026-10-13, 9d6bd4c) 48px tally glyph square (`--cN` 22% fill) is missing because
  `TransactionController::index()` sends `tally->only(['tally_id','uuid','name','description','owner_id'])`
  without `color`/`glyph`. The meta sub ("3 members") also needs a member count. **B-07** (superseded) → **DONE** (2026-10-13): `tally.color/glyph/users_count` are sent. **PARTLY FIXED** (2026-10-13): sub shows the member count ("2 members" / "just you"), description dropped. **Owner decision (2026-10-13):** `CategoryGlyph` gains `xl` (48px / r16 / 20px) and `header` (40px / r13 / 17px), documented in A11. → **FIXED** (2026-10-13): glyph rendered from `tally.color/glyph` (name initial fallback; text presentation forced with U+FE0E). Compared `desktop/detail-f058-*.png` with `design/desktop/detail-0.png`.
  (The date range in the sub → DO-03.)

**TD-01 M — header**
- F-055 `VISUAL` **Status: ACCEPTED** (2026-10-13, 9d6bd4c; the Edit/gear button is the only path to tally edit, design has none) App: back + h1 + two-line description + round share + round settings (gear) icon
  buttons, plus a sticky full-width "Add transaction" bar above the tab bar. Design: back + 40px
  glyph + 20px h1 + one-line sub, then round Share and round **accent** `+` buttons, and no sticky
  bar. (superseded) **B-07 DONE (2026-10-13). PARTLY FIXED** (2026-10-13): one-line member sub on mobile; glyph blocked with F-054 (no 40px detail-header glyph size). Reopened 2026-10-13 after side-by-side: no glyph, and the actions sat inline so the title truncated ("House…"); design wraps them to a right-aligned second row. → **FIXED** (2026-10-13): 40px glyph (`header`), `DetailHeader` wraps actions onto their own right-aligned row. Compared `mobile/detail-f058-*.png` with `design/mobile/detail-0.png`. Known gap: the gear (Edit) button is kept (design has none).
- F-056 `MISSING-UI` **Status: FIXED** (2026-10-11) Round 40px accent `+` (Add expense, preselecting this tally) in the header. → **FIXED** (2026-10-11): mobile `DetailHeader` actions lead with a round `icon-lg` (40px) accent `+` linking to the tally's create-transaction route; the sticky full-width "Add transaction" bar (F-055) is removed (0 `.fixed.z-30` bars in the DOM at 375px). F-055 stays partly open: the gear (Edit) button is kept because it is the only mobile path to tally edit; glyph + one-line sub still wait on B-07. Re-screenshotted `mobile/detail-0.png`.
- (glyph → F-054 / B-07)

**TD-02 D — total hero**
- F-057 `VISUAL` **Status: FIXED** (2026-10-11) App: an uncarded "Net Balance" amount plus two full-width "Total income / Total
  expenses" cards, **repeated per currency** (EUR block, then USD block). Design: a single
  `--surface` card "Total spent" with 64px amount and a 2-up In/Out-style row ("Per person",
  "Days") inside the card. Multi-currency needs its own treatment (design is single-currency);
  stacking two full heroes doubles the page height before any content. → **FIXED** (2026-10-11): `tallies/transactions/Index.vue` renders one `rounded-card bg-surface` card (`26px 28px`, `22px 20px` on phones) with a `BalanceHero size="card"` + inline `InOutCards` block per currency, separated by a `--line` rule. "Per person"/"Days" stay out (B-07/DO-03). Re-screenshotted `desktop/detail-0.png`.
- F-058 `BACKEND` **Status: FIXED** (2026-10-13, 9d6bd4c) "Per person" value (total ÷ members), which needs member count. **B-07** (superseded) → **DONE** (2026-10-13): `per_person[]` sent, one entry per currency. **Owner decision (2026-10-13):** shared tallies (`users_count > 1`) follow the design: a "Total spent" hero (expenses only) with "Per person" beside/below it, both per currency (O-04). Private tallies keep Net balance + In/Out: **ACCEPTED** (the design shows no private tally). → **FIXED** (2026-10-13): Household and Weekend Trip show Total spent + Per person per currency; Solo Savings and City Break (both `users_count = 1` in the demo seed, so not shared) keep Net Balance + In/Out. "Days" stays cut (DO-03). Compared with `design/{desktop,mobile}/detail-0.png`.
- F-059 `DESIGN-ONLY` **Status: REMOVED** (DO-03 CUT) "Days" (trip length). **DO-03**. → **REMOVED** (DO-03 CUT): nothing to build.

**TD-02 M**
- F-060 `VISUAL` **Status: FIXED** (2026-10-11) Same structure issue as F-057. At 375px the In/Out card values wrap "€" onto its own
  line (as in F-023). → **FIXED** (2026-10-11): same change as F-057; at 375px In/Out values no longer wrap. Re-screenshotted `mobile/detail-0.png`.

**TD-03 D/M — settle banner**
- F-061 `VISUAL` **Status: FIXED** (2026-10-14, e6cee5b) App banner is a single line of plain 14px text ("You owe Priya Shah € 4,692.24"),
  one banner per settlement per currency, with no amount emphasis. Design: `--accent` 15% card,
  `18px 20px` padding, "**Luca** owes you" with the amount in mono `--accent` 600, a second
  `--text-3` line ("Sara is settled"), and a right-aligned small primary button (full-width below
  the text on mobile). Light-theme fill colour matches. (superseded) → **FIXED** (2026-10-11): `SettleBanner` padding `18px 20px`, 14px text, accepts `class` (merged via `cn`); detail page renders names bold and the amount in mono `text-primary` through `<i18n-t>` slots, one stack gap instead of `mt-[14px]`. Remind → DO-05 (cut); the "X is settled" second line is not built (needs per-member settled data). Verified computed padding `18px 20px`. Re-screenshotted `desktop/detail-0.png`, `mobile/detail-0.png`.
  (superseded) → **OPEN** (2026-10-13): **B-04 DONE**; the detail page receives `settled_members[]` (per currency, current members at exactly zero), the second banner line is not built yet.
  (superseded) → **BUILT, NOT VERIFIED** (2026-10-14): `SettleBanner` gets a `detail` slot (12.5px `--text-3`); `transactions/Index.vue` fills it on the first banner of each currency from `settled_members` ("Sara is settled" / "Sara and Marco are settled" / "A, B and C are settled", en + it). The demo has only 2-member tallies, where nobody is settled while a debt is open, so the line can't be screenshotted: stays OPEN until a ≥3-member fixture shows it. Household banners re-screenshotted at 1440 and 375: unchanged.
  → **FIXED** (2026-10-14): verified on the new Ski Cabin demo tally (`…0006`, EUR, 40/30/30: Priya owes 40.00, Noah settled). Banner reads "**Priya Shah** owes you € 40.00" with the amount mono `--accent`, second line "Noah Whitfield is settled" in 12.5px `--text-3`, same card fill/padding as `desktop/detail-0.png` and `mobile/detail-0.png` side by side; only differences are the known DO-05 (no Remind button) and the unrelated header. Screenshots: `.verify/app/{desktop,mobile}/detail-skicabin.png`.
- F-062 `DESIGN-ONLY` **Status: ACCEPTED** (DO-05: no Remind button, no reminder system) "Remind" button — no reminder mechanism exists. **DO-05**.

**TD-04 D/M — split so far**
- F-063 `VISUAL` **Status: FIXED** (2026-10-11) Heading is an uppercase eyebrow outside the card, repeated per currency. Design
  puts a "Split so far" h3 in the card header. Row anatomy (26px avatar, name, bar, mono value,
  pct) matches. The design suffixes the current user "(you)".
  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses `CardHeader` (B15): "Split so far" h3 + the existing
  "Edit split" action (F-064), one card with a block per currency.
  → **FIXED** (2026-10-11): `tallies/transactions/Index.vue` renders one card with a single `CardHeader` + "Edit split"; each currency is a block under a mono currency label (only when > 1 currency). "(you)" suffix not added (content). Re-screenshotted `desktop/detail-0.png`, `mobile/detail-1.png`.
- F-064 `MISSING-UI` **Status: FIXED** (2026-09-15) "Edit split" link in the card header, opening the Share sheet
  (`TallyShareDialog` already exists — frontend only). → **FIXED** (2026-09-15):
  `pages/tallies/transactions/Index.vue`'s per-currency "Split so far" heading (previously a
  hand-rolled `div`, not the shared `SectionHeader`) now uses `SectionHeader` with an `#action`
  button labelled `t('transactions.edit_split')` ("Edit split") that sets `showShareDialog = true`,
  gated by `isOwner` like the existing header Share button. Verified the link opens the
  `TallyShareDialog` split editor. Re-screenshotted `desktop/detail-0.png`.

**TD-05 D — transactions**
- F-065 `VISUAL` **Status: FIXED** (2026-10-11) App: an eyebrow + "15 ITEMS" outside a single-column list card, with rows of
  initials square + title + "date + category pill" + amount. Design: a card with a "Transactions"
  h3 + link header and a 4-column table (Item / Paid by / Date / Amount) with uppercase headers;
  rows show a category glyph and category sub.
  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses `CardHeader` (B15, "Transactions") and `DataTable`
  (B17: Item / Paid by / Date / Amount). The Paid by column waits on B-07 (F-066); the glyph comes
  from the F-037 map (decided).
  → **FIXED** (2026-10-11): one `CardHeader` card ("Transactions", no count, no link: Export CSV cut), `DataTable` Item / Date / Amount (Paid by omitted until B-07), rows link to edit, `DataTableItem` glyph from `categoryGlyph(category_id)`, income in `--pos`; infinite-scroll skeleton kept inside the card. Re-screenshotted `desktop/detail-0.png`.
- F-066 `BACKEND` **Status: FIXED** (2026-10-13, 8d72e72) "Paid by" column (22px avatar + first name). `party` is eager-loaded but not
  mapped into the payload. **B-07** (superseded) → **DONE** (2026-10-13): `party { name, initials, color }` sent. → **FIXED** (2026-10-13): `DataTable` gains a Paid by column (`Avatar xs` + first name, `AvatarFallback` colour from `party.color`); hidden stacked ≤ 640px by `DataTable`, as in the design. Re-screenshotted `desktop/detail-f054-f066.png`, `mobile/detail-f054-f066.png`.
- F-067 `DESIGN-ONLY` **Status: REMOVED** (DO-06 CUT) "Export CSV" link. No export exists, and CSV export was flagged as an untrue
  marketing claim (#M7). **DO-06**. → **REMOVED** (DO-06 CUT): nothing to build.
- F-068 `BACKEND` **Status: FIXED** (2026-10-13, f9081f9) **Functional bug** found while verifying: scrolling to the end loads `?page=2`,
  which **replaces** the first 15 rows instead of appending (15 rows before and after; the first
  date moves from 2026-09-15 to 2026-08-09). `transactions` is `Inertia::merge()` on a whole
  paginator, so the `data` key is shallow-replaced. **B-08**.
  → **FIXED** (2026-10-13): `Inertia::scroll()` + `<InfiniteScroll data="transactions">`; page 2 appends (30 rows, no duplicates, first row date unchanged). Re-screenshotted `desktop/detail-0.png`, `mobile/detail-0.png`.

**TD-05 M**
- F-069 `VISUAL` **Status: FIXED** (2026-10-11) Same list-vs-card-table structure as F-065 (on mobile the design also drops Paid
  by / Date columns and keeps icon + name/category + amount). Same pagination bug (F-068 / B-08).
  (superseded) → **UNBLOCKED (2026-10-11), open.** `DataTable` (B17) collapses to stacked rows at ≤ 640px.
  → **FIXED** (2026-10-11): same change as F-065; at 375px rows are glyph + name/category + amount, no overflow. Re-screenshotted `mobile/detail-1.png`. Pagination bug stays F-068/B-08.

**TD-06 D — light**
- PASS: palette (surfaces, accent-soft settle fill, pos/neg) matches.

### Batch 4 — Stats

App shots: `desktop/stats-0.png`, `desktop/stats-week.png`, `desktop/stats-bar-hover.png`,
`mobile/stats-0..1.png`.

**Owner decisions (2026-10-14) — Stats UI (F-070, F-071, F-075, F-076, F-077)**
- **Filter (F-070/F-071):** an outline "Filter" button in the header row opens a menu of "All
  tallies" plus every option from `tallies[]`. Filtered, the button reads "Filter: <tally>" and the
  sub reads "<period> · <tally>" ("· all tallies" unfiltered). `tally` persists when switching
  Week/Month/Year. Below 640px the button is hidden, as in the design, but an active filter shows
  as a dismissible chip under the header.
- **KPI stack (F-075)**, with the quiet "Other currencies" line where it applies:
  - Daily average with the delta pill and the same caption format as Home (F-022), from
    `previous_period`; pill and caption hidden when `delta_percentage` is null.
  - Shared portion: "35% of spending is in shared tallies"; "—" when `shared_percentage` is null;
    "Private tally" as the sub when filtered to a private tally.
  - Owed to you, in `--pos`: `owed_to_me.amount_minor` ÷ 10^decimals, sub "1 open balance" /
    "N open balances" / "No open balances".
- **Layout (F-076):** row 1 chart (8 of 12 columns) beside the KPI stack (4); row 2 By category (5)
  beside All activity (7); both rows stack at ≤1100px. By category keeps its in-card `CardHeader`.
- **All activity (F-077):** `CardHeader` "All activity" over a `DataTable` (Transaction / Tally /
  Amount). The tally is an accent pill when `is_shared`; each amount is in its own currency. Below
  640px, stacked rows with the date and the tally pill. No Export CSV (DO-06). No activity →
  `EmptyState`.

**ST-01 D — top**
- F-070 `VISUAL` **Status: FIXED** (2026-10-14, 0c639b0) (superseded) **FIXED (2026-10-11, header only; Filter button → F-071/B-09)** — same `Stats.vue` change as F-072; h1 `26px` at 1440 with the range label as 13px sub below and the segmented top-right. Original: Month label is an eyebrow above the h1 (design: 13px sub below, "April 2026 · all
  tallies"). The segmented Week/Month/Year sits on its own row under the title, left-aligned;
  design puts it top-right in the header row with the Filter button. Segmented styling
  (`--surface` track, `--surface-3` active pill) matches.
  (superseded) → **OPEN** (2026-10-14): **B-09 DONE**; the sub's "· all tallies" / tally-name half has its data (`filter.tally_uuid`,
  `tallies[]`); UI not built.
  → **FIXED** (2026-10-14): sub reads "<period> · all tallies" / "<period> · <tally>" (en/it), Filter beside the segmented in the header row. Verified 1440 unfiltered and filtered (Weekend Trip, Solo Savings). Compared with `desktop/stats-0.png`.
- F-071 `BACKEND` **Status: FIXED** (2026-10-14, 0c639b0) Outline "Filter" button (by tally). `StatsController` only accepts `range`.
  **B-09**. (superseded) → **OPEN** (2026-10-14): **B-09 DONE**; `GET /stats?tally={uuid}` filters every figure (403 foreign, 422 invalid) and
  the payload carries `filter.tally_uuid` + `tallies[]` (options by name). No button yet; the range
  switch must keep `tally` when it's built.
  → **FIXED** (2026-10-14): `Stats.vue` outline Filter button (`DropdownMenuRadioGroup`: All tallies + `tallies[]`), label "Filter: <tally>"; every visit sends `range` and `tally`, so the filter survives Week → Month → Year (verified by URL and sub at each step). Hidden ≤640px; there an active filter is a dismissible chip (verified at 375: chip shown, dismiss → `?range=month`, chip gone). `tallies[]` gained `is_shared` for the "Private tally" sub. demo-empty has no tallies → no button.

**ST-01 M — top**
- F-072 `VISUAL` **Status: FIXED** (2026-10-11) Segmented is a full-width row below the title; design keeps a compact segmented
  inline top-right beside the h1 (Filter hidden below 640px). → **FIXED** (2026-10-11): `Stats.vue` header is one flex row, `h1` 24/26px + 13px sub, `SegmentedControl` (`variant="mobile"`, `w-auto`) right-aligned in it; hidden when empty. Verified 375px: h1 `24px`, segmented top-right. Re-screenshotted `mobile/stats-0.png`.

**ST-02 D — chart**
- KPI line ("Out this month" ↔ mono value): PASS.
- F-073 `VISUAL` **Status: FIXED** (2026-10-11) Plot is ~120px tall inside a card stretched to the category card's height, leaving
  ~250px of empty card below the axis. Bars are 23px wide with wide gaps. Design: 220px plot, flex
  bars with 8px gaps and `6px 6px 2px 2px` radius, and a mono 10.5px `.04em` axis with 6 evenly
  spaced labels (app: sans "W 27" labels, 3 ticks).
  (superseded) → **PARTLY FIXED** (2026-10-11): plot height `220px` ≥ `sm`, `160px` ≤ 640px (F-074; `useMediaQuery`), and the grid uses `items-start` so the chart card no longer stretches to the category card (measured svg `220`/`160`). **Blocked:** mono 10.5px axis (documented `--text-axis` is 10px and not wired in `app.css`), `6px 6px 2px 2px` bar radius (no token), 6 evenly spaced ticks need the same decision. Re-screenshotted `desktop/stats-0.png`, `mobile/stats-0.png`.
  (superseded) → **UNBLOCKED (2026-10-11), open** (the axis + radius part). Uses `--radius-bar`
  (`6px 6px 2px 2px`) and `--text-axis` (10.5px, `.04em`, with `font-mono`), both wired in
  `app.css`. Labels (owner decision D2): **≤ 7 buckets → every bar labelled; > 7 → 6 labels at
  evenly spaced indexes including the first and the last** (`round(i × (n − 1) / 5)`, `i = 0…5`;
  `COMPONENT_INVENTORY.md` B14).
  → **FIXED** (2026-10-11): `StatsSpendingChart.vue` sets the axis to `--text-axis` / `--font-mono` / `--text-axis--letter-spacing` (scoped `:deep(.tick text)`), bar radius read from `--radius-bar` (Unovis takes one corner radius, so the 6px end radius only; the 2px base corner is not expressible), and `tick-values` per D2 (≤ 7 → all, else 6 at `round(i×(n−1)/5)`). Verified: 6 labels, Geist Mono 10.5px, `.42px` spacing at 1440 and 375. Re-screenshotted `desktop/stats-0.png`, `mobile/stats-0.png`.

**ST-02 M — chart**
- F-074 `VISUAL` **Status: FIXED** (2026-10-11) Same thin bars. The card has ~50px of dead space under the axis and a ~70px gap
  before the next section. Design: 160px plot with 5px bar gaps, followed directly by the KPI cards. → **FIXED** (2026-10-11): card padding `20px`, plot bottom margin `18px`, grid gap `12/16px` (was 36px), bar gap `0.2` phone / `0.12` desktop (≈5/8px). KPI cards that follow it are B-09. Re-screenshotted `mobile/stats-0.png`.

**ST-03 D/M — KPI cards**
- F-075 `BACKEND` **Status: FIXED** (2026-10-14, 0c639b0; no "Other currencies" on KPI cards ACCEPTED) The three KPI cards ("Daily average" + "−4.2% vs March", "Shared portion" + "35%
  of spending is in shared tallies", "Owed to you" in `--pos` + "1 open balance") are absent. The
  payload has only `total_out`, `series`, `categories`. **B-09** (reuses B-03 delta and B-04
  balances). (superseded) → **OPEN** (2026-10-14): **B-09 DONE**; `daily_average`, `previous_daily_average`, `previous_period`,
  `delta_percentage` (daily averages), `shared_total_out`, `shared_percentage` (null when nothing spent),
  `owed_to_me {amount_minor, open_balances}` in the payload; cards not built.
  → **FIXED** (2026-10-14): `StatsKpiStack.vue`, three cards per the decisions above (pill via `TrendBadge` and the shared `deltaCaption`, "—"/"Private tally", `--pos` owed amount with plural subs). Verified unfiltered (EUR), Weekend Trip (USD, "$ 105.85", no pill, "—"), Solo Savings ("Private tally"). The quiet "Other currencies" line stays on the chart and By category. **ACCEPTED** (2026-10-14, owner): no "Other currencies" line on the KPI cards, because each KPI is single-currency by design (daily average, a percentage and owed carry no other-currency data in the payload).
  → **Reversed in part** (2026-10-14, owner, for Owed to you only): the KPI keeps its KPI-currency amount and count and adds a quiet line "Also $ 105.85" (`stats.kpi.owed_also`) listing what is owed in other currencies, never added or converted (O-04). `owed_to_me.other_currencies[]` `{amount_minor, currency_symbol, currency_code, decimals}`, minor units, from `GetBalanceSummaryAction`, scoped to the filtered tally. Daily average and Shared portion stay without it. Verified unfiltered (€ 40.00, "1 open balance", "Also $ 105.85") at 1440 and 375: `desktop/stats-owed-1440.png`, `mobile/stats-owed-375.png` vs `design/desktop/stats-0.png`.

**ST-04 D/M — by category**
- F-076 `VISUAL` **Status: FIXED** (2026-10-14, 06c3517) Heading is an eyebrow outside the card (design: "By category" h3 in the card). Every
  category is listed; design shows the top 5 plus an "Other" row (`--text-3` dot, `--surface-3`
  bar). Placement: app puts it beside the chart; design has it on the second row (5 columns) beside
  All activity, with the KPI stack beside the chart. Row anatomy matches. (superseded) → **FIXED** (2026-10-11): `StatsCategoryBreakdown.vue` shows the top 5 plus an "Other" row (`--text-3` dot, `--surface-3` bar via new `MiniBar` colour, new `stats.categories.other`). Heading style stays an eyebrow (no card-header component) and the c5/c7 placement needs the KPI/All activity cards (B-09). Re-screenshotted `desktop/stats-0.png`, `mobile/stats-0.png`.
  (superseded) → **Heading UNBLOCKED (2026-10-11), open.** Uses `CardHeader` (B15) for "By category" inside the
  card. The c5/c7 placement still waits on B-09. (superseded) → **OPEN** (2026-10-14): **B-09 DONE**; the KPI stack and All activity data exist, so
  the c5/c7 placement can be built; not built.
  (superseded) → **Heading FIXED** (2026-10-11): `StatsCategoryBreakdown.vue` is one `rounded-card bg-surface` card with a `CardHeader` "By category". Re-screenshotted `desktop/stats-0.png`, `mobile/stats-0.png`.
  (superseded) → **Placement FIXED** (2026-10-14): 12-col grid ≥1101px (chart 8 / KPI stack 4, By category 5 / All activity 7), single column below. Compared with `desktop/stats-0.png`, `stats-1.png`, `mobile/stats-0.png`.
  → **FIXED** (2026-10-14): with no categories (a filtered tally with no spending in the range) the By category slot keeps its place and shows `EmptyState` (`stats.categories.empty_*`, en/it) instead of being removed, so All activity stays at 7 of 12 columns and never spans the full width. Verified on Solo Savings, Week (no expenses): 1440×900 By category and All activity side by side, 375×812 stacked. Compared with `design/desktop/stats-1.png` and `design/mobile/stats-1.png` (same c5/c7 grid); screenshots `desktop/stats-f076-empty-1440.png`, `mobile/stats-f076-empty-375.png`.

**ST-05 D/M — all activity**
- F-077 `BACKEND` **Status: FIXED** (2026-10-14, 0c639b0; no time on activity rows ACCEPTED) "All activity" card is missing (table Transaction / Tally / Amount with accent
  tally pills; on mobile rows with date + tally pill). Stats sends no transaction list for the
  range. **B-09**. (superseded) → **OPEN** (2026-10-14): **B-09 DONE**; `activity[]` = the latest 20 transactions in range, each in its own
  currency, with `tally_uuid`/`tally_name`/`is_shared` and `category_id`; card not built.
  → **FIXED** (2026-10-14): `StatsActivity.vue` (`CardHeader` + `DataTable` + `DataTableItem`; accent pill for shared tallies, neutral pill inline ≤640px, own-currency signed amounts, `EmptyState` when empty). Compared with `desktop/stats-1.png` and `mobile/stats-1.png`. **ACCEPTED** (2026-10-14, owner): no time on activity rows, because the data has dates only (the design's "Today, 11:24" is not built).
- F-078 `DESIGN-ONLY` **Status: REMOVED** (DO-06 CUT) "Export CSV" link on All activity. **DO-06**. → **REMOVED** (DO-06 CUT):
  nothing to build.

**ST-06 D — interaction**
- Segmented switch: PASS (`?range=week` swaps KPI label/value, the Mon–Sun series and the
  categories; see `desktop/stats-week.png`).
- F-079 `VISUAL` **Status: FIXED** (2026-09-15) Bar hover leaves the fill unchanged (`--surface-3` before and after); design hover
  is `--accent-dim`. → **FIXED** (2026-09-15): `StatsSpendingChart.vue`'s `colorAccessor` now checks
  a `hoveredIndex` ref (`d.is_current ? 'var(--accent)' : hoveredIndex === d.index ? 'var(--accent-dim)'
  : 'var(--surface-3)'`), matching `.chart .b:hover{background:var(--accent-dim)}.chart
  .b.on:hover{background:var(--accent)}` (the active/current bar keeps `--accent` on hover). Wired via
  `VisGroupedBar`'s `:events` prop on `VisGroupedBarSelectors.bar` (`mouseenter`/`mouseleave`). The
  Vue wrapper's own prop-diffing didn't reliably re-render on the `hoveredIndex` change (traced with
  the chart's exposed `component` instance — `_setUpComponentEvents`/listeners were confirmed bound
  correctly via the DOM node's `__on`, but the reactive `:color` prop path never triggered a redraw),
  so the fix calls the exposed unovis instance's `.render(0)` directly from the event handlers
  (`chart.value.component.render(0)`, `ref="chart"` on `VisGroupedBar`) instead of depending on that
  path. Verified via computed style: hovering a non-active bar changes its inline `fill` from
  `var(--surface-3)` (`oklch(0.31 0.024 265)`) to `var(--accent-dim)` (`oklch(0.4 0.11 262)`, dark
  theme) and back on mouseleave; hovering the active bar stays `var(--accent)`
  (`oklch(0.7 0.17 262)`) throughout. Re-screenshotted `desktop/stats-bar-hover.png`.

### Batch 5 — You

The You tab (`BottomNav` → `profile.edit`, `/settings/profile`) renders `settings/Profile.vue`
inside `layouts/settings/Layout.vue`. App shots: `desktop/profile-0.png`, `mobile/profile-0..1.png`,
`mobile/appearance-0.png`. Left out per triage: "Balance reminders" toggle (DO-05) and "Export all
data · CSV" (DO-06).

**YO-01 D — top**
- F-080 `VISUAL` **Status: FIXED** (2026-10-13) The page opens with the 56px header bar (sidebar trigger + "PROFILE SETTINGS"
  breadcrumb, kept by the F-004 `breadcrumbs` gate), then a mono eyebrow "ACCOUNT · SETTINGS", a
  36px "Your settings." h1 and a `--line` divider rule. Design: no header bar, a 26px/600/`-.03em`
  "You" h1, a 13px `--text-3` sub "Account & preferences" (`margin-top:3px`), no rule, `26px` below
  the header. Same pattern as `HomeHeader` (F-014). Frontend only, not blocked.
  → **FIXED** (2026-10-13): `settings/Layout.vue` header is now h1 "You" (`24px`/`sm:26px`, 600, `-.03em`) + 13px `--text-3` sub "Account & preferences" (`mt-[3px]`), `18px`/`26px` below, no eyebrow or rule; container matches Stats (`max-w-330 px-5 py-6 md:px-8 md:py-8`). The three settings pages drop `breadcrumbs`, so no header bar. New keys `settings.title`/`subtitle` (old `crumb`/`title_lead`/`title_em` removed). Verified computed `26px` at 1440, `24px` at 375, no horizontal overflow. Re-screenshotted `desktop/profile-0.png`, `mobile/profile-0.png`.

**YO-01 M — top**
- F-081 `VISUAL` **Status: FIXED** (2026-10-13) Same as F-080 at 375px: eyebrow + 30px h1 + rule. Design: 24px h1, 13px sub,
  `18px` below the header. The tab bar's active "You" (icon `--accent`, label `--text`) is a PASS.
  → **FIXED** (2026-10-13): same `settings/Layout.vue` change as F-080 (24px h1, 13px sub, `18px` below). Re-screenshotted `mobile/profile-0.png`.

**YO-01 D/M — page structure**
- F-082 `VISUAL` **Status: FIXED** (2026-10-13) · (superseded) **BLOCKED (mapping)** The app splits You into three sub-pages (Profile /
  Password / Appearance) behind a "SECTIONS" nav: a 200px column at `lg`, stacked above the content
  on phones, where it pushes the first field ~350px down. Design: one page on the 12-col grid
  (`gap:16px`, `12px` on phones): an identity card at c5 (`align-self:start`) beside a c7 stack
  (`gap:16px`) of the Preferences and Data cards, stacking to full width at `≤1100px`. Password
  change, "Delete account" (red warning card + destructive button) and the Light/Dark/System
  appearance tabs have no design counterpart. Owner call needed: where do Password and Delete
  account live (e.g. setting rows in a third "Security" card linking to the existing forms, or kept as
  sub-pages reached from rows). The row layout waits on F-085.
  → **FIXED** (2026-10-13): new `YouController` + route `you` (`/settings`, was a redirect to profile) render `settings/You.vue`; bottom-nav You tab links to it (still active under `/settings*`). Identity card c5 beside a c7 stack, stacking < 1101px; Preferences (Theme), Account (Password), Data (Sign out, Delete account via the new `DeleteAccountDialog`, shared with `DeleteUser`). Default currency row waits on B-10; sidebar link on F-007. Re-screenshotted `desktop/you-hub-0.png`, `mobile/you-hub-0.png`.
  _(original unblock note)_ Owner decision D3: **You is a hub page**; the existing
  settings pages stay as destinations. Layout: identity card c5 beside a c7 stack (Preferences,
  Data), stacking ≤ 1100px; `CardHeader` + `SettingRow` rows. Rows:
  - Preferences: Theme (`PillSelect`, F-086), Default currency (`PillSelect`, F-087 / B-10).
  - "Password" → the existing password page (link row).
  - Data: Sign out (`neg`, F-088), then **"Delete account" (`neg`) at the bottom, below Sign out**,
    opening the existing `DeleteUser` confirmation.

  The red "Delete account" warning card and the "SECTIONS" nav leave the You page. The hub needs
  its own page + route; Profile / Password / Appearance keep theirs. Full map:
  `COMPONENT_INVENTORY.md` §G.
  **Entry points (owner, 2026-10-11):** the bottom-nav **You** tab (today `profile.edit`, i.e.
  `/settings/profile`, active on `/settings*`) and the sidebar user block (F-007) both link to the
  **new You hub route**. The tab stays active on the hub and on the settings pages it leads to.

**YO-02 D/M — identity card**
- F-083 `MISSING-UI` **Status: FIXED** (2026-10-13) · (superseded) **BLOCKED-TOKEN** No identity card. The app shows an always-open Name/Email
  form with an inline Save. Design: `--surface` card, `--radius-card` (20px), padding `20px` (`16px`
  ≤640), flex row `gap:16px`, centered. Avatar is a **56×56** circle, c1 tint (bg
  `color-mix(in oklch, var(--c1) 26%, var(--surface))`, text `--c1`, 1px border
  `color-mix(in oklch, var(--c1) 30%, transparent)`), initial **18px**/600/`.02em`. Name
  17px/600/`-.02em` `--text`, email 13px `--text-3` `margin-top:2px`. Then an outline small "Edit"
  (`btn-o btn-sm`): transparent, 1px `--line`, `--text-2`, 13px/600/`-.01em`, `8px 14px`, pill; hover
  `--surface` bg and `--text`. Blocker: `ui/avatar` (A3) stops at 38px and `DESIGN_TOKENS.md §2`
  lists avatar text only for 38/26/22px, so a 56px avatar with 18px text needs a documented size
  (e.g. `Avatar size="xl"`: 56px / 18px). Also: the app's `outline` Button is `bg-background
  shadow-xs` with a `surface-2` hover, while `btn-o` is transparent with a `surface` hover.
  → **FIXED** (2026-10-13): identity card in `settings/You.vue` (`Avatar xl`, c1 tint). See F-082.
  _(original unblock note)_ Uses `Avatar size="xl"` (56px, initial 18px/600/`.02em`;
  `DESIGN_TOKENS.md §2.2`, `COMPONENT_INVENTORY.md` A3). Placement per F-082 (D3).
- F-084 `VISUAL` **Status: FIXED** (2026-10-13) · (superseded) **BLOCKED (mapping)** The design draws no edit UI behind "Edit". The existing
  Name/Email form (+ email verification notice) is the only edit path. Owner call: Edit opens that
  form in a `ResponsiveModal` (D1), or links to a sub-page. Waits on F-082.
  → **FIXED** (2026-10-13): Edit is a link to `profile.edit`. See F-082.
  _(original unblock note)_ Owner decision D3: "Edit" **links to the existing profile
  settings page** (Name/Email form + verification notice). No modal.

**YO-03 D/M — preferences**
- F-085 `MISSING-UI` **Status: FIXED** (2026-10-13) · (superseded) **BLOCKED-COMPONENT** No Preferences card. It needs two undocumented
  pieces:
  - (a) the card header `.card-h`: flex, space-between, `margin-bottom:14px`; h3 15px/600/`-.02em`;
    optional link 12.5px/500. Same blocker as F-036/F-039.
  - (b) a setting row `.sw`: flex, center, `gap:12px`, padding `13px 0`, `border-top:1px --line`
    (first row: no border, `padding-top:4px`); title 13.5px/500/`-.01em` `--text`; sub 11.5px
    `--text-3` `margin-top:2px`; control on the right, `flex-shrink:0`. It is not in
    `COMPONENT_INVENTORY.md`; the closest is `.mem-row` (E3: 11px padding, 14px name). Needs an
    inventory entry, e.g. `SettingRow` (`title`, `description`, `#control` slot). The Data card
    (F-088) and the Share toggles (DO-09/DO-10) reuse it.

  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses `CardHeader` (B15) and `SettingRow` (B16). Row set per
  F-082. The Share toggles are gone (DO-09/DO-10 cut), so the You page is the only consumer.
  → **FIXED** (2026-10-13): Preferences / Account / Data cards in `settings/You.vue` use `CardHeader` + `SettingRow` (built with F-082). Re-screenshotted `desktop/you-hub-f085.png`, `mobile/you-hub-f085.png`. Balance reminders stays out (DO-05).
- F-086 `VISUAL` **Status: FIXED** (2026-10-13) · (superseded) **BLOCKED-COMPONENT** + **BLOCKED (mapping)** "Dark theme" is a switch row in
  Preferences. The app has a working Light / Dark / System segmented on the separate Appearance
  sub-page (`mobile/appearance-0.png`). Blockers:
  1. **Component.** There is no `ui/switch`. Inventory E5 says to build one "only if a boolean
     toggle appears later"; it now has, but E5 gives no spec. `.tg` values: track 40×23,
     `--radius-pill`, no border, off `--surface-3`, on `--accent`, `transition:.2s`. Knob 17×17
     circle, `top:3px; left:3px`, off `--text-2`; on `--accent-ink` at `left:20px`.
  2. **Mapping.** The settled default theme is `system` (`CLAUDE.md`), which a binary toggle can't
     express. Owner call: keep the 3-way `SegmentedControl` as the row's control (no new
     component), or use a toggle that flips the resolved theme like the sidebar Theme row (F-005).

  (superseded) → **UNBLOCKED (2026-10-11), open.** Owner decision D4: keep **Light / Dark / System**. The row is
  titled "Theme" and its control is a `PillSelect` (E6), not the design's two-state toggle. No
  `ui/switch` is built (E5).
  → **FIXED** (2026-10-13): "Theme" row with `SelectTrigger variant="pill"` (Light / Dark / System) wired to `useAppearance`. Re-screenshotted `desktop/you-hub-f085.png`, `mobile/you-hub-f085.png`.
- F-087 `BACKEND` **Status: FIXED** (2026-10-13, 4c901a9) · (superseded) **BLOCKED-COMPONENT** (select style) "Default currency" row with a pill select
  (EUR € / USD $ / GBP £), sub "Used for new tallies". There's no currency preference: `users` has
  no currency column, tallies carry no currency (B-11), and `AddTransactionForm` starts with
  `currency_id: ''` ("Select a currency"). **B-10**. The `.role` pill select is not a documented
  `SelectTrigger` variant (`default|filled` only). Its values: `--surface-2` bg, no border,
  `--radius-pill`, `--text-2` 12px/500, padding `7px 26px 7px 11px`, 11px chevron at `right 9px`,
  hover/focus `--text`. It is reused by the Share role select (DO-08). Needs a `pill` (`sm`)
  `SelectTrigger` variant in A-/E1.
  (superseded) → **Component UNBLOCKED (2026-10-11); waits on B-10 (now BUILD).** Uses `PillSelect` (E6) in a
  `SettingRow`. B-10 adds the column and the prop; the select offers a "None" option because the
  column is nullable. The Share role select no longer exists (DO-08 cut).
  → **FIXED (2026-10-13)**, see Fixes applied. _(was: OPEN, unblocked: B-10 is done.)_ `PATCH settings/preferences` (`preferences.update`, field `default_currency_uuid`, uuid only) and the shared prop `auth.user.default_currency` (`{uuid, code, symbol}|null`) exist. The Preferences select UI itself is still to build (visual loop).

**YO-04 D/M — data**
- F-088 `MISSING-UI` **Status: FIXED** (2026-10-13) "Sign out" row (sub "On this device") with an outline small button in `--neg`
  text. The You page has no sign-out. On desktop the only path is the sidebar user-block dropdown
  (`UserMenuContent`, F-007). **On phones there is no sign-out anywhere**: the tab bar's You leads
  here and `NavUser` exists only in the `≥ md` sidebar (verified at 375px: no logout control in the
  DOM). Frontend only: `POST /logout` and the `Link method="post"` pattern in `UserMenuContent`
  already exist. The button can ship now; the row layout waits on F-085. It unblocks F-007 (user
  block → straight to You).
  _(2026-10-11: the row layout now uses `SettingRow` `tone="neg"`; "Delete account" is added below
  it, see F-082.)_
  → **FIXED** (2026-10-13): row layout verified: `SettingRow tone="neg"` + outline small `text-neg` button, Delete account below. Re-screenshotted `desktop/you-hub-f085.png`, `mobile/you-hub-f085.png`.

### Batch 6 — Modals / sheets

All three overlays go through `ResponsiveModal.vue`: `ui/dialog` at `≥ md` (768px), `BottomSheet`
below. Share uses its own `TallyShareDialog`. App shots: `desktop/modal-addTx-0.png` (picker),
`desktop/modal-addTx-form-0..1.png`, `desktop/modal-newTally-0.png`,
`desktop/modal-shareTally-0..1.png`, `desktop/modal-focus-trap.png`;
`mobile/sheet-addTx-picker.png`, `mobile/sheet-addTx-0..1.png`, `mobile/sheet-newTally-0.png`,
`mobile/modal-shareTally-0..1.png`.

**MO-01 D — add transaction**
- F-089 `DEAD-UI` **Status: FIXED** (2026-10-11) **Functional bug.** At 1440×900 the Add-tx dialog (after picking a tally) is
  1058px tall and centered, so its top sits at −79px. `DialogContent` has `overflow-y:hidden` and no
  `max-height`, and the mouse wheel doesn't scroll it. The title, the close button and "← Change
  tally" are above the viewport, and the **"Add transaction" submit sits at y 910–958, below the
  900px viewport and unreachable by pointer**. Design: `.modal` `max-height: calc(100vh - 48px)`,
  flex column; `.sh-hd` fixed (`22px 24px 14px`); `.sh-body` `overflow-y:auto`, padding
  `0 24px 24px`, scrollbar `thin` / `--surface-3`. Frontend only: `ResponsiveModal.vue` /
  `DialogContent` classes. It hits every `≥ md` dialog with tall content, including New tally once
  F-096 adds members. (The mobile sheet scrolls correctly.)
  → **FIXED** (2026-10-11): `DialogContent` is now a flex column capped at `max-height: calc(100dvh - 3rem)`;
  `ResponsiveModal` splits it into a fixed header and a `min-h-0 flex-1 overflow-y-auto` body (thin
  `--surface-3` scrollbar); the Add-tx / New-tally action row is `sticky` at the bottom of that body;
  `TallyShareDialog` keeps its header/footer and scrolls its body. Checked at 1440×900 (Add-tx 852px,
  submit at y 807–855) and 1280×720 (672px, submit 627–675) for Add-tx, New tally, Share; and at 375×812
  for the Add-tx / New-tally sheets and the Share dialog. Submitted a real transaction from the dialog
  (302, dialog closed, row listed). Scrollbar thumb/track styling is the only part of the design's
  `.sh-body` not matched pixel-for-pixel (WebKit/Blink `scrollbar-color`).
- F-090 `VISUAL` **Status: FIXED** (2026-10-13, 9d6bd4c, 1ab0c61) · **FIXED** (scrim, 2026-10-13: `--color-scrim` + `--blur-scrim` wired in `app.css`, used by `DialogOverlay`, `DialogScrollContent`, `SheetOverlay`) · was (superseded) **BLOCKED-TOKEN** (shadow) Dialog shell (also MO-02/MO-03 desktop):
  - Width 440px vs design `max-width:500px`.
  - One `p-5` (20px) vs design header `22px 24px 14px` and body `0 24px 24px`.
  - Close is a 28px transparent ✕. Design: `.icb`, a 40px circle with `--surface` bg, 1px `--line`
    and `--text-2`, hover `--surface-2`/`--text`. That's A2, an existing `Button`
    `variant="outline"` icon size.
  - Scrim is `oklch(0 0 0/.4)` + `blur(8px)`. Documented `--scrim`: `rgba(0,0,0,.55)` +
    `--blur-scrim` `blur(2px)` (the desktop design file uses `blur(4px)`) → **FIXED** (2026-10-13): `--blur-scrim` is now `4px`, computed `backdrop-filter: blur(4px)` on dialog + sheet overlays; compared `app/desktop/scrim-4px.png` with `design/desktop/modal-newTally-0.png`.
  - Shadow is `0 30px 80px oklch(0 0 0/.18)` + a 1px border. Design: `0 40px 100px -20px
    rgba(0,0,0,.6), 0 0 0 1px var(--line)`. That isn't a token: `DESIGN_TOKENS.md §4.2` says app
    panels have no drop shadow, and §1.3 says to ignore this value on import. Needs
    `--shadow-modal`.
  - The radius (28px = `--radius-lg`) matches.

  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses `--shadow-modal` (`shadow-modal`, wired in `app.css`;
  `DESIGN_TOKENS.md §4.2`). The width, padding and close button need no new token. The scrim uses
  the documented `--scrim` + `--blur-scrim`.
  (superseded) → **FIXED, scrim excepted** (2026-10-11): `DialogContent` is `max-w-[500px]` with `shadow-modal`; the close is a 40px circle (`bg-surface`, `border-line`, `text-text-2`, hover `surface-2`/`text`); `ResponsiveModal` header `22px 24px 14px`, body `0 24px 24px`. Verified at 1440×900: dialog 500px wide, computed shadow `0 40px 100px -20px rgba(0,0,0,.6), 0 0 0 1px var(--line)`, close 40×40. Re-screenshotted `desktop/modal-addTx-form-0.png`; mobile sheet untouched. **Open:** the scrim still uses `oklch(0 0 0/.4)` + `blur-sm`: `--scrim`/`--blur-scrim` are documented but not wired in `app.css`.
- F-091 `MISSING-UI` **Status: FIXED** (2026-10-11) Desktop "Cancel" beside "Add transaction". Design `.acts`: flex, `gap:10px`,
  `margin-top:6px`, each button `flex:1`, `14px 22px`. Cancel is `btn-g` (`--surface-2` bg,
  `--text`, hover `--surface-3`). The app has only a full-width primary. Hiding Cancel on phones
  matches the design. Frontend only.
  → **FIXED** (2026-10-11): `AddTransactionForm.vue` renders a `bg-surface-2` Cancel (`hidden md:inline-flex`, emits `cancel`; `AppOverlays` closes the dialog) and both buttons are `flex-1` with `gap-3`. Verified 188px each at 1440px, Cancel closes the dialog. Re-screenshotted `desktop/modal-addTx-form-1.png`.
- F-092 `VISUAL` **Status: FIXED** (2026-10-14, e6cee5b; Description field ACCEPTED: an existing feature the design omits) Flow and fields:
  - **Tally choice.** The app opens a "Pick a tally" step first, and the form then shows "← Change
    tally · Household". Design: Tally is an inline filled select in a 2-col row with Date (`.fld-2`,
    `gap:10px`, 1 column `≤640`). An inline select needs the per-tally form data refetched on change
    (`formDataHttp` exists, so frontend only).
  - **Field pairing.** The app pairs Currency + Date and adds a Description textarea; the design
    has neither field.
  - **Amount.** The app shows "0.00" with no currency mark. Design: `€` 26px/500 `--text-3`, then
    the 48px mono value with `--text-3` decimals.

  The currency mark and dropping the empty "Select a currency" field depend on B-10/B-11.
  _(2026-10-13, OWNER DECISION, record only, UI in a later run: the currency mark in Add transaction's amount entry becomes the currency picker. Tapping it opens a `PillSelect`-style menu of the supported currencies, and the separate Currency select is removed. The prefill order (B-11) and the manual-pick rule are unchanged.)_
  _(2026-10-14, CURRENCY HALF FIXED: `AmountInput` gets a `currency` slot; `AddTransactionForm` puts `Select` + `SelectTrigger variant="pill"` ("EUR €", E6) there with the supported currencies as items, and the separate Currency field is gone (its error shows under the amount). Prefill order and manual-pick rule unchanged. Verified open menu at 1440 and 375.)_
  (superseded) _(2026-10-13, STOPPED: the design draws no currency control in Add transaction, only the `€` mark in the amount entry; a control there is not a documented component. The Currency select stays as is; owner call needed.)_
  (superseded) _(2026-10-13: B-11 DONE — the prefill order is now resolved server-side, so the Add-tx currency follows tally default → most-used → user default; **F-092's currency half stays OPEN** (currency mark / dropping the empty select UI not built). B-10 DONE — `form-data` and the Create page return `default_currency_uuid`; `AddTransactionForm` prefills the currency from it (still editable). Tally-currency priority waits on B-11; F-092's currency half stays open.)_
  _(2026-10-11: B-10 and B-11 are both BUILD. Prefill order: the tally currency, then the user's
  default, then empty. The currency stays editable on every transaction.)_
  (superseded) → **PARTLY FIXED** (2026-10-13): the Tally + Date half is done. The "Pick a tally" step and the
  "← Change tally" link are gone; `AddTransactionForm` takes `tallyOptions` and renders an inline
  filled Tally select in a `gap-x-2.5` grid with Date (`md:grid-cols-2`: 2 columns in the dialog,
  1 in the sheet). The first tally is auto-selected; changing it refetches `form-data` in place
  without remounting, so typed values survive, and `party_id` resets if the new tally lacks that
  member. The full-page fallback (no `tallyOptions`) shows no Tally select. Currency is untouched
  (same select, still "Select a currency", now on its own row under the Tally/Date row) and
  **its half waits on B-10/B-11** (mark prefill, dropping the empty select); Description and the
  amount currency mark are unchanged. Verified at 1440×900 (Tally 495/215px, Date 720/215px,
  same row, 10px gap; dialog 24–876 of 900; submit bottom 851) and 1280×720 (dialog 24–696 of
  720; submit 623–671; F-089 still holds). Sheet at 375×812: Tally, Date and Currency stack at
  x=20, 325px wide, no horizontal scroll. Submitted one transaction ("F092 test", 7.25, Solo
  Savings, switched from Household inside the dialog): the dialog closed and the row appears in
  the tally. Screenshots `desktop/modal-addTx-f092-1440.png`, `-1280.png`, `-filled.png`,
  `mobile/sheet-addTx-f092.png`.
- F-093 `DESIGN-ONLY` **Status: REMOVED** (DO-07 CUT) "Transfer" segment. `TransactionType` has only `income`/`expense`. **DO-07**.
  → **REMOVED** (DO-07 CUT): the segment stays Expense / Income.

**MO-01 M — add transaction sheet**
- PASS: grabber, 28px top radius, 19px title with a round close, scrollable body (`scrollHeight`
  1039 in a 715px sheet), full-width submit at the end, no Cancel, `.fld` labels 11px/600/uppercase,
  `.chips` category chips. Flow/fields → F-092, Transfer → F-093.

**MO-02 D/M — new tally**
- F-094 `VISUAL` **Status: FIXED** (2026-10-13) Colour & icon: the app renders two chip rows (6 colour dots, then 6 glyphs) and
  pre-selects neither (`form.color`/`form.glyph` start as `''`). Design: one row of combined
  dot + glyph chips with the first one selected (`--accent` fill, `--accent-ink` text, dot
  `currentColor`). The combined chip is already shown on `/dev/design-system` ("Colour & icon
  (c1)"), so this is frontend only. The app also adds a Description textarea the design doesn't have.
  → **FIXED** (2026-10-13): `NewTallyForm.vue` shows one `ChipGroup` of 6 combined chips (colour dot + glyph, the colour is the value and sets the paired glyph), first preselected (`c1` + ⌂), Description textarea and its field removed (it stays optional in the request). Re-screenshotted `desktop/modal-newTally-f094.png`, `mobile/sheet-newTally-f094.png`.
- F-095 `BACKEND` **Status: FIXED** (2026-10-13, 4c901a9) "Currency" select (EUR € / USD $ / GBP £) in a 2-col row with Type. Tallies have
  no currency (`tallies` columns: name, description, color, glyph). **B-11**.
  → **FIXED (2026-10-13)**, see Fixes applied. _(was: OPEN, B-11 backend DONE, select UI not built.)_ `tallies/Create` and `tallies/Edit` already receive `currencies` + the prefill; NewTallyForm/EditTally need the select.
  → **B-11 is BUILD AS DEFAULT ONLY (2026-10-11).** The select sets the tally's *default* currency
  for new transactions, prefilled from the user's B-10 default. Its label/hint must not suggest a
  limit (e.g. hint "Default for new transactions"). The 2-col row pairs it with nothing now that
  Type is gone (F-096), so it takes the full width.
- F-096 `BACKEND` **Status: REMOVED** (B-12 CUT) · (superseded) **BLOCKED-COMPONENT** "Type" select (Shared / Private) plus, when Shared, a
  "Members & split" block: Equal/Custom, total, split bar, member rows, hint, "+ Invite someone"
  (`btn-g btn-full`, which reveals an email field and a small "Add"), then "Create tally"
  (`btn-p btn-full`). The app has no type and no members: you share after creating, through the
  Share dialog. Inviting at creation and holding a share for a not-yet-joined invitee needs **B-12**.
  The Type select itself is frontend (Private = no invites). Component: E3 (`SplitEditor`) is in the
  inventory, but its split bar, lock and small segmented aren't specified, and `ui/number-stepper`
  has no lock. Values the editor needs:

  | Part | Values |
  |---|---|
  | Container `.split` | `--surface-2`, `--radius-sm` (14px), padding `12px 14px` |
  | Head | flex, space-between, `margin-bottom:12px`, `gap:10px` |
  | Total | label 12px `--text-3`; value mono/600 `--pos`, `--neg` when ≠ 100 |
  | Split bar | 8px tall, pill radius, `--surface-3` track, `gap:2px`, `margin-bottom:4px`; segments `--cN`, `transition: width .25s` |
  | Row | `gap:10px`, padding `10px 0`, `--line` divider; 26px avatar; name 13.5px/500/`-.01em` (ellipsis); sub 11.5px `--text-3` |
  | Pct stepper `.pct` | `--surface` pill, `2px` padding; 26px round ± in `--text-2`, hover `--surface-3`; input 34px mono 13px/600, right-aligned; unit 12px mono `--text-3` |
  | Lock | 28px transparent circle, `--text-3`, hover `--surface-3`/`--text`; locked `--accent`; `visibility:hidden` in Equal mode |
  | Equal mode | steppers `opacity:.45`, inert |
  | Small segmented `.seg-s` | `--surface` track, `2px` padding; buttons `6px 12px`, 12px |
  | Hint | 11.5px `--text-3`, `margin-top:10px`, `line-height:1.45` |

  → **REMOVED (2026-10-11, B-12 CUT).** New tally creates a solo tally; invites and splits happen
  afterwards in the Share dialog. "Members & split" and "+ Invite someone" leave the New tally
  dialog, and so does the Type (Shared / Private) select, since there is nothing to choose for a
  solo tally. The table above is kept as the value source for the Share dialog's split editor,
  which now carries it: `COMPONENT_INVENTORY.md` E3 extras, used by F-097.
- PASS (M): the bottom-sheet shell matches MO-01 M. The custom-split ref
  (`modal-newTally-custom-split.png`) is covered by F-096.

**MO-03 D — share tally**
- F-097 `VISUAL` **Status: FIXED** (2026-10-13) The Share dialog keeps the old editorial styling:
  - A 24px title with a share icon and an accent "· Household".
  - Mono, wide-tracked uppercase labels ("INVITE COLLABORATOR").
  - An underline email input with a ghost "+ Invite".
  - Mono emails and boxed `%` inputs.
  - A separate footer band with a mono "Total: 100%".

  Design:
  - A 19px "Share tally" header with an `.icb` close.
  - A 13.5px `--text-3` intro, the tally name bold in `--text-2`, `margin-bottom:20px`.
  - `.fld` labels.
  - "Invite by email": a filled mono 12.5px input with a `btn-p btn-sm` "Send" in an `.lk` row
    (`gap:8px`).
  - Members as `.mem-row`: 26px avatar, name 14px/500, email 11.5px `--text-3`, an "Owner"
    `.pill` (10.5px/600/`.03em`, `--surface-2`, `--text-3`).
  - The Split editor (F-096), then `.acts` Cancel + Done.

  Everything except the split editor (F-096) and the switch (F-086) is documented.

  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses the `SplitEditor` extras (E3: `SplitBar`, lock, `sm`
  segmented, `sm` % stepper). The switch is not needed: both Share toggles are cut (DO-09, DO-10).
  No role select (DO-08 cut): member rows keep the trash icon as "Remove". No invite-link field.
  → **FIXED** (2026-10-13): `TallyShareDialog.vue` now uses `ResponsiveModal` (500px, 19px title, `.fld` labels via `FormField`), intro with the tally name bold, filled mono invite input + primary Send, `Badge pill` Owner, and the E3 split editor (`SplitBar`, `SegmentedControl sm`, `NumberStepper sm`, `SplitLockButton`; Equal disables steppers/hides locks; Custom rebalances the other unlocked rows, mirroring the design's `initSplit`). Cancel (≥ md) + Done save. Shares are rounded to whole percentages on load. Verified: 60/40 → Custom, Equal → 50/50, +5 → 55/45. Re-shot `desktop/modal-shareTally-f097.png`.
- F-099 `VISUAL` **Status: FIXED** (2026-10-13) Pending invitations are a separate list with a "PENDING" badge. The design lists
  invitees inline in Members, with the sub "Invited · not joined", and gives them a Split row (sub
  "Invited"). Listing them inline is frontend only (the invitations are already loaded). Their split
  rows need **B-12**.
  → **Scope trimmed (2026-10-11), open.** B-12 is cut, so invitees get **no split row**. What
  remains is frontend only: list pending invitees inline in Members with the sub "Invited · not
  joined" and drop the separate "PENDING" list.
  → **FIXED** (2026-10-13): pending invitees listed inline in Members ("Invited · not joined", X cancels), no split row, separate PENDING list removed. Same file.
- F-100 `DESIGN-ONLY` **Status: REMOVED** (DO-08 CUT) Per-member role select (Can edit / Can view / Remove). There are no roles:
  `tally_user` has only `default_share_percentage`. "Remove" maps to the existing delete-share
  action (trash icon). Pill select style → F-087. **DO-08**. → **REMOVED** (DO-08 CUT): no role
  select; the trash icon stays as Remove.
- F-101 `DESIGN-ONLY` **Status: REMOVED** (DO-09 CUT) "Invite link" read-only field + Copy, and the "Link requires approval"
  toggle. Invitations are per-email tokens; there's no shareable join link and no approval queue.
  **DO-09**. → **REMOVED** (DO-09 CUT): invite by email only.
- F-102 `DESIGN-ONLY` **Status: REMOVED** (DO-10 CUT) "Members can settle up" toggle. There's no per-tally permission flag.
  **DO-10**. → **REMOVED** (DO-10 CUT).

**MO-03 M — share sheet**
- F-098 `VISUAL` **Status: FIXED** (2026-10-13) At 375px Share opens as a centered dialog (343px wide, top 88px), not a bottom
  sheet; inventory D2 says to present it as a bottom sheet `< md`, and `ResponsiveModal` already
  does that. The ✕ overlaps the title ("Household" runs into it).

**MO-04 D — interaction**
  → **FIXED** (2026-10-13): Share now goes through `ResponsiveModal`, so < md it is a bottom sheet with the round close clear of the title; no horizontal overflow at 375px. Re-shot `mobile/sheet-shareTally-f098.png`.
- PASS: 15 × Tab kept focus inside the dialog; a scrim click closes it; Esc closes it (reka). When
  opened with `n`, focus goes back to `body` on close because there's no trigger element (fine).
  Shot: `desktop/modal-focus-trap.png`.

### Batch 7 — States not drawn in the design + base components

Judged against tokens/components, not a reference shot. App shots: `{desktop,mobile}/empty-
{home,tallies,stats,addtx}.png`, `mobile/loading-addtx-{picker,form}.png`,
`desktop/newTally-validation.png`, `mobile/addTx-validation.png`, `{desktop,mobile}/error-404.png`,
`desktop/design-system-0..6.png`, `mobile/design-system-0.png`. Empty account:
`demo-empty@tallypal.test`.

**ES-01..ES-03 D/M — empty Home / Tallies / Stats**
- F-103 `VISUAL` **Status: FIXED** (2026-10-13) · (superseded) **BLOCKED-COMPONENT** Three hand-rolled copies (`HomeEmptyState.vue`,
  `tallies/Index.vue`, `StatsEmptyState.vue`) of a centered `text-3xl` (**30px**) title, a body
  line and a primary "Create new tally" pill, with no surface. The empty title is bigger than the
  page's own 24/26px h1 just above it, which inverts the hierarchy, and its trailing period ("No
  tallies yet.") is the removed editorial voice. Everything else uses tokens. The design draws no
  empty state, and `COMPONENT_INVENTORY.md §F` says empty states "must be designed" (decision #20)
  without a spec. So this needs an `EmptyState` entry with values. The design has none to copy; a
  proposal from existing tokens for owner sign-off:
  - title ≤ h1: 15px/600/`-.02em` (`.card-h h3`) or 19px (`--text-sheet-title`)
  - body 13px `--text-3` (`--text-hdr-sub`)
  - `btn-p` CTA
  - optionally a `--surface` `--radius-card` card, padding 20px / 16px

  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses `EmptyState` (`COMPONENT_INVENTORY.md` F1): the 15px
  title, 13px `--text-3` body, `btn-p` CTA, on a `--surface` card. It replaces the three copies.
  → **FIXED** (2026-10-13): the three copies now render `EmptyState` (15px/600 title, 13px `--text-3` body, `--surface` card, primary CTA); trailing periods dropped from the en/it titles. Verified as `demo-empty` at 1440 and 375 (computed `h3` 15px). Re-screenshotted the six `*-empty-f103.png`.
- F-104 `VISUAL` **Status: FIXED** (2026-10-11) The Stats empty state always links "Create new tally" (`tallies.create`), while
  its own copy says to add a transaction. An account that has tallies but no transactions in the
  range gets sent to create another tally. It should open Add transaction (`useAddTransaction`) when
  tallies exist. Frontend only.
  → **FIXED** (2026-10-11): `StatsEmptyState.vue` now opens Add transaction (`useAddTransaction`); with zero tallies the overlay already shows "No tallies yet" + New tally (ES-05). Verified as `demo-empty` at 1440 and 375; re-screenshotted `desktop/stats-empty-0.png`, `mobile/stats-empty-0.png`.

**ES-05 D/M — add-tx with zero tallies**
- PASS: the sheet/dialog shows "No tallies yet" and a body line, then "New tally".

**ES-04 — tally with no transactions**: fixture added — `DemoSeeder` now creates a fourth tally for
demo@tallypal.test, "Fresh Start" (solo, no transactions, `c5`/☕; `DemoSeeder::EMPTY_TALLY_NAME`).
Screenshotted at 1440×900 and 375×812 (`tallies/{uuid}/transactions`): header, "0 ITEMS" and the empty
card ("Nothing here yet — add your first transaction above.") render. Not yet diffed against a design
frame; observations for the next pass:
- The balance area renders as an empty rounded bar (desktop 466×52 left card, mobile a 44px strip)
  with no text. With no transactions there are no per-currency heroes, so it reads as a broken card.
- The empty copy says "add your first transaction **above**", but the add action is the header
  button (desktop: "Add expense", top right) and nothing sits above the card on mobile.

**ES-04 D/M — judged 2026-10-13** (no design frame; against `COMPONENT_INVENTORY.md` F1 `EmptyState`,
`--text-h1`/`CardHeader` typography, `--space`/card tokens). Re-shot after the seeder re-run:
`desktop/tally-empty-0.png` (1440×900), `mobile/tally-empty-0.png` (375×812). Theme dark, no horizontal
overflow at either size. PASS: header (26px/600 h1 + sub, back / Share / settings / Add expense), the
"Transactions" `CardHeader` (15px/600), `--surface` card with `rounded-card`.
- F-108 `VISUAL` **Status: FIXED** (2026-10-13) The empty card is a bare centered 13px `--text-3` line inside the Transactions card
  (`transactions.empty_state`, `tallies/transactions/Index.vue:264`). Inventory F1 names this screen as
  an `EmptyState` use (title 15px/600, body 13px, optional primary CTA); there is no title and no CTA, so
  the one action that fixes the state is not offered where the user is looking. Frontend only.
  → **FIXED** (2026-10-13): the card renders `EmptyState` (title "No transactions yet", body, primary "Add expense" CTA → `tallies.transactions.create`, the header button's URL; transparent bg so it doesn't nest a second surface). New keys `transactions.empty_title` / `empty_description` (en + it), `empty_state` removed. Re-shot `desktop/tally-empty-f108.png` (1440×900), `mobile/tally-empty-f108.png` (375×812): computed `h3` 15px, no horizontal overflow.
- F-109 `VISUAL` **Status: FIXED** (2026-10-13) The balance column renders an empty `--surface` rounded bar (desktop 466×52 at
  x268/y120, mobile 335×44) with no text: with no transactions there are no per-currency blocks, but
  the card shell still renders, so it reads as a broken card and pushes the empty card down. Should be
  omitted when there is nothing to total. Frontend only.
  → **FIXED** (2026-10-13): the card is `v-if="currencyTotals.length > 0"`; the empty tally shows no balance card at either size. Household (EUR+USD, settle banners, split) re-shot unchanged: `desktop/detail-f108-regress.png`, `mobile/detail-f108-regress.png`.
- F-110 `VISUAL` **Status: FIXED** (2026-10-13) Copy: "add your first transaction **above**" (en; the it copy needs the same check).
  On desktop the Add expense button is in the header, ~100px up and to the right; on mobile it is the
  small round `+` in the header. Fixed by F-108 (CTA in the card) or by dropping "above".
  → **FIXED** (2026-10-13): "above" is gone in en and it (new body: "Add the first expense to start splitting this tally." / "Aggiungi la prima spesa per iniziare a dividere questo tally.").

**LE-02 D/M — judged 2026-10-13** via `/dev/error-page` (real 404 status; run with the Debugbar hidden
as in the other shots). `desktop/error-404-page.png`, `mobile/error-404-page.png`. PASS: dark palette,
`--text`/`--text-3` colours, Geist, mono `.eyebrow` (11px, `.12em`, `--text-3`) with the rule, no
horizontal overflow, single CTA "Back to home". Other statuses (500, 403, 503: the `generic` copy)
were not rendered: the preview route only serves 404.
- F-111 `VISUAL` **Status: FIXED** (2026-10-13) Sizes are hardcoded Tailwind steps, not tokens: h1 `text-5xl md:text-6xl lg:text-7xl`
  = 48px (375) / 72px (1440), `leading-none`, `-0.03em`; description `text-lg` = 18px. Every other
  app screen title is `--text-h1` (26px/24px measured, see VERIFICATION SSOT) and body copy tops out
  at 15px, so the error page is a 2.8× larger title in a different voice. It is also not the marketing
  context (no app shell, but not `Tallypal Site.html` either), so `--text-hero` doesn't apply. Needs
  an owner call on whether the 404 is an app screen (use `--text-h1` + 15px body) or a display
  moment (add a documented token); until then it breaks the "no hardcoded font sizes" rule.
  **Owner decision (2026-10-13):** the error page belongs to the **public context** (auth/marketing), not the app shell. It uses the display/marketing type tokens and keeps the shared `AppLogo`.
  → **FIXED** (2026-10-13): h1 `text-hero` (`clamp(40px,6.2vw,74px)`, lh 1.02, `-0.045em` as in `MarketingHero`; measured 40px at 375, 74px at 1440), body `text-lede` (`clamp(16px,1.4vw,18.5px)`, lh 1.55, `max-w-[50ch]`; measured 16px / 18.5px). No hardcoded sizes left in `ErrorPage.vue`.
- F-112 `VISUAL` **Status: FIXED** (2026-10-13; wordmark half ACCEPTED) Voice and brand mark differ from the shell: the header wordmark is lowercase two-tone
  "tally**pal**" (`AppLogo size="md"`), while the sidebar brand block is "Tallypal" (`--text-brand`
  16/600). The title keeps a trailing period ("Page not found.") against the F1 rule "plain sentence,
  no trailing period" (the editorial voice is gone); the generic copy likely does too.
  → **Wordmark half ACCEPTED** (2026-10-13): the lowercase two-tone `AppLogo` stays, per the F-006 note (`AppLogo` is shared with the marketing header/footer, auth layouts and the error page, a separate visual context from the sidebar brand block) and the public-context decision above.
  → **Title half FIXED** (2026-10-13): trailing periods dropped from `title_em` in `not_found` and `generic`, en + it ("Page not found", "Oops! Something went wrong").

**LE-02 — 403 / 500 / 503 (2026-10-13)**: the preview route is now `GET /dev/error-page/{status?}` (`403|404|500|503`, default 404, real status code, same non-production guard; other values 404). All four screenshotted at 1440×900 and 375×812, dark, no horizontal overflow, h1/body sizes as above, layout identical to the 404 (the two-line title wraps cleanly at 375).
- Copy: 403, 500 and 503 all render the `generic` block ("Something went wrong" eyebrow, "Oops! Something went wrong", "We're working on it — try again in a moment or head back home."). Layout PASS. Copy is **not status-specific**: for 403 "We're working on it" is misleading (nothing is broken, access is denied), and 503 gets no maintenance wording. Left as is: new copy is a content decision, not a token/layout fix. F-113 below.
- F-113 `VISUAL` **Status: FIXED** (2026-10-13) Copy not status-specific for 403/503.
  → **FIXED** (2026-10-13): 403 and 503 have their own copy (en + it, owner-supplied; "tally" kept as in `it.ts`); 404 and 500 unchanged. Eyebrows follow the 404 pattern ("403 · Forbidden" / "403 · Accesso negato", "503 · Unavailable" / "503 · Non disponibile"). Verified through `/dev/error-page/{403,404,500,503}` at 375×812 and 1440×900 in en (all four) and it (all four texts read back; screenshots for 403/503): no horizontal overflow, titles wrap on two lines at both sizes. `DevPreviewRoutesTest` asserts each title per locale by reading the locale files (the title renders client-side, so there is no server-side prop to assert).

**LE-01 — loading**
- No deferred props exist (`grep Inertia::defer|optional app/` → 0), so there's nothing to check
  on Home / Stats / detail. The skeletons that do exist were checked: the Add-tx picker (3 bars) and
  the per-tally form loader (4 bars) use `ui/skeleton` with `rounded-card` inside the sheet: PASS
  (`mobile/loading-addtx-picker.png`, `-form.png`). The detail page's `WhenVisible` page-2 fallback
  wasn't captured (the request-delay intercept failed), and B-08 changes that list anyway.

**LE-02 — error page**: `bootstrap/app.php` still renders `ErrorPage` only outside `local`/`testing`
(production error handling untouched). To see it locally, `routes/web.php` registers
`GET /dev/error-page` (`dev.error-page`) inside the same `! app()->isProduction()` guard as
`/dev/design-system`; it renders `ErrorPage` with `status: 404` and answers with a real 404. Screenshotted
at 1440×900 and 375×812: logo, "404 · NOT FOUND" eyebrow, "Page not found.", sub, "Back to home".
Not yet diffed against a design frame.

**LE-03 D/M — validation**
- F-105 `VISUAL` **Status: FIXED** (2026-10-13) · (superseded) **BLOCKED-COMPONENT** A server error (New tally with an empty name, `required`
  attribute removed in devtools to reach the server) renders as 14px `--neg` text under the field.
  The input itself shows no error state (border stays transparent, no `aria-invalid`). Before that,
  the browser's own unstyled `required` bubble fires (it's localized by the browser: "Compila questo
  campo."). Add-tx disables its submit until the form is valid, so it shows no field errors at all.
  Inventory E1 lists an "(add) error" state with no values. Needs owner values; the design has none.
  Proposal, mirroring the focus rule (`border-color: --accent`):
  - field border `1px --neg`
  - message 11.5–12px `--neg`, `margin-top:6px`
  - `aria-invalid` + `aria-describedby`

  (superseded) → **UNBLOCKED (2026-10-11), open.** Uses the `FormField` error state
  (`COMPONENT_INVENTORY.md` E1): `--neg` border, 12px `--neg` message `margin-top:6px`,
  `aria-invalid` + `aria-describedby`, no native `required` bubble.
  → **FIXED for New tally** (2026-10-13): `required` removed from the name input; the empty submit shows `aria-invalid=true`, `--neg` border, 12px message via `aria-describedby=name-error`. Add-tx submit stays disabled until valid, so it has no server field errors to show. Re-screenshotted `desktop/modal-newTally-error-f105.png`.

**BC-01 D/M — `/dev/design-system`**
- F-106 `VISUAL` **Status: FIXED** (2026-10-13) → **FIXED** (2026-10-13), see Fixes applied. The showcase still uses the removed editorial style. Section labels are mono,
  wide-tracked uppercase 11px ("BUTTON — VARIANTS"). The `SegmentedControl` default variant renders
  mono labels with an accent-filled active pill. "Underline input (editorial default)" is shown as a
  live variant, and `TallyShareDialog` (F-097) still uses it. Dev-only page, but views can still
  pick these variants.
- F-107 `VISUAL` **Status: FIXED** (2026-10-13) → **FIXED** (2026-10-13), see Fixes applied. The showcase demos cut or stubbed items as live examples: notifications bell icon
  button (DO-01), "Scan" quick action (DO-02), "Remind" on the settle banner (DO-05), "Apr 04—11" /
  "8 days" / "3 invoices" (DO-03), and the "Transfer" segment (DO-07). Two demo defects:
  - The settle banner drops the space before the amount ("You owe Sara€ 21.00").
  - In a narrow cell the tally-card demo wraps the amount ("€" / "487.40"), because the amount has
    no `whitespace-nowrap`.
- PASS (M): no horizontal overflow at 375px (`scrollWidth` 360 = viewport).

**Observations (outside the visual diff, not classified)**
- O-03 **Status: FIXED** (2026-10-11) `StoreTallyRequest` allows `name` up to `max:255`, but `tallies.name` is `string(25)`. SQLite
  (local) doesn't enforce the length; on MySQL/Postgres a 26–255-char name would fail at the DB.
  Not tested beyond reading the rules.
  → **FIXED** (2026-10-11): migration `widen_name_on_tallies_table` takes `tallies.name` to `string(50)`;
  `StoreTallyRequest` and `UpdateTallyRequest` (was `max:25`) both validate `max:50`; the New tally and
  Edit tally inputs got `maxlength="50"`. Feature tests at 50 (stored) and 51 (rejected) for create and
  update (`tests/Feature/TallyCreateTest.php`) and the API boundary in `TallyValidationTest.php`.
- O-10 **Status: FIXED** (2026-10-11, resume notes updated; renumbered from a duplicate O-04 on 2026-10-14) **DemoSeeder re-run, stale UUIDs.** Since the last session, `DemoSeeder` was re-run, so the stored tally UUIDs 404'd (resume
  notes updated).

---

## BACKEND mini-specs

**B-01 — Sidebar tally list** (F-010)
- **Status: FIXED** (2026-10-13, 59acc35)
- **Decision: BUILD · (superseded) Status: BACKEND.** Action: implement per this spec. Data source is
  `TallyRepository::tallySummaries()` (already returns uuid/name/color/glyph and the per-currency
  `monthly_totals[]`, O-02). Add the prop in `HandleInertiaRequests::share()`.
  **Rewritten 2026-10-13 for the per-currency model (O-04).**
- Endpoint: an Inertia shared prop in `HandleInertiaRequests::share()`,
  `sidebar_tallies`, as `Inertia::once()` (v3) so it isn't re-sent on every visit. Alternative:
  extend `GET tallies/picker`.
- Shape: `Array<{ uuid: string, name: string, color: 'c1'…'c6'|null, glyph: string|null,
  monthly_total: { amount: number, currency_symbol: string, currency_code: string, decimals:
  number }|null }>`, ordered like `tallies.index`. `monthly_total` is the tally's **first
  `monthly_totals` entry** (its most-used currency) — the sidebar row has room for one amount; it
  is never a sum across currencies. `null` for a tally with no transactions (the row shows no
  amount).
- Validation: none (read-only).
- Schema change: no.
- Policy/auth: authenticated only, `Tally::forUser($user)` scope. Cache per user under a versioned
  `HomeCacheService::key()` and flush with the existing `HomeService::forgetCacheForUser` hook.
- Test: `SharedSidebarTalliesTest` — guest gets no prop; a user sees only owned + member tallies;
  another user's tally is absent; a mixed EUR/USD tally exposes only its first `monthly_totals`
  entry (most-used currency), not a sum; month filter on `transaction_date`.

**B-02 — Transaction search** (F-015)
- **Status: ACCEPTED** (UI-only stub: disabled search pill, tooltip "Search coming soon", no endpoint; real search deferred)
- **Decision: STUB · (superseded) Status: DESIGN-ONLY/STUBBED.** Action: render the search box UI but disable
  it with the tooltip "Search coming soon". **Do not** create an endpoint or wire it to a route;
  the UI placeholder is enough. Real search is deferred to a later phase. The spec below is kept
  for that phase only; it is **not** part of the current implementation.
- Endpoint (deferred): `GET /transactions/search?q=` (web JSON, `useHttp`), or an Inertia page
  `GET /transactions?q=`.
- Request: `q` string 2–50 chars; optional `tally` uuid.
- Response: `{ data: Array<{ uuid, title, amount, type, transaction_date, category_code,
  category_name, tally_uuid, tally_name, currency_symbol }>, next_cursor: string|null }`, max 20
  items, newest first.
- Validation: `q` required, min 2, max 50; `tally` exists and is visible to the user.
- Schema change: no (an index on `transactions.title` is optional).
- Policy/auth: limit to tallies where `TallyPolicy::view` passes; never match across other users'
  tallies.
- Test: `TransactionSearchTest` — matches title case-insensitively, excludes foreign tallies,
  validates `q` length, caps results.

**B-03 — Month-over-month delta** (F-022; also Stats "vs March" lines, Batch 4)
- **Status: FIXED** (2026-10-13, 09f2af0, 575a189)
- **Decision: BUILD · (superseded) Status: BACKEND.** Action: implement per this spec. Extend
  `HomeService::buildHomePayload` to call `monthlyBalance()` twice (current and previous month)
  and compute `delta_percentage`. **Rewritten 2026-10-13 for the per-currency model (O-04).**
- Currency: both months are computed in the **primary currency** (B-10 default, else most-used;
  the same `primaryCurrency` the hero uses), so the delta compares like with like. Other
  currencies never enter the delta and get no delta of their own (they keep the hero's quiet
  "Other currencies" line).
- **Owner decision (2026-10-13), delta definition:** `delta_percentage = (current − previous) /
  |previous| × 100`, rounded to 1 decimal, `null` when previous is 0. The arrow direction is the
  sign of `current − previous` (= the sign of `delta_percentage`), so an improvement from a negative
  balance shows ▲.
- **Owner decision (2026-10-13), amended — replaces "whole previous calendar month":** the delta
  compares **period-to-date with the same span of the previous period** (`transaction_date`). Month:
  Oct 1–13 vs Sep 1–13. The span is **clamped** when the previous month is shorter (Mar 31 → Feb
  1–28, or 1–29 in a leap year). Previous-month transactions dated after today's day-of-month are
  excluded. The previous net is therefore cached **per day** (`previous_net:{Y-m-d}`), and
  `KEY_VERSION` went `v5` → `v6`. Implemented in `HomeService::buildHomePayload` (end =
  `min(today's day, days in previous month)`). Tests written first in `HomeBalanceDeltaTest`:
  mid-month span, excluded later days, 1st of the month, Mar 31 (2026) and Mar 31 (2028 leap),
  unclamped Mar 28, per-day caching, 1 Jan → December.
  → **DONE** (2026-10-13): `HomeService::withMonthOverMonthDelta()` on the cached primary-currency
  balance; `HomeCacheService::previousMonthNet()` (key `previous_net:{Y-m}` before the amendment above, forgotten by
  `forgetForUser`) reuses `TransactionStatsRepository::monthlyBalance()` with the primary currency
  already resolved by O-04 (no new currency selection); `KEY_VERSION` bumped `v4` → `v5`. Empty
  payload: `previous_net 0`, `delta_percentage null`. Frontend: `BalanceShape` gains the two fields;
  `HomeBalanceGrid` passes `delta` to the existing `BalanceHero` → `TrendBadge` (value only, "▲ 27.1%"
  on the demo data; no "vs <month>" caption, no new UI). Tests: new `HomeBalanceDeltaTest` (positive,
  negative, negative → positive ▲, smaller loss ▲, previous 0 / no activity → null, month boundary
  with injected `CarbonImmutable` incl. 1 Jan, other currency ignored, user default currency, the
  Inertia prop) written first; `HomeServiceTest`/`HomeCacheServiceTest` updated. Screenshots
  `app/{desktop,mobile}/home-b03-0.png` compared with `design/desktop/home-0.png`: layout unchanged,
  no horizontal overflow at 375; the pill is the only addition. `npm run build`, `vue-tsc`,
  `composer test` (644 tests, coverage 98.0%) and `composer test:mutate` (score 97.37%) pass.
- Endpoint: extend `HomeService::buildHomePayload` → `balance.previous_net` and
  `balance.delta_percentage` (`null` when the previous month's primary-currency net is 0).
- Shape: `{ …BalanceShape (incl. other_currencies), previous_net: number, delta_percentage:
  number|null }`; `previous_net` is in `balance.currency_code`.
- Validation: n/a.
- Schema change: no. Bump `HomeCacheService::KEY_VERSION` (the cached balance shape changes).
- Policy/auth: same scope as the current balance.
- Test: extend `HomeServiceTest` — positive/negative delta, previous-month zero → `null`, month
  boundary handled with `CarbonImmutable` injection; a USD-only previous month with a EUR primary
  gives `null` (USD is not compared).

**B-04 — Per-user open balance summary** (F-027, F-033; reused on Tallies list / Stats "Owed to you")
- **Status: FIXED** (2026-10-13, 0d65f83)
- **Decision: BUILD · (superseded) Status: BACKEND.** Action: implement per this spec. Refactor or wrap
  `GetTallyDebtsAction` (per-tally today) to add `my_balance` and `settle_suggestion` to the Home
  payload.
- Endpoint: extend the Home `tallies[]` item with `my_balance: { amount: number, direction:
  'owed_to_me'|'i_owe'|'settled', counterparty_name: string|null, currency_symbol: string }`, and
  add a top-level `settle_suggestion: { tally_uuid, counterparty_name, amount, currency_symbol }|null`
  (the largest amount owed to the user).
- Built on the existing `GetTallyDebtsAction`, filtered to the current user.
- Validation: n/a.
- Schema change: no.
- Policy/auth: only tallies the user can view.
- Test: `HomeServiceTest` — owed-to-me, I-owe, settled, multi-currency (no cross-currency netting),
  no suggestion when everything is settled.
- **Owner decision (2026-10-13) — supersedes the Endpoint/Test lines above where they differ:**
  - **`my_balance` is per currency (O-04):** `my_balance: Array<{ currency: { code, symbol, decimals },
    amount: int, direction: 'owed_to_me'|'i_owe'|'settled', counterparty_name: string|null,
    counterparties_count: int }>` on each Home `tallies[]` item, one entry per currency the tally has
    transactions in, most-used first. `amount` is the user's **signed** net position in that currency in
    that tally, in **integer minor units** (+ owed to me, − I owe, 0 settled). The counterparties are the
    other side of the user's settlements in that currency (from the netting); `counterparty_name` is set
    only when there is exactly one, otherwise `null` and `counterparties_count` drives "2 people owe you".
  - **`my_balance` also on the Tallies index items** (F-050's data), from the same cached value.
  - **`settle_suggestion: { tally_uuid, counterparty_name, amount: int, currency: { code, symbol, decimals } }|null`.**
    Candidates are single debts owed **to** the user (one debtor, one tally, one currency), never what the
    user owes. (1) The largest candidate in the user's primary currency (B-10 default, else most-used);
    ties → the tally with the most recent activity, then ledger order. (2) If nothing is owed in the
    primary currency: the tally with the most recent activity (max `transaction_date`, ties → higher
    `tally_id`) among those with a candidate, its most-used currency, the largest amount there. Amounts
    are never compared across currencies. `null` when nothing is owed to the user (everyone settled, or
    the user only owes).
  - **Per-member settled state (F-061 "X is settled"):** `TransactionController::index()` sends
    `settled_members: Array<{ currency, members: Array<{ id, name }> }>`, per currency the current members
    whose position is exactly 0.
  - **Netting (`GetTallyDebtsAction`, changed once):** integer minor units end to end. Participants are
    the B-07 members (owner first, then the others by id, owner once; an owner without a `tally_user` row
    takes the remainder share) **plus every former member with transactions in the tally, at a 0%
    share** — so a member who left keeps an open balance equal to what they paid net (their share was
    redistributed by `DeleteTallyShareAction`; there is no share history, O-08). Fair shares split the
    net spent by integer weights (share × 100) normalised by their sum (all zero → equal); the
    **rounding remainder** goes one unit at a time to the largest fractional remainders, ties in B-07
    member order. Positions sum to exactly zero per currency. `handle()` keeps its output (`debts`
    stays a float in major units, so the tally detail page is unchanged); all new B-04 fields are
    integer minor units.
  - **Cache:** per-user key `balances`, forgotten by `forgetForUser`; `KEY_VERSION` `v6` → `v7`. A
    transaction (settling is a transaction) or a share change flushes every member
    (`forgetCacheForTallyMembers`).
  - **Frontend:** the new data is only typed and passed as props to existing components; no new UI.
    F-025, F-027, F-033, F-050, F-061 stay **OPEN**.
  - **Follow-up (not done):** convert the `debts` prop to integer minor units (with the tally detail
    page's formatting).
  → **DONE** (2026-10-13). `GetTallyDebtsAction` gains `ledgers(Collection<Tally>)` (one grouped
  query: per tally, party and currency `SUM(±ROUND(amount))` + `COUNT(*)`) and `ledger(Tally)`:
  `{currency_id, currency, positions[{user, is_member, amount:int}], settlements[{debtor, creditor,
  amount:int}]}` per currency; `handle()` is built on it and keeps its shape. Its catch block is the
  O-06 target (`report()` + `abort(500)`). The B-07 member definition moved from `TallyRepository`
  (private `membersOf`/`sharesOf`) to `Tally::membersInOrder()` / `Tally::memberShares()`, used by
  both. New `GetBalanceSummaryAction`: `handle($userId, $tallyIds)` → `my_balances` (by tally uuid)
  + `owed_to_me` candidates; `settleSuggestion($owedToMe, $primaryCurrencyId)`;
  `settledMembers($tally)`. Cached per user under `balances` (`HomeCacheService::balanceSummary`),
  read by `HomeService::buildHomePayload` and `HomeService::myBalances` (Tallies index).
  `TallyUserObserver` also handles `updated` and flushes every member on created/updated/deleted
  (shares changed by `sync()` used to flush nobody else). Frontend: `MyBalance`,
  `SettleSuggestion` types; `settle_suggestion` passed to `HomeQuickActions` (declared, not
  rendered), `my_balance` typed on the Tallies index item, `settled_members` on the detail page
  props. No UI change. Demo data unchanged (no balance differs, see **Balance comparison**).
  **Tests (written first, failing):** `GetTallyDebtsActionTest` (zero-sum dataset: uneven three-way,
  three equal 33.33 shares, net income, 1 cent, 1 cent of income, cancelling out, odd shares;
  100.00 ÷ 3 at 33.33/33.33/33.34; leftover cent to the owner on equal remainders and to the largest
  remainder otherwise; 0.07 at 60/40; float-inexact shares 18.4/16.67; a zero member never paired;
  EUR + USD opposite directions; most-used currency first; departed member at 0%; owner without a
  pivot row; all settled; all-zero shares → equal; JPY decimals; several tallies at once; DB error →
  reported + 500), `GetTallySplitActionTest` (blank name → `··`, line 95; DB error, line 83),
  `GetBalanceSummaryActionTest` (owed/owe/settled, "2 people", EUR + USD, departed member, scope,
  suggestion rungs and ties, null cases, settled members), `TallyTest`, `HomeServiceTest`,
  `HomeCacheServiceTest` (`v7`, `balances`), `HomeControllerTest` (B-10 default primary; a left
  tally never suggested), `TalliesIndexTest`, `Transactions/IndexTest`, `HomeCacheInvalidationTest`
  (transaction, share update, member removal flush every member), `CompareBalancesCommandTest`.
  **Done check (2026-10-13):** `npm run build`, `vue-tsc`, `composer test` (fresh, 739 tests,
  coverage 98.5%) and `composer test:mutate` (1401 tested, 28 uncovered, 3 timeout, **score
  98.04%**) pass. **Mutation, money classes:** `GetTallyDebtsAction` 114/114 (100%),
  `GetTallySplitAction` 54/54 (100%), `GetBalanceSummaryAction` 69/69 (100%): no escaped, uncovered
  or timed-out mutant. Also 100% on the other classes B-04 touches: `Tally` 63, `HomeService` 89,
  `HomeCacheService` 26, `TallyController` 56, `TransactionController` 85, `TallyRepository` 86.

**B-05 — Tally member preview** (F-032)
- **Status: FIXED** (2026-10-13, a624067)
- **Decision: BUILD · (superseded) Status: BACKEND.** Action: implement per this spec. Add initials and
  `split_label` logic to `TallyRepository::tallySummaries()`. A private `initials()` helper already
  exists in `TallyRepository` (another in `GetTallySplitAction`); reuse it rather than adding a
  third.
- Endpoint: extend the Home `tallies[]` item (and the Tallies index item, Batch 2) with
  `members_preview: Array<{ initials: string, color: 'c1'…'c6' }>` (max 3) and
  `split_label: string|null` built from `tally_user.default_share_percentage` (e.g. `"60 / 40"`;
  `null` when equal or solo).
- Validation: n/a.
- Schema change: no.
- Policy/auth: expose initials only, never emails, to co-members.
- **Members (B-07, 2026-10-13):** `users_count` / `member_count` must use `Tally::withMembersCount()`
  (`Tally::membersCount()` for one tally): distinct users, owner counted once. Do not re-derive it
  from `withCount('users')`.
  Also reuse `User::initials()` / `User::avatarColor()` (added by B-07) instead of a third initials helper.
- Test: `HomeServiceTest` — preview capped at 3, deterministic colour, `split_label` for unequal
  and equal splits.

**B-06 — Tallies index list data** (F-046, F-049)
- **Status: FIXED** (2026-10-13, 845a62a)
- **Decision: BUILD · (superseded) Status: BACKEND.** Action: implement per this spec. Copy the
  `tallySummaries()` pattern into `accessibleTalliesWithTotals()`: `withCount`, `withMax`, and the
  boolean fields (`is_shared`). **Updated 2026-10-13 (O-04).**
- **Currency (must, found by O-02, left untouched by O-04):** `accessibleTalliesWithTotals()` uses
  `withIncomeAndExpenseTotals()`, which sums all-time income/expense **across currencies**, and
  `TallyListRow` renders it with its `€` default. Apply the **O-02 per-currency pattern**: replace
  the two scalar totals with `totals: Array<{ income: number, expense: number, currency_symbol,
  currency_code, decimals }>`, one entry per currency the tally has used, most-used first; the row
  shows each currency (or the first, like B-01, if the row has room for one). Never summed, never
  converted.
- Endpoint: extend `TallyController::index()` items with `users_count: int`, `is_shared: bool`
  (`users_count > 1`), `transactions_count: int` and `last_activity_at: ISO8601|null` (max
  `transaction_date`, falling back to `updated_at`). Add `members_preview` and `split_label` from
  B-05. Optional query `?filter=all|shared|private&sort=recent|name` if filtering moves
  server-side; client-side is enough at current volumes. **No `amount` sort (owner decision
  2026-10-13): it would rank tallies across currencies (O-04).**
- Response: `tallies: Array<{ …existing minus the scalar totals, totals, users_count, is_shared,
  transactions_count, last_activity_at, members_preview, split_label }>`.
- Validation (if query params): `filter` in enum, `sort` in enum, both optional.
- Schema change: no (`withCount(['users','transactions'])` + `withMax`).
- Policy/auth: unchanged (`accessibleTalliesWithTotals($user->id)`).
- **Members (B-07, 2026-10-13):** `users_count` / `member_count` must use `Tally::withMembersCount()`
  (`Tally::membersCount()` for one tally): distinct users, owner counted once. Do not re-derive it
  from `withCount('users')`.
  Chain `->withMembersCount()` onto `accessibleTalliesWithTotals()`; `users_count` comes from it.
- Test: extend `TallyControllerTest` index — shared tally reports the right `users_count` and
  `is_shared`; counts respect membership; a EUR + USD tally returns two `totals` entries, not a
  sum; no N+1 (assert query count).

**B-07 — Tally detail header + per-row party** (F-054, F-058, F-066)
- **Status: FIXED** (2026-10-13, a3774ac)
- **Decision: BUILD · (superseded) Status: BACKEND.** Action: implement per this spec. Add `color`, `glyph`,
  `users_count` to the `tally` shape and a `party` object to each transaction in the
  `TransactionController::index()` response.
- Endpoint: `TransactionController::index()` — add `color`, `glyph`, `users_count` to `tally`, and
  `party: { name, initials, color }` to each transaction item (already eager-loaded).
- Response: `tally: { tally_id, uuid, name, description, owner_id, color, glyph, users_count }`;
  `transactions.data[]: { …, party: { name: string, initials: string, color: 'c1'…'c6' } }`.
  **Shipped 2026-10-13:** "Per person" is computed server-side (per O-04, never summed across
  currencies): top-level prop `per_person: Array<{ amount, currency_symbol, currency_code, decimals }>`,
  expense total ÷ `users_count` per currency, most-used first. `party` is `null` if the user is gone.
  `users_count` = `Tally::membersCount()`, the single members definition (owner once). Home cache
  `KEY_VERSION` bumped to `v3` (`member_count` values changed).
- Validation: n/a.
- Schema change: no.
- Policy/auth: unchanged (`authorize('view', $tally)`); expose the party name only to members.
- Test: extend `TransactionControllerTest` index — asserts `tally.color/glyph/users_count` and
  `party.name` on items via `assertInertia`.

**B-08 — Tally transactions infinite scroll replaces instead of appending** (F-068)
- **Status: FIXED** (2026-10-13, f9081f9)
- **Decision: BUILD · (superseded) Status: BACKEND (bug fix).** Action: fix the `Inertia::merge()` wrapping in
  `TransactionController::index()` so page 2+ **appends** to `data` instead of replacing it. The
  brief's wording was inverted; see Notes #2.
- Endpoint: `GET tallies/{tally}/transactions?page=N` (existing).
- Change: return the list as `Inertia::scroll(fn () => $paginator)` (v3 infinite scroll), or
  `Inertia::deepMerge()` / merge `data` with `matchOn('data.uuid')`, and use the matching
  frontend `<InfiniteScroll>` / `WhenVisible` params.
- Response: unchanged paginator shape; the client now keeps `data` cumulative.
- Schema change: no.
- Policy/auth: unchanged.
- Test: feature test for `?page=2` asserting the merge/scroll prop metadata (`mergeProps` /
  `deepMergeProps` contains `transactions.data`), plus a browser/Vue check that 30 rows exist
  after the second load.

**B-09 — Stats filter, KPIs and activity** (F-071, F-075, F-077)
- **Status: FIXED** (2026-10-14, 8a3963b)
- **Decision: BUILD · (superseded) Status: BACKEND.** Action: implement per this spec. Extend
  `StatsService::buildStatsPayload` with the tally filter, the summary cards and the activity list.
  **Rewritten 2026-10-13 for the per-currency model (O-04).**
- Currency: every KPI is in the **primary currency** (`currency_code` already in the payload: B-10
  default, else most-used). Other currencies never enter a KPI; the chart's quiet
  `other_currencies[]` line (O-04) already covers them. **Activity rows each show their own
  currency** (`currency_symbol` + `decimals` per row), never the primary one.
- Endpoint: `GET /stats?range=week|month|year&tally={uuid}` (existing route).
- Request validation: `range` as today; `tally` `sometimes|uuid|exists:tallies,uuid`, and the user
  must pass `TallyPolicy::view` (otherwise 403). Absent `tally` means all accessible tallies. With
  a tally filter the primary currency is still the user's (not the tally's), and
  `other_currencies` is scoped to that tally.
- Response additions to `StatsService::buildStatsPayload`, all KPI amounts in the primary currency:
  `daily_average: number` (`total_out` ÷ elapsed days in range),
  `previous_total_out: number`, `delta_percentage: number|null` (same rule as B-03, **including the
  same-span comparison, owner decision 2026-10-13: week-to-date vs the same days of the previous
  week; year-to-date vs the same date last year; month as B-03, clamped**),
  `shared_total_out: number`, `shared_percentage: number` (expenses in tallies with > 1 member;
  percentage of the primary-currency `total_out`),
  `owed_to_me: { amount: number, open_balances: int }` (from B-04, primary currency only; open
  balances in other currencies are not counted in `amount`),
  `activity: Array<{ uuid, title, amount, type, transaction_date, category_code, tally_uuid,
  tally_name, is_shared, currency_symbol, currency_code, decimals }>` (latest 20 in range, **all
  currencies**, each row in its own currency),
  `filter: { tally_uuid: string|null }`, `tallies: Array<{ uuid, name }>` (filter options).
- Schema change: no.
- Policy/auth: every aggregate scoped to accessible tallies, and to the single tally when filtered.
- Test: extend `StatsControllerTest`/`StatsServiceTest` — foreign `tally` → 403, invalid uuid → 422,
  shared portion counts only multi-member tallies, delta `null` when the previous period is 0,
  activity capped and range-scoped; a mixed EUR/USD range keeps every KPI EUR-only while activity
  rows carry `$` and `€` each.
- **Owner decisions (2026-10-14) — supersede the lines above where they differ:**
  - **Currency:** unfiltered, every figure is in the user's primary currency (O-04), with the quiet
    `other_currencies` line. **Filtered to one tally, every figure is in that tally's currency** in
    the B-11 order (tally default → most-used, `Tally::prefillCurrency()`), with the same quiet
    line for that tally's other currencies. This replaces "with a tally filter the primary currency
    is still the user's (not the tally's)".
  - **Deltas:** period-to-date against the same span of the previous period, exactly as B-03: week
    Mon → today vs the same weekdays last week; month as B-03 (clamped); year Jan 1 → today vs the
    same dates last year (Feb 29 → Feb 28). The B-03 code is called, not reimplemented.
    **`delta_percentage` compares daily averages:** `daily_average` vs `previous_daily_average` =
    `previous_total_out` ÷ the previous span's own day count (they differ from a totals delta only on
    a clamped span: on Mar 31, 31 days vs Feb 1–28).
  - **`owed_to_me`:** from `GetBalanceSummaryAction` (B-04), in the same currency as the other KPIs.
  - **`daily_average`** = `total_out` ÷ elapsed days in the period-to-date span.
  - **`shared_percentage`** = expenses in multi-member tallies ÷ all expenses, same currency; `null`
    when total expenses are 0.
  - **`activity`:** the latest 20 transactions in range, each in its own currency, never summed.
  - **Unit convention:** `owed_to_me` is `{ amount_minor: int, open_balances: int }`. `amount_minor`
    is the only integer minor-unit field (from B-04, named for it). Every other Stats amount is a
    float in major units, like `total_out`.
  - **`HomeCacheService::balanceSummary($userId)`** derives the user's full tally ids itself (the
    `$tallyIds` parameter is gone), so no caller can cache a partial summary under the unscoped
    `balances` key. Stats filters the result afterwards.
  → **DONE** (2026-10-14). **As built:**
  - **B-03 reuse:** the B-03 month span and delta formula moved verbatim out of `HomeService`
    (inline span + private `withMonthOverMonthDelta`) into the new stateless
    `App\Services\PeriodComparison`: `periodStart()`, `previousPeriod()` (month = B-03; week and year
    added under the same rule), `dayCount()` and `deltaPercentage()`. `HomeService` calls it;
    `HomeBalanceDeltaTest` / `HomeServiceTest` B-03 assertions are unchanged and pass.
  - **Request:** `StatsController` validates `tally` `sometimes|uuid|exists:tallies,uuid` inline (422
    on a JSON request; a browser/Inertia visit gets the usual redirect with `errors.tally`, like
    `range`), then `authorize('view', $tally)` (403 for a foreign tally).
  - **Payload** (`StatsService::buildStatsPayload($user, $range, $now, ?$tally)`): `daily_average`,
    `previous_total_out`, `previous_daily_average`, `previous_period {start, end}`,
    `delta_percentage`, `shared_total_out`, `shared_percentage`, `owed_to_me`, `activity[]` (`uuid,
    title, type, amount, transaction_date, category_id, category_name, category_code, tally_uuid,
    tally_name, is_shared, currency_symbol, currency_code, decimals`; `category_id` for the D1 glyph
    map), `filter {tally_uuid}`, `tallies [{uuid, name}]` (every accessible tally by name, also when
    filtered). Empty payload: zero / `null` for each.
  - **New repository methods:** `TransactionStatsRepository::activity()` (one query + currency and
    tally eager-loads, `is_shared` via `Tally::withMembersCount()`); `TallyRepository::filterOptions()`
    (one query with `withMembersCount()`, also gives the shared ids). Shared total and previous total
    reuse `expenseTotal()`.
  - **Query plan:** constant, independent of the number of tallies and transactions (tested with
    the cache cleared, unfiltered and filtered).
  - **Cache:** no new key, `KEY_VERSION` stays `v7` (no cached shape changed). Stats reuses
    `tally_ids`, `primary_currency` and `balances`, already flushed by the transaction, tally and
    share observers and by a default-currency change.
  - **Frontend:** props only. `Stats.vue` declares the new keys (`StatsPeriod`, `StatsOwedToMe`,
    `StatsActivityItem`, `StatsTallyOption`); nothing renders them. The range switch still drops
    `tally` (F-071's wiring). F-070, F-071, F-075, F-076, F-077 stay **OPEN**.
  **Tests (written first, failing):**
  - `StatsKpisTest` (new): USD tally filter (non-zero USD KPIs, EUR on the quiet line); unfiltered
    mixed EUR/USD; B-11 currency order and the no-prefill fallback; week/month/year spans;
    Mar 31 daily-average delta (0.0, where totals would give +10.7%); Feb 29 year clamp; the 1st;
    `null` delta; shared portion and its `null`; `owed_to_me` scope; activity cap, order, tie-break,
    row shape; constant query count; cache freshness after a transaction; a filtered visit on a cold
    cache leaves Home's balances covering every tally.
  - `StatsControllerTest`: 403, member access, 422 (not a uuid, unknown uuid), session error,
    filter options, empty payload keys.
  - `PeriodComparisonTest` (new), `TransactionStatsRepositoryTest` (`activity`),
    `TallyRepositoryTest` (`filterOptions`), `StatsServiceTest`, `HomeCacheServiceTest`
    (`balanceSummary($userId)`).
  **Done check (2026-10-14):** `composer check` exits 0. Stages:
  - `npm run build` ✓ (built in 7.94s);
  - `vue-tsc` clean;
  - Pint and Rector pass, PHPStan 0 errors;
  - Pest **799 tests passed** (3390 assertions), **coverage 98.6%**;
  - `test:mutate`: 1597 mutations in 53 files, 1566 tested, 28 uncovered, 3 timeout, **score
    98.25%**. The 28 uncovered and 3 timeout match the B-04 run; none is in a B-09 class.

  **Mutation, B-09 code** (`--mutate --class=…` per class): 100% everywhere, with no escaped or
  uncovered mutant.
  - New: `PeriodComparison` 29/29, `StatsService` 131/131, `StatsController` 18/18.
  - Touched: `TransactionStatsRepository` 289 tested + 3 timeout, `TallyRepository` 98,
    `HomeService` 77, `HomeCacheService` 26.
  - The 3 timeouts are not named by Pest. They match the three pre-existing `for` loops of the
    history methods; `activity()` has no loop.

**B-10 — User default currency** (F-087; prefills F-092, B-11)
- **Status: FIXED** (2026-10-13, d33c60e)
- **Decision: BUILD · (superseded) Status: BACKEND** (owner, 2026-10-11). **Scope, and only this:**
  1. a nullable `users.default_currency_id` column;
  2. the You → Preferences **"Default currency"** select (`SettingRow` + `PillSelect`, F-087);
  3. **prefilling the currency in Add transaction.**
- Endpoint: add `PATCH settings/preferences` (the hub You page saves one row at a time, F-082) with
  `default_currency_id`; the Profile Name/Email form is untouched. Expose it on the shared
  `auth.user` prop as `default_currency: { currency_id, code, symbol }|null`.
- Request: `default_currency_id` `nullable|integer|exists:currencies,currency_id`.
- Response: redirect back (Inertia), flash as today.
- Schema change: **yes**. `users.default_currency_id` nullable FK → `currencies.currency_id`,
  `nullOnDelete`. Nullable means "no default", which is today's behaviour; the select offers "None".
- Use: Add transaction prefills `currency_id` from the tally currency (B-11), else this default,
  else the app default (EUR, `config('app.default_currency')`; amended 2026-10-13, was "else empty").
  **Registration stores the app default** as the new user's `default_currency_id` (amended
  2026-10-13); existing users with `null` keep `null` (the app-default rung covers them). It is a prefill only: the currency select stays editable. B-11 also uses it as the
  initial value of the New tally currency select.
- Policy/auth: the authenticated user only (no id in the URL).
- Test: `PreferencesUpdateTest`: sets/clears the default; rejects an unknown id (422); the shared
  prop carries the code/symbol; a guest can't reach it. Add-tx form-data endpoint test: the default
  comes back pre-selected when the tally has no currency.

**B-11 — Tally default currency** (F-095; feeds F-092's currency mark)
- **Status: FIXED** (2026-10-13, 6b9df22)
- **Decision: BUILD AS DEFAULT ONLY · (superseded) Status: BACKEND** (owner, 2026-10-11).
  **Tallies keep holding multiple currencies.** The tally currency only **prefills new
  transactions**. It **never restricts** which currency a transaction can use and **never
  converts** amounts. Totals, balances and settlements stay per currency, exactly as today.
- Endpoint: `POST tallies` / `PUT tallies/{tally}` (`StoreTallyRequest`/`UpdateTallyRequest`) accept
  `currency_id`. The add-tx form-data payload (`GET tallies/{tally}/transactions/form-data`) and
  the full-page create route return the tally's `currency: { currency_id, code, symbol }|null` so
  the form can prefill it. Nothing else reads it.
- Request: `currency_id` `nullable|integer|exists:currencies,currency_id`. The New tally select
  starts at the user's B-10 default; the user can clear it.
- Schema change: **yes**. `tallies.currency_id` nullable FK → `currencies.currency_id`,
  `nullOnDelete`. Existing tallies stay `null`. Transactions keep their own required
  `currency_id`, and validation of a transaction's currency does **not** compare it with the
  tally's.
- Use: Add-tx prefills the tally currency, then B-10, then empty; the select stays editable. The
  amount entry shows the selected currency's symbol (F-092). New tally (F-095) and Edit tally show
  the select labelled as a default (hint "Default for new transactions").
- Policy/auth: unchanged (`TallyPolicy::create/update`).
- Test: `TallyControllerTest` store/update: persists `currency_id` and accepts `null`; rejects an
  unknown id. Add-tx form-data test: tally currency wins over the user default. A transaction in
  another currency on a tally with a default still saves (no restriction).
- **Owner decision, built 2026-10-13.** Add-tx prefill order: (1) the tally's default currency;
  (2) the tally's most-used currency (transaction count, ties → lowest `currency_id`); (3) the
  user's default (B-10); (4) the app default currency (`config('app.default_currency')`, EUR, env
  `APP_DEFAULT_CURRENCY`) — **amended 2026-10-13, was "none: the select stays empty"**; the select
  is empty only if the configured code is not a stored currency. New tally prefills the
  user's default. Existing tallies keep `tallies.default_currency_id = null` (no backfill; rule 2
  covers them). As built: column `tallies.default_currency_id` (nullable FK, `nullOnDelete`);
  requests take `default_currency_uuid` (B-10 convention, `nullable|uuid|exists:currencies,uuid`),
  an update that omits it keeps the stored value; the form-data payload and the create route keep
  returning `default_currency_uuid`, now resolved server-side through the order
  (`Tally::prefillCurrency`); `tallies/Create` gets `currencies` + `default_currency_uuid`,
  `tallies/Edit` gets `currencies` + `tally.default_currency_uuid` (unused until F-095's UI).
  `AddTransactionForm` re-applies the prefill when the tally select changes. Tests:
  `TallyDefaultCurrencyTest`. Screenshots: `{desktop,mobile}/addTx-b11-{household,weekend}.png`
  (Household → EUR by usage, Weekend Trip → USD by default, at 1440×900 and 375×812).

**B-12 — Invite at creation + shares for pending invitees** (F-096, F-099)
- **Status: REMOVED** (CUT 2026-10-11)
- **Decision: CUT · Status: REMOVED** (owner, 2026-10-11). **New tally creates a solo tally.**
  Invites and splits happen afterwards, through the Share dialog. "Members & split" and "Invite
  someone" are removed from the New tally dialog (the Type select goes with them, F-096). No
  schema change; invitees have no share until they join (F-099 lists them without a split row).
  The spec below is kept for reference only and is **not** to be built.
- Endpoint:
  - `POST tallies` accepts `invites: Array<{ email, percentage }>` and `split_mode: 'equal'|'custom'`.
    It creates the tally, the owner's `tally_user` row and one `TallyInvitation` per email,
    reusing the `CreateTallyShareRequest` mail flow.
  - `PUT api/tallies/{tally}/shares` (`UpdateTallySharesRequest`) also accepts
    `shares.*.invitation_uuid` as an alternative to `shares.*.user_id`.
- Request validation:
  - `invites` `sometimes|array|max:20`; `invites.*.email` `required|email|distinct`, not the owner's
    email; `invites.*.percentage` `required_if:split_mode,custom|numeric|min:0|max:100`.
  - The owner's share plus the invitee shares must total 100 (same rule as `UpdateTallySharesRequest`).
  - In each `shares.*` item, exactly one of `user_id` / `invitation_uuid`.
- Response: redirect to the new tally (as today). The Share payload lists invitations with
  `share_percentage`.
- Schema change: **yes**. `tally_invitations.share_percentage` `decimal(5,2)` nullable. On accept,
  the invitations controller copies it into `tally_user.default_share_percentage`; on decline or
  expiry, the owner's share takes back that amount (or shares are re-equalised in Equal mode).
- Policy/auth: only the owner invites or edits shares (existing `TallyPolicy`). Invitee emails are
  shown only to members.
- Test: `TallyControllerTest` store with invites creates the invitations and the mail; totals ≠ 100
  → 422; owner email → 422. `TallyInvitationController` accept copies the share; decline gives it
  back. `UpdateTallySharesRequest` accepts `invitation_uuid` and rejects foreign ones.

## DESIGN-ONLY — owner decisions

- **DO-01 Notifications bell** **Status: ACCEPTED** (2026-10-13: bell left out, no placeholder) (F-016) — no notification model or feed exists. Options: drop it, or
  scope it to pending tally invitations + settle reminders (the latter is also DESIGN-ONLY, Batch 5).
  - **Decision: STUB · (superseded) Status: REMOVED from UI (DESIGN-ONLY/STUBBED).** Action: don't port the
    bell from the Home top bar (the app has none today; the design has no dropdown). If the space
    must be filled, render a placeholder icon button with a "Notifications coming soon" tooltip;
    otherwise leave it empty.
    **ACCEPTED (2026-10-13):** the bell is left out entirely, with no placeholder (F-016).
- **DO-02 Import (CSV or receipt)** **Status: REMOVED** (CUT) (F-028) — no importer; receipt OCR was already rejected as an
  unverified claim (#M7).
  - **Decision: CUT · Status: REMOVED from UI.** Action: leave out the "Import · CSV or receipt"
    tile from Home → Quick actions (the design has no upload UI; the app has neither). Rationale:
    previously rejected as an unverified marketing claim (#M7).
- **DO-03 Tally meta concepts** **Status: REMOVED** (CUT) (F-034, F-059) — trip date ranges / day counts, "Monthly"
  recurrence, invoices / "unpaid", per-tally month-over-month %. None are domain concepts today.
  - **Decision: CUT · Status: REMOVED from UI.** Action: leave these out wherever the design shows
    them: Home tally-card meta and footer, Tallies list row sub/cells/status, Tally detail header sub
    and "Days". Keep member count and the owe/settled status (B-04/B-05/B-06). Not modelled in the
    domain.
- **DO-04 Archived tallies** **Status: ACCEPTED** (stub: no Archived segment or archive state; the filter ships All / Shared / Private) (F-047, F-051) — the "Archived" filter segment and the faded archived
  row. There's no archive flag (only delete). If kept, it becomes BACKEND: `tallies.archived_at`
  nullable timestamp, archive/unarchive endpoints, and excluding archived tallies from the add-tx
  picker.
  - **Decision: STUB · (superseded) Status: REMOVED from UI (DESIGN-ONLY/STUBBED).** Action: leave out the
    "Archived" segment (the filter ships All / Shared / Private) and the `opacity:.6` archived row
    styling. If an entry point is needed, render a disabled archive control with a tooltip (or an
    "Archive feature coming soon" label in settings). No archive control exists today.
- **DO-05 Reminders** **Status: ACCEPTED** (stub: no Remind button or reminder system; mail/notification system deferred) (F-062) — the "Remind" button on the settle banner; Batch 5 adds the "Balance
  reminders" preference. No notification/mail reminder exists. If kept: a queued mail
  notification to the debtor, rate-limited per tally/pair, plus a user preference column.
  - **Decision: STUB · (superseded) Status: REMOVED from UI (DESIGN-ONLY/STUBBED).** Action: leave the "Remind"
    button out of the settle banner. The full mail + notification system is deferred to a later
    implementation phase (the brief said "Batch 5", which here is a verification batch; see
    Notes #5).
- **DO-06 CSV export** **Status: REMOVED** (CUT) (F-067, F-078) — "Export CSV" on detail. It also appears on Stats and in You →
  "Export all data". Previously ruled an untrue marketing claim (#M7).
  - **Decision: CUT · Status: REMOVED from UI.** Action: leave out "Export CSV" on the Tally detail
    Transactions card and the Stats All activity card, and the "Export all data · CSV" row in
    You → Data. None exist in the app today. Rationale: previously rejected as an unverified
    marketing claim (#M7).
- **DO-07 Transfer type** **Status: REMOVED** (CUT 2026-10-11) (F-093) — the third Add-tx segment. `TransactionType` is
  `income|expense`; a transfer (between tallies or people) isn't modelled. Settling a debt is the
  nearest existing concept. If kept, it's BACKEND: a new enum case, plus a decision on how it affects
  balances and stats.
  - **Decision: CUT · Status: REMOVED from UI** (owner, 2026-10-11). Action: the type segment stays
    Expense / Income. No enum case.
- **DO-08 Member roles** **Status: REMOVED** (CUT 2026-10-11) (F-100) — "Can edit / Can view" per member in Share. `tally_user` has no
  role, so every member can edit. If kept: `tally_user.role` enum plus `TallyPolicy` checks on every
  write. "Remove" already exists (delete share).
  - **Decision: CUT · Status: REMOVED from UI** (owner, 2026-10-11). Action: member rows have no
    role select; the trash icon stays as Remove.
- **DO-09 Shareable invite link + approval** **Status: REMOVED** (CUT 2026-10-11) (F-101) — the "Invite link" field + Copy and the "Link
  requires approval" toggle. Invitations are per-email tokens only. If kept: a per-tally join token,
  a join route, an approval queue and the toggle column.
  - **Decision: CUT · Status: REMOVED from UI** (owner, 2026-10-11). Action: leave out the link
    field, Copy and the approval toggle; invite by email only.
- **DO-10 "Members can settle up" permission** **Status: REMOVED** (CUT 2026-10-11) (F-102) — no per-tally permission flag exists.
  - **Decision: CUT · Status: REMOVED from UI** (owner, 2026-10-11). Action: leave the toggle out.

## Triage decisions

| Item | Title | Decision | Status | Checklist rows |
|---|---|---|---|---|
| B-01 | Sidebar tally list | BUILD | **FIXED** (2026-10-13, 59acc35) · (superseded) **DONE** (2026-10-13; shared once prop `sidebar_tallies` with each tally's full `monthly_totals[]`, keyed by a version token rotated by `forgetCacheForUser`; the sidebar list UI is F-010) | SH-01 D |
| B-02 | Transaction search | STUB | **ACCEPTED** (UI-only stub: disabled search pill, tooltip "Search coming soon", no endpoint; real search deferred) · (superseded) DESIGN-ONLY/STUBBED (disabled box, "Search coming soon"; no endpoint) | HO-01 D |
| B-03 | Month-over-month delta | BUILD | **FIXED** (2026-10-13, 09f2af0, 575a189) · (superseded) **DONE** (2026-10-13; Home `balance.previous_net` + `balance.delta_percentage`, primary currency; the pill label/"vs <month>" is F-022, open; Stats "vs" lines come with B-09) | HO-02 D/M (+ ST-03) |
| B-04 | Per-user open balance summary | BUILD | **FIXED** (2026-10-13, 0d65f83) · (superseded) **DONE** (2026-10-13; per-currency `my_balance` on Home and the Tallies index, `settle_suggestion`, `settled_members`; UI findings F-025/F-027/F-033/F-050/F-061 open; Stats "Owed to you" comes with B-09) | HO-03 D, HO-04 D, TA-03 D/M (+ ST-03) |
| B-05 | Tally member preview | BUILD | **FIXED** (2026-10-13, a624067) · (superseded) **DONE** (2026-10-13; Home `tallies[]` only, the Tallies index gets it with B-06) | HO-04 D/M, TA-03 D |
| B-06 | Tallies index list data | BUILD | **FIXED** (2026-10-13, 845a62a) · (superseded) **DONE** (2026-10-13; `totals[]` per currency, members/split/activity wired into the existing `TallyListRow`; segmented filter + Sort UI still to build, no Archived, DO-04) | TA-02 D/M, TA-03 D/M |
| B-07 | Tally detail header + per-row party | BUILD | **FIXED** (2026-10-13, a3774ac) · (superseded) **DONE** (2026-10-13) | TD-01 D/M, TD-02 D, TD-05 D |
| B-08 | Infinite scroll merge | BUILD | **FIXED** (2026-10-13, f9081f9) · (superseded) DONE (2026-10-13) | TD-05 D/M |
| B-09 | Stats filter, KPIs, activity | BUILD | **FIXED** (2026-10-14, 8a3963b) · (superseded) **DONE** (2026-10-14; tally filter (tally's own currency when filtered), daily-average KPIs with the B-03 same-span delta, shared portion, `owed_to_me.amount_minor` from B-04, latest-20 activity; props only, F-070/F-071/F-075/F-076/F-077 open) | ST-01 D, ST-03 D/M, ST-05 D/M |
| DO-01 | Notifications bell | STUB | **ACCEPTED** (2026-10-13: bell left out, no placeholder) · (superseded) REMOVED from UI (DESIGN-ONLY/STUBBED) · ACCEPTED 2026-10-13, no placeholder | HO-01 D/M |
| DO-02 | Import CSV or receipt | CUT | **REMOVED** · REMOVED from UI | HO-03 D |
| DO-03 | Tally meta concepts | CUT | **REMOVED** · REMOVED from UI | HO-04 D, TD-02 D |
| DO-04 | Archived tallies | STUB | **ACCEPTED** (stub: no Archived segment or archive state; the filter ships All / Shared / Private) · (superseded) REMOVED from UI (DESIGN-ONLY/STUBBED) | TA-02 D/M, TA-04 D/M |
| DO-05 | Reminders | STUB | **ACCEPTED** (stub: no Remind button or reminder system; mail/notification system deferred) · (superseded) REMOVED from UI (DESIGN-ONLY/STUBBED) | TD-03 D/M |
| DO-06 | CSV export | CUT | **REMOVED** · REMOVED from UI | TD-05 D, ST-05 D/M (+ YO-04, Batch 5) |
| B-10 | User default currency | **DONE** (2026-10-13) — BUILD (2026-10-11) | **FIXED** (2026-10-13, d33c60e) · (superseded) BACKEND (nullable column + Preferences select + Add-tx prefill) | YO-03 D/M (+ MO-01) |
| B-11 | Tally default currency | **DONE** (2026-10-13) — BUILD AS DEFAULT ONLY (2026-10-11); backend + props only, the New/Edit tally select UI was F-095 ((superseded) open; now FIXED) | **FIXED** (2026-10-13, 6b9df22) · (superseded) BACKEND (prefills new transactions; never restricts or converts) | MO-02 D/M (+ MO-01) |
| B-12 | Invite at creation + invitee shares | CUT (2026-10-11) | **REMOVED** · REMOVED (solo create; invites/splits via Share) | MO-02 D/M, MO-03 D/M |
| DO-07 | Transfer type | CUT (2026-10-11) | **REMOVED** · REMOVED from UI | MO-01 D/M |
| DO-08 | Member roles | CUT (2026-10-11) | **REMOVED** · REMOVED from UI | MO-03 D/M |
| DO-09 | Invite link + approval | CUT (2026-10-11) | **REMOVED** · REMOVED from UI | MO-03 D/M |
| DO-10 | "Members can settle up" | CUT (2026-10-11) | **REMOVED** · REMOVED from UI | MO-03 D/M |

## Totals

_Derived 2026-10-14 from the current status at the start of each entry (F-001..F-113, O-, B-, DO-). Class = the class tag the finding was filed under; it never changes after triage. A finding counts once, under its current status. The old per-class running table (including its PASS sub-check and Blocked rows) was dropped: PASS sub-checks are not findings, and nothing is blocked._

**Findings by batch**

| Status | Batch 1 | Batch 2 | Batch 3 | Batch 4 | Batch 5 | Batch 6 | Batch 7 | Total |
|---|---|---|---|---|---|---|---|---|
| FIXED | 39 | 7 | 13 | 9 | 9 | 8 | 11 | 96 |
| ACCEPTED | 1 | 2 | 3 | 0 | 0 | 1 | 0 | 7 |
| REMOVED | 2 | 0 | 2 | 1 | 0 | 5 | 0 | 10 |
| OPEN | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| **Findings** | 42 | 9 | 18 | 10 | 9 | 14 | 11 | 113 |

**Findings by class**

| Class (as tagged) | FIXED | ACCEPTED | REMOVED | OPEN | Total |
|---|---|---|---|---|---|
| VISUAL | 62 | 3 | 0 | 0 | 65 |
| MISSING-UI | 17 | 0 | 0 | 0 | 17 |
| DEAD-UI | 1 | 0 | 0 | 0 | 1 |
| BACKEND | 16 | 0 | 1 | 0 | 17 |
| DESIGN-ONLY | 0 | 4 | 9 | 0 | 13 |
| **Total** | 96 | 7 | 10 | 0 | 113 |

**Observations, BACKEND and DESIGN-ONLY items**

| Item type | FIXED | ACCEPTED | REMOVED | OPEN | Total |
|---|---|---|---|---|---|
| Observations O-01..O-08, O-10 (O-09 was not raised) | 8 | 1 | 0 | 0 | 9 |
| BACKEND B-01..B-12 | 10 | 1 | 1 | 0 | 12 |
| DESIGN-ONLY DO-01..DO-10 | 0 | 3 | 7 | 0 | 10 |

Open items: no finding.

Batch 5–7 findings: F-080..F-113 (34). ES-04 and LE-02 were unverifiable at first; both have a harness since 2026-10-11 (DemoSeeder "Fresh Start" tally; `/dev/error-page`) and were judged against tokens/components on 2026-10-13 → F-108..F-113.

## Blocked items (batches 1–7)

Owner decisions of 2026-10-11 cleared every token and component blocker and four of the five
mappings. Each unblocked finding carries an **UNBLOCKED (2026-10-11)** line naming what it now uses.

**Still blocked**
- None. F-037 (category → glyph) was the last one; its map was approved and recorded the same
  day (2026-10-11).

**Unblocked 2026-10-11 → what each now uses**

| F-id | Was blocked on | Now uses |
|---|---|---|
| F-006 | token | `--radius-mark`, `--text-brand` |
| F-007 | token + F-088 | `Avatar md` (32px); F-088 done → plain link to You (D6) |
| F-021 | token | `--radius-spark` |
| F-036 | component | `CardHeader`, `DataTable` |
| F-037 | mapping | `category_id` → glyph map (D1), text presentation (U+FE0E) |
| F-039 | component | `CardHeader` |
| F-063 | component | `CardHeader` |
| F-065 | component | `CardHeader`, `DataTable` |
| F-069 | component | `DataTable` (stacked ≤ 640px) |
| F-073 | token + mapping | `--radius-bar`, `--text-axis`; label rule D2 |
| F-076 | component (heading) | `CardHeader` |
| F-082 | mapping | hub page + `SettingRow` rows (D3) |
| F-083 | token | `Avatar xl` |
| F-084 | mapping | Edit → profile settings page (D3) |
| F-085 | component | `CardHeader`, `SettingRow` |
| F-086 | component + mapping | `PillSelect` Light / Dark / System (D4) |
| F-087 | component | `PillSelect`; data still waits on B-10 |
| F-090 | token | `--shadow-modal` |
| F-097 | component | split editor extras (E3); no switch |
| F-103 | component | `EmptyState` |
| F-105 | component | `FormField` error state |

**Waits on a backend item**
_(superseded 2026-10-14: every backend item below is DONE and every finding it lists is FIXED or ACCEPTED; kept as history.)_
- B-01: F-010 (sidebar tally list).
- B-03: F-022 (delta pill).
- B-04: **DONE** (2026-10-13) — F-025 (Settle action), F-027, F-033, F-050 and F-061's "X is
  settled" line are unblocked (data in the payload) and OPEN for the UI.
- B-05 / B-06: F-032, F-043 (sub stats), F-046, F-048 (type pill, avatars, tx count), F-049.
- B-07: F-054, F-055 (glyph + one-line sub), F-058, F-066 (Paid by, also F-065's column).
- B-08: F-068.
- B-09: **DONE** (2026-10-14) — F-070 (sub "· all tallies" / tally name), F-071 (Filter), F-075 (KPI
  cards), F-076 (c5/c7 placement), F-077 (All activity) are unblocked (data in the payload) and OPEN
  for the UI.
- B-10: F-087 (Default currency row), F-092 (prefill, with B-11).
- B-11: F-095 (New tally currency select), F-092 (currency mark, drop the empty select).

**Waits on another finding**
_(superseded 2026-10-14: all of these findings are FIXED; kept as history.)_
- F-007 → the You hub (F-082), so the link has its destination.
- F-084, F-085 (rows), F-086, F-087, F-088 (row layout) → land with the You hub (F-082).
- F-006 rail wordmark overflow (900px) → fix together with the F-006 brand block.
- F-021 → also re-add the `weekly_history` prop to `Home.vue` (frontend, with the token).

## Build first

_Historical build order. Every component and finding run listed here is done (see **Fixes applied** and the status on each finding)._

One run = one fix session. Each new or extended component gets **its own run, before** the
findings that use it, with a `/dev/design-system` example and its own screenshot. The token runs
are already done (wired in `app.css` on 2026-10-11), so token-only findings can be picked at any
point.

**Component runs (in order)**
1. `CardHeader` (B15). **Built 2026-10-11** (`ui/card-header`, `/dev/design-system`; not adopted yet).
2. `DataTable` (B17), first cell reusing `TransactionRow` anatomy, stacked ≤ 640px. **Built 2026-10-11** (`ui/data-table`: `DataTable` + `DataTableItem`, `CategoryGlyph size="table"` 40/13; `/dev/design-system`; not adopted yet).
3. `SettingRow` (B16), incl. `tone="neg"` and link rows. **Built 2026-10-11** (`ui/setting-row`, `/dev/design-system`; not adopted yet).
4. `PillSelect` — `ui/select` `SelectTrigger variant="pill"` (E6). **Built 2026-10-11** (`SelectTrigger variant="pill"`, `/dev/design-system`; not adopted yet).
5. `Avatar size="xl"` (A3). **Built 2026-10-11** (`ui/avatar` `size="xl"` 56px / 18px; `/dev/design-system`; not adopted yet).
6. `EmptyState` (F1). **Built 2026-10-11** (`ui/empty-state`, with/without CTA, `/dev/design-system`; not adopted yet).
7. `FormField` error state (E1). **Built 2026-10-11** (`FormField` provides `aria-invalid`/`aria-describedby` to `Input`/`Textarea`/`SelectTrigger`, `--neg` border, 12px message; `/dev/design-system`; not adopted yet).
8. Split editor extras (E3): `SplitBar`, `SplitLockButton`, `NumberStepper size="sm"`,
   `SegmentedControl size="sm"`. **Built 2026-10-11** (`ui/split-bar`, `ui/split-lock-button`, `size="sm"` on `ui/number-stepper` (editable mono input) and `ui/segmented-control` (`variant="mobile"`); `/dev/design-system`; not adopted yet).
9. **Category glyph map** (F-037): the `category_id` → glyph module used by `CategoryGlyph`,
   with U+FE0E on ✂ ☕ ♥ ✈, rendered as text and tinted by CSS `color`. Add `category_id` to the
   transaction payloads that feed the rows (Home recent, Tally detail). **Verification:** screenshot
   a row of every glyph in the map (incl. ↓ income and ◌ fallback) at **375×812** and
   **1440×900**, in both themes, and confirm none renders as a colour emoji (each glyph must take
   its `--cN` tint).
   **Built 2026-10-11** (`ui/category-glyph/categoryGlyphs.ts`: `CATEGORY_GLYPHS`, `categoryGlyph(id, type)`, exported from the index; `category_id` added to the Home recent payload — Tally detail already sends it; `/dev/design-system` glyph-map row, checked at 375×812 and 1440×900 in both themes; not adopted yet).

**Finding runs (after their components)**
- After 1 + 2 + 9: F-036 (Home Recent card + table). After 1: F-039 (Home By category heading).
- After 1 + 2 + 9: F-065 + F-069 (Tally detail Transactions card + table; Paid by waits on B-07).
- After 1: F-063 (Split so far card), F-076 heading (Stats By category).
- After 1, 3, 4, 5: the You hub — F-080, F-081, F-082, F-083, F-084, F-085, F-086, F-088 row
  layout, "Password" and "Delete account" rows. F-087 joins once B-10 lands.
- After the You hub: F-007 (sidebar user block → plain link to You, dropdown removed).
- After 6: F-103 (+ F-104 CTA target, ES-04 empty card).
- After 7: F-105.
- After 8: F-097 (+ F-099 inline invitees, F-098 sheet `< md`).

**Token-only runs (any time)**
- F-006 (brand block + rail overflow), F-021 (sparkline + `weekly_history`), F-073 (bar radius,
  mono axis, D2 labels), F-090 (dialog shell + `--shadow-modal`).

**No dependency (any time)**
- F-015 (disabled search pill + "Search coming soon" tooltip, B-02 stub), F-025 (drop You from
  Quick actions; Settle after B-04), F-091 (desktop Cancel), F-092 (Tally +
  Date row; currency parts after B-10/B-11), F-094 (combined colour/icon chips, drop Description),
  F-106 + F-107 (`/dev/design-system` cleanup, incl. dropping the cut Transfer demo).

---

## Final pass

**2026-10-14, verification only (no code change).** Run on `feature/app-restyle` at `ee810ad`, built assets current
(`public/build` newer than every `resources/` file). DemoSeeder re-run first. Logged in with the Resume-notes recipe as
`demo@tallypal.test`, then `demo-empty@tallypal.test`. Dark theme unless a row says light. Viewport shots only, in
`.verify/app/final/{desktop,mobile}/` (50 desktop, 29 mobile), each compared side by side with its `.verify/design/` reference.
Batch 7 is judged against the tokens and components. No data was created, edited or deleted: the only submit was an empty
New tally, which failed validation.

**Classes.** `PASS` matches the design · `REGRESSED` was PASS/FIXED and now differs · `STILL OPEN` an unresolved finding
(here every one is **new**: never recorded, present since the commit named, not a regression) · `ACCEPTED` a recorded
STUB/CUT decision or deliberate deviation that renders as decided. One class per row. Precedence: REGRESSED > STILL OPEN >
ACCEPTED > PASS.

**Colour rule (owner decision, 2026-10-14; replaces the colour wording of FP-01).** `--pos` / `--neg` mean good / bad
**for the user** and are used only where the direction is unambiguous: money owed to you, and the Home balance delta.
`--pos` also marks income: money coming to you. Spending figures and spending deltas are neutral. Findings FP-11..FP-16
below follow from it.

### Totals

After the delta pass (2026-10-14, at `63d3398`; see **Delta pass** below) and the fixes for FP-11..FP-16 that followed it.

| Class | B1 | B2 | B3 | B4 | B5 | B6 | B7 | Total |
|---|---|---|---|---|---|---|---|---|
| PASS | 12 | 1 | 4 | 7 | 4 | 1 | 9 | 38 |
| REGRESSED | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| STILL OPEN | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| ACCEPTED | 8 | 8 | 8 | 4 | 4 | 6 | 1 | 39 |
| **Rows** | 20 | 9 | 12 | 11 | 8 | 7 | 10 | 77 |

Before the delta pass the totals were PASS 47 · REGRESSED 0 · STILL OPEN 0 · ACCEPTED 30. The delta pass moved 11 rows. Nine
went PASS → ACCEPTED. None of them looks different; each row shows an owner decision that departs from the design, so the
class definition makes it ACCEPTED: HO-04 D/M, HO-06 D/M, TA-03 D/M, TD-02 M, YO-04 M and MO-02 M. Two went ACCEPTED → STILL
OPEN because of a new finding, FP-11 (ST-03 D/M); FP-11 is now FIXED and both are ACCEPTED again, which gives the table above.

Every recorded F-, B- and DO- status still holds: each FIXED finding renders as fixed and each ACCEPTED/REMOVED one as
decided. The 18 STILL OPEN rows came from 8 new findings, FP-01..FP-08 below; all eight are FIXED (FP-05..FP-08 on 2026-10-14), leaving 0 rows. FP-09 (found afterwards by checking Home against the Stats fix) is FIXED too, and so is FP-10 (the rail's nav tiles). FP-11 (found in the delta pass) is FIXED too, and so are FP-12..FP-16 (the delta pass's minor notes), leaving 0 rows.

### New findings (this pass)

- **FP-01** `DATA` **Status: FIXED** (2026-10-14; owner decision, replacing the earlier "sidebar shows the month's net": **every per-tally amount in the app means money SPENT (expenses), per currency, no sign;** its colour follows the **Colour rule** above, which replaces the earlier "neutral in `--text`, no `--pos`" wording. Money owed to or by the user is shown only by the `my_balance` status lines and pills, which keep their tones). The sidebar tally amount was the month's **expense** total printed with `+` in `--pos`, so spending read as a gain (City Break "+118.40 · $" while its detail showed Net Balance € −118.40). Design: a plain number for spend. Present since F-010 (9abacf0). Rows: SH-01.
  → **FIXED** (2026-10-14): the sidebar prints `monthly_totals[].amount` (the month's spending by `transaction_date`) as a plain `--text` number, and the "· $" marker follows non-zero spending in other currencies. `monthly_totals[].net` is removed (nothing else used it; `KEY_VERSION` v9 → v10). The Home tally card (rail and grid) labels its amount "spent in Oct" / "spesi a ott" (locale month from the new `current_month_start` Home prop, `--text-3` 12px under the amounts). The Tallies list row showed the **all-time net** with `+` in `--pos`; it now shows all-time **spending**, neutral, labelled "total spent" / "spesi in totale". The `positive` / `amountPositive` props are removed from `TallyRailCard` and `TallyListRow` (replaced by `amountCaption`), so a per-tally amount cannot be tinted as a gain.
  Verified at 1440×900 and 375×812 on City Break, Household and Weekend Trip: sidebar (≥ md only), Home card and Tallies row agree on the same tally, and differ only where a label says why (month "spent in Oct" vs all-time "total spent" / "Total spent"). `desktop/fp01b-{home,home-scrolled,citybreak,household,weekendtrip,tallies}-1440.png`, `mobile/fp01b-{home,tallies,citybreak,household,weekendtrip}-375.png`.
  **ACCEPTED (owner, F-058 decision):** a private tally's detail hero keeps "Net Balance" (income − expense) with In/Out beneath (City Break: € −118.40 beside "spent in Oct" 118.40). It is labelled, so it follows the "unless a label says why" rule. On that page the all-time spending figure is now named "Total spent" / "Spesi in totale" (was "Total Expenses" / "Uscite Totali", key `transactions.total_expenses`, used only there), the same name as the Tallies row. Shot: `desktop/fp01c-citybreak-1440.png`.
- **FP-02** `VISUAL` **Status: FIXED** (2026-10-14). Home Recent + By category sit in `mt-9 grid gap-9 lg:grid-cols-[1.4fr_1fr]`
  (`pages/Home.vue:59`), not the 12-column c8/c4 grid with a 16px gap (12px on phone) that the row above uses. At 1440 the split
  is ≈7/5 with 36px gaps (design 8/4, 16px). On the phone the gaps around Recent are 36px (design 12px). From 1024 to 1100px the
  two cards stay side by side (the design stacks at ≤1100) and Recent titles truncate ("Quick grocery …"). Present since 06892ae.
  Rows: HO-05 D/M, HO-06 D; in-between 1024.
  → **FIXED**: `pages/Home.vue` uses the same grid as the row above, `grid-cols-1 gap-3 sm:gap-4 min-[1101px]:grid-cols-12` with `col-span-8` / `col-span-4` and `mt-3 sm:mt-4`. Measured at 1440: Recent 744px, By category 364px, 16px gap (exactly 8/4 of 12); at 1024 both stack at 709px. `desktop/fp02-home-1-1440.png`, `fp02-home-1024.png`, `mobile/fp02-home-{1,2}-375.png`.
- **FP-03** `VISUAL` **Status: FIXED** (2026-10-14). Tally glyphs that have an emoji form (✈ U+2708, ☕ U+2615, ❄ U+2744) render as colour
  emoji, not as `--cN`-tinted text. Confirmed by a zoomed shot of the Tallies rows (`desktop/tally-glyphs-zoom.png`). Also
  visible on the Home cards and rail, in the sidebar list and in the New tally chips. The detail header tints them correctly.
  D1's U+FE0E rule covered category glyphs only. Rows: SH-01, HO-04 D/M, TA-03 D/M, MO-02 D/M.
  → **FIXED**: `asTextGlyph()` (`category-glyph/categoryGlyphs.ts`) ends every glyph on U+FE0E; `CategoryGlyph` and `ChoiceChip` both call it, so the sidebar, Home cards and rail, Tallies rows, detail header and the New tally / Edit tally chips render ✈ ☕ ❄ as tinted text. The detail page's own `replace` was removed. `desktop/fp03-tallies-1440.png`, `fp03-newtally-1440.png`, `fp02-home-1-1440.png` (sidebar), `mobile/fp03-tallies-375.png`.
  **Verified side by side (2026-10-14, a2647bb's gap closed):** sidebar and Tallies rows at 1440 against `design/desktop/home-0.png` and `tallies-0.png`; Tallies rows at 375 against `design/mobile/tallies-0.png`; New tally chips at 1440 and 375 against `design/desktop/modal-newTally-0.png` and `design/mobile/sheet-newTally-0.png`; the Edit tally chips at 1440 and 375 against the same chip row (the design has no Edit screen). ✈ ☕ ❄ are U+FE0E text glyphs in `--cN` (computed colors `oklch(0.83 0.17 158)`, `0.72 0.17 320`, `0.75 0.18 25`), not emoji, on every surface. The Edit page lays its chips out as two rows of round buttons, not the New tally dot+glyph chip; that layout predates FP-03 and is not part of it. Shots: `desktop/fp03v-{tallies,sidebar-zoom,rows-zoom,rows-zoom2,newtally,edittally}-1440.png`, `mobile/fp03v-{tallies,newtally,edittally}-375.png`.
- **FP-04** `VISUAL` **Status: FIXED** (2026-10-14). Category bars are scaled to each category's share of the total: Rent at 58.1% fills 58%
  of the track. The design scales them to the largest category, so the top row is full and the others are relative (widths
  100/60/50/42/18%). Rows: HO-06 D/M, ST-04 D/M.
  → **FIXED**: `barFill()` (`pages/home/utils.ts`) scales each bar to the largest amount in its list (on Stats, the rows as displayed, "Other" included), so the top row is full. Demo: Rent 100%, Groceries 27%, Dining Out 13%. `desktop/fp02-home-1-1440.png`, `fp04-stats-0-1440.png`, `mobile/fp02-home-2-375.png`.
  **Re-opened and fixed again (2026-10-14).** Compared with `design/desktop/stats-0.png` and `stats-1.png`. The design's widths are 100/60/50/42/18 for the five named rows and 85 for Other (461 against Housing 540); Other (461) is smaller than Housing, so the design does not show whether the maximum includes it. Decision: **Other never sets the scale and is capped at 100%.** It is a remainder, not a category, and it can outweigh them all. That exposed a second defect: `categoriesBreakdown()` stopped at 6 rows, so "Other" was only the 6th category (never more than the 5th, so it could not be the largest) and the percentages were of the top six. Stats now asks for every category (`limit: null`); `StatsService::categoryBars()` returns the top five plus one Other row with the rest's summed amount, its share of the true total, `bar_fill` and `is_other`. The component only renders them. Home was still on the 6-row query with no Other row; that is FP-09. Tests: `StatsControllerTest` "category bars are relative…", "Other bar never sets the scale and is capped at 100 when it is the largest amount" (rest 180 vs largest 100 → 100), "without a rest … no Other row". Demo: Rent 100%, Groceries 27%, Dining Out 13%, Other (€ 151.39, 8.9% of the true total) 16%. `desktop/fp04-stats-1440.png`, `mobile/fp04-stats-375.png`.
- **FP-05** `VISUAL` **Status: FIXED** (2026-10-14). The Stats range control is `SegmentedControl variant="mobile"` at every width
  (`pages/Stats.vue:122-127`): 12.5px text, `8px 4px` padding, a 129px track with the labels crowded. The design's desktop
  `.seg` uses 13px text and `8px 16px` padding (≈204px track). The phone size matches. Present since F-072 (2026-10-11).
  Rows: ST-01 D.
  → **FIXED**: the `mobile` `SegmentedControl` takes the design's desktop `.seg` size from `sm` (640px): `sm:px-4 sm:text-[13px]`, so the phone size (12.5px, 4px) is unchanged below 640. The Tallies filter's own `sm:[&>button]:px-4` override is gone. Compared with `design/desktop/stats-0.png` (Week/Month/Year track, then Filter) and `design/mobile/stats-0.png`. `desktop/fp05-stats-1440.png`, `fp05-seg-zoom.png`, `mobile/fp05-stats-375.png`.
- **FP-06** `VISUAL` **Status: FIXED** (2026-10-14). `Button variant="outline"` paints `bg-background` (`ui/button/index.ts:15-16`).
  Inside `--surface` cards it reads as a dark filled pill (You: Edit, Sign out, Delete account). Design `.btn-o`:
  `background: transparent`. On the page background (detail Share/gear, Sort, Filter) there's no visible difference.
  Rows: YO-02 D/M, YO-04 D/M.
  → **FIXED**: `outline` is `bg-transparent` (was `bg-background`); the hover (`surface-2`) is unchanged. Every `variant="outline"` in the app (You Edit / Sign out / Delete account, detail Share and gear, Tallies Sort, Stats Filter, the calendar prev/next arrows; the dev design-system page) takes the new fill. Compared with `design/desktop/profile-0.png` (`.btn-o`), `design/desktop/detail-0.png` (Share) and `design/desktop/tallies-0.png` (Sort). `desktop/fp06-you-1440.png`, `fp06-detail-1440.png`, `fp06-tallies-1440.png`, `mobile/fp06-you-375.png`, `fp06-detail-375.png`.
- **FP-07** `VISUAL` **Status: FIXED** (2026-10-14). New tally dialog (1440): a faint rectangle frames the sticky footer around
  "Create tally". The footer is `sticky -bottom-5 -mx-5 px-5` inside a `px-6 pb-6` body (`NewTallyForm.vue:92`), so it is
  inset 4px. It's visible in the 2026-10-13 `modal-newTally-f095.png` too. Add transaction uses the same classes, but no
  seam shows there (`desktop/modal-addTx-footer-zoom.png`). Rows: MO-02 D.
  → **FIXED**: the seam was not the 4px inset. The dialog is centred with a fractional `translate(-50%, -50%)` (here y = 220.75), and the sticky footer's own background edge is antialiased into a 1px bluish rectangle; at whole-pixel positions it disappears. The footer now has `will-change-transform` (on New tally and, for the same latent edge, Add transaction). Pixel-checked (no non-background row at the footer edges) and compared with `design/desktop/modal-newTally-0.png`. `desktop/fp07-newtally-1440.png`, `fp07-newtally-zoom.png`, `fp07-addtx-1440.png`.
  **Re-checked for blur (2026-10-14, 1440×900, deviceScaleFactor 1):** the dialog sits at y = 220.75, so a composited footer could soften its text. It does not: the "Create tally" glyph edges reach 124 of a 127 contrast range, the same with `will-change` removed (125), and the dialog title's reach 210 of 217; the two renders are visually identical. `will-change-transform` stays. `desktop/fp09-newtally-1440.png`, `fp09-btn-compare.png` (with / without), `fp09-addtx-footer-zoom.png`.
  **Second cause found by that check, FIXED:** on Add transaction (the form overflows, `scrollTop` 0) the footer ended 4px above the dialog's bottom edge (871.3 against 875.3), so the Description field showed through as a 5px strip under the buttons. The desktop modal body is `px-6 pb-6`, and Chrome measures `bottom: -20px` from the scroller's content box (inset by that 24px), so `-bottom-5` falls 4px short. Both forms now add `md:-bottom-6` (the mobile sheet keeps its own padding). Measured 875.33 = 875.33; no non-background pixel under the footer except the 1px dialog border, at scroll top and scrolled to the end. Compared with `design/desktop/modal-addTx-0.png` and `modal-newTally-0.png`. `desktop/fp09b-addtx-top-1440.png`, `fp09b-addtx-bottom-1440.png`, `fp09b-newtally-1440.png`.
- **FP-08** `VISUAL` **Status: FIXED** (2026-10-14, minor). The md icon rail is 48px wide (`ui/sidebar/utils.ts:8`,
  `SIDEBAR_WIDTH_ICON = '3rem'`); the design rail is 72px (`--side: 72px`, `padding: 18px 10px`). The rail's range itself is
  ACCEPTED (see in-between breakpoints). Rows: in-between 900.
  → **FIXED**: `SIDEBAR_WIDTH_ICON = '72px'`. Measured 72px at 900 (design `.side` 72px at 860). Compared with the 860px render of `tallypal-desktop.html`; the design rail also lists a tinted tile per tally and the app rail does not (ACCEPTED rail strategy, unchanged). `desktop/fp08-home-900.png`, `fp08-design-860.png`.
  **Corrected and built (2026-10-14):** that "ACCEPTED rail strategy" wording was wrong. The responsive table decides *when* the rail shows (md–lg), not what it holds, and the design rail at 860px (`design/tallypal-desktop.html`, `.snv` hides only its text) shows a 22px tinted glyph tile per tally under Home / Tallies / Stats. `AppSidebar.vue` now keeps the tally list in the rail: the `xs` glyph in a 44px tile (`p-[11px]`, centred), linking to the tally, `aria-current` → `--surface-2` as in the full sidebar, name and amount hidden, `aria-label` = the name, and the F-010 `title` (every currency) led by the name when the rail is collapsed (`useSidebar().state`). The rail content scrolls with no scrollbar when the list is long (design `.side{overflow-y:auto}`). At 860×800: Household, Fresh Start, City Break, Solo Savings, Ski Cabin, Weekend Trip, 50px pitch like the design's `.snv`; on Household's page its tile is `--surface-2`; hovering City Break gives "City Break · € 118.40 · $ 274.50"; clicking it opens `/tallies/…0005/transactions`. The expanded sidebar at 1440 is unchanged (title "€ 118.40 · $ 274.50", no name). Compared with `desktop/fp09-design-rail-860.png`: same order, tint, size and pitch. Still different, as before FP-08 and not part of it: the design's nav icons are 44px tiles, the app's are 32px shadcn icon buttons, and the app's tiles are 55px wide against 51px (rail padding 8px against 10px). `desktop/fp09-rail-860.png`, `fp09-rail-compare.png` (design left, app right), `fp09-rail-active-860.png`, `fp09-sidebar-1440.png`.

- **FP-09** `DATA` **Status: FIXED** (2026-10-14). Home By category truncated its categories exactly as Stats did before f50378a: `HomeCacheService::categoriesBreakdown()` asked the repository for its default 6 rows, so there was no "Other" row, anything past the sixth category was dropped, and every percentage was a share of the *top six's* total, not of the month. Found by reading the query after FP-04. Rows: HO-06 D/M.
  → **FIXED**: the repository returns every category (its `limit` parameter is removed, so no caller can truncate again). The row-building moved from `StatsService::categoryBars()` into `App\Services\CategoryBars::build()`, used by both Stats and `HomeCacheService::categoriesBreakdown()` (cached shape changed, `KEY_VERSION` v10 → v11): top five, one real Other row with the summed rest and its share of the true total, `bar_fill` relative to the largest named category (Other capped at 100), `is_other`. `HomeCategoriesBreakdown.vue` renders those rows like `StatsCategoryBreakdown.vue` (grey dot and bar for Other, `home.categories.other` en/it); the client-side `barFill()` and the `StatsCategory` type are gone (`CategoryBreakdown` carries `bar_fill` / `is_other`). Tests: `HomeControllerTest` "home By category folds every category beyond the top five into a real Other row and the rows add up to the month" (nine categories, Other = 32.5, the six rows sum to the month's expense 807.50), `HomeCacheServiceTest` (fold, shape), `CategoryBarsTest` (5 / 6 boundary, share of the true total, cap, zero rest), `TransactionStatsRepositoryTest` (all 8 categories returned). Demo, October: Rent € 950.00 55.9%, Groceries 255.35, Dining Out 126.70, Travel 120.00, Utilities 96.10, Other € 151.39 8.9%, which sums to € 1,699.54, the DB expense total from the data checks below, and equals the Stats card row for row. Compared with `design/desktop/home-1.png` (By category card: dot, name, bar, value and percent, top bar full; the design has no Other row, the owner asked for one). `desktop/fp09-home-1-1440.png`.

- **FP-10** `VISUAL` **Status: FIXED** (2026-10-14, minor). In the collapsed rail (641–860px) the nav icons were 32px shadcn icon buttons (`size-8`, `p-1.5`, 20px icon); the design's are tiles. Measured on `design/tallypal-desktop.html` at 860px: **51.3 × 41px, not 44** (the 44px figure is the tally tile `.snv`), `padding: 11px`, 19px icon, radius 14px (`--r-sm`), `.nv:hover` → `--surface`, `.nv.on` → `--surface-2` with an accent icon, rail inset 10px. Rows: in-between 900.
  → **FIXED**: the collapsed `sidebarMenuButtonVariants` now use `p-[11px]`, a 19px icon, full width, auto height and hidden label spans (`ui/sidebar/index.ts`); the rail's inset is 10px on the nav group, tally list and footer (`NavMain.vue`, `AppSidebar.vue`), and the tally tile radius is `--radius` (14px, was `rounded-sm` 12px). At 860×800 Home, Tallies, Stats and Theme all measure 51.33 × 41 at x = 10, and the tally tiles 51.33 × 44 at x = 10, the design's `.nv` / `.snv` values (this also closes the 55px against 51px width gap noted under FP-08). Active and hover states compared side by side. Still different, not part of FP-10: the app's Tallies icon is a tally-mark glyph against the design's table icon, and hovering a tile shows a tooltip the design does not have. Shots, saved in `.verify/desktop/` (not in `.verify/app/final/desktop/` like the other FP shots): app `.verify/desktop/fp10-rail-860.png`, `.verify/desktop/fp10-rail-hover-860.png`; design render of `design/tallypal-desktop.html` at 860px `.verify/desktop/fp10-design-rail-860.png`, `.verify/desktop/fp10-design-rail-hover-860.png`.

- **FP-11** `DATA` **Status: FIXED** (2026-10-14, found in the delta pass). The Stats "Daily average" delta pill shows rising spending as good news. `StatsKpiStack.vue:25-33` passes the change in the daily **spending** average to `TrendBadge` (line 71). `TrendBadge` (`ui/trend-badge/TrendBadge.vue:18-31`) makes `up` a green `--pos` pill with ↑ and `down` a red `--neg` one. Demo: € 121.40 a day, 27.4% more than Sep 1–14, shown as a green "↑ 27.4%". So spending more reads as a gain, the same misreading FP-01 removed from per-tally amounts. The Home hero uses the same pill correctly, because it tracks Net: a fall there is red. In the design (`design/desktop/stats-0.png`, `design/mobile/stats-0.png`) the delta is plain `--text-3` text with no tone ("−4.2% vs March"). The owner's F-075 decision ("Daily average with the delta pill … as Home") set the pill but not what its colour means. Present since F-075 was built (2026-10-14). It is already visible in the earlier final-pass shot `desktop/stats-owed-1440.png`, where it was not flagged. Rows: ST-03 D/M. **Owner call needed:** a neutral pill (as in the design), or reversed tones for spending deltas (rise `--neg`, fall `--pos`).
  → **FIXED** (owner decision: the Colour rule above, spending deltas are neutral). `TrendBadge` takes a required `tone`: `pos-neg` (the tinted pill, `--pos` up / `--neg` down) or `neutral` (the design's `.pill`: `--surface-2` fill, `--text-3` text, arrow and percentage kept, same size as the delta). The prop has no default, so a call site has to choose, and `BalanceHero`'s `delta` carries it too. Stats Daily average is `neutral`; the Home balance delta is `pos-neg` and keeps its tint (computed `oklch(0.72 0.19 22 / 0.16)` fill, `--neg` text for a fall). Measured on Stats at 1440: fill `oklch(0.255 0.022 265)` (`--surface-2`), text `oklch(0.6 0.015 265)` (`--text-3`). Compared with `design/desktop/stats-0.png` and `design/mobile/stats-0.png`: the design's delta is plain `--text-3` text; the owner's F-075 pill stays, in the design's neutral `.pill` colours. `desktop/fp11-stats-1440.png`, `fp11-home-1440.png`, `mobile/fp11-stats-375.png`. Rows: ST-03 D/M are ACCEPTED again.
  → **REVISED** (2026-10-14, owner): the neutral pill is gone too; the delta is drawn as the design draws it, plain `--text-3` text with no pill. `TrendBadge` `tone="neutral"` now renders arrow, percentage and caption as 12px / 400 `--text-3` with no fill, padding or radius, the same style as the sub-lines under the other KPIs (`StatsKpiStack.vue` is unchanged; `pos-neg` keeps the tinted pill). Measured on Stats (1440 and 375, dark): fill `rgba(0, 0, 0, 0)`, padding `0px`, radius `0px`, colour `oklch(0.6 0.015 265)`, 12px, weight 400, the same colour, size and weight as the "1 open balance" line beneath; the text reads "↑ 27.4% vs Sep 1–14". Compared with `design/desktop/stats-0.png` and `design/mobile/stats-0.png` ("−4.2% vs March" under the figure, same position and style): the one difference is the ↑/↓ arrow, which the owner asked to keep and the design does not draw. `desktop/fp11b-stats-1440.png`, `mobile/fp11b-stats-375.png`.

- **FP-12** `VISUAL` **Status: FIXED** (2026-10-14, seen as a delta-pass minor note). By category colours came from a hash of the category name (`categoryColorFromString`) over c1–c6, so rows shown together could share a hue: in the demo month Rent, Groceries and Dining Out were one colour and Travel and Utilities another. The design gives each row its own colour (Housing blue, Groceries amber, Travel cyan, Restaurants magenta, Subscriptions green, Other `--text-3`). Rows: HO-06 D/M, ST-04 D/M.
  → **FIXED**: `breakdownRowColors()` (`lib/category.ts`) colours the rows of a card by rank, used by both `HomeCategoriesBreakdown.vue` and `StatsCategoryBreakdown.vue`: the top five take `--c1`, `--c4`, `--c2`, `--c6`, `--c3` (the design's order) and "Other" is `--text-3` for the dot and `--surface-3` for the bar, as in the design. **Hue tokens: six exist** (`--c1`…`--c6`, DESIGN_TOKENS §1.3; `--chart-1..5` are aliases of c1..c5). Five are used in a card; `--c5` (red) is left out because the design never gives a category that hue and it reads as `--neg`. Documented in DESIGN_TOKENS §1.3. Measured (dark) on Home and Stats, same six rows on both: Rent `oklch(0.7 0.17 262)`, Groceries `0.85 0.16 95`, Dining Out `0.8 0.16 195`, Travel `0.72 0.17 320`, Utilities `0.83 0.17 158`, Other dot `0.6 0.015 265` and bar `0.31 0.024 265`: six distinct values. Compared with `design/desktop/stats-1.png`, `design/mobile/stats-1.png`, `design/mobile/home-2.png` (and `desktop/home-1.png`): same hue order and the same grey Other row. Not part of FP-12: transaction rows and glyphs (Recent, All activity, the detail list) still tint by name hash, so a category can wear a different hue there than in its By category row. `desktop/fp12-home-1440.png`, `fp12-stats-1440.png`, `mobile/fp12-home-375.png`, `fp12-stats-375.png`.

- **FP-13** `VISUAL` **Status: FIXED** (2026-10-14, seen as a delta-pass minor note). The income glyph (↓) took its tint from the same name hash as every other row, so Salary's arrow rendered red (the hash of "Salary" is `--c5`) next to a green `+` amount; an income row with another category name got whatever hue that name hashed to. The design tints the income arrow green (`Salary — April`). Rows: HO-05 D/M, ST-05 D, TD-04 D/M.
  → **FIXED**: `transactionGlyphColor(type, categoryName)` (`lib/category.ts`) returns `--pos` for income and the name-hash hue for an expense; Home Recent, Stats All activity and the tally detail list all call it, so the three sites cannot disagree. `CategoryGlyph`, `DataTableItem` and `TransactionRow` take a `GlyphColor` (`CategoryColor | 'pos'`). **Chosen: `--pos`, not neutral.** Money coming in is unambiguously good for the user, the design and the `+` amount beside it are green, and a neutral arrow next to a green amount would be a new departure from the design. The Colour rule now names income ("`--pos` also marks income: money coming to you"), so this follows the rule; it was first built as a reading of it, before the rule listed income. Measured on Stats (1440, 375) and Household's list (1440, 375): the ↓ is `oklch(0.83 0.17 158)` (`--pos`) on `oklch(0.83 0.17 158 / 0.2)`, three Salary rows on the detail. Compared with `design/mobile/home-2.png` and `design/desktop/stats-0.png` (Salary — April: green ↓ on a green tinted square). `desktop/fp13-stats-1440.png`, `fp13-detail-1440.png`, `mobile/fp13-stats-375.png`, `fp13-detail-375.png`.

- **FP-14** `DATA` **Status: FIXED** (2026-10-14, seen as a delta-pass minor note). A tally with no spending read differently on each surface: Fresh Start had no figure in the sidebar or on its Tallies row but "€ 0.00" in `--text` on its Home card, and Weekend Trip (nothing spent in October) showed "0.00" in `--text` in the sidebar. Rows: SH-01 D, HO-04 D/M, TA-03 D/M.
  → **FIXED**: no spending is one zero line in the user's primary currency, in `--text-3`, on every surface. `zeroSpending()` and `spendingToneClass()` (`lib/spending.ts`) are the only place that decides it; the sidebar (`AppSidebar.vue`, number only, as before), the Home card (`HomeYourTallies.vue` → `TallyRailCard`) and the Tallies row (`tallies/Index.vue` → `TallyListRow`) all use them, so an empty tally can no longer be handled per surface. The currency comes from a new shared Inertia once prop `primary_currency` (`{symbol, code, decimals}`, `HomeService::primaryCurrency()`, keyed to the sidebar version so a default-currency change refreshes it), read through `usePrimaryCurrency()`; Home's two currency props are gone. Tests: `SharedSidebarTalliesTest` (guest, default currency, most-used fallback, once key), `HomeServiceTest` (no currency id leaks). Measured at 1440 and 375: Fresh Start "0.00" (sidebar), "€ 0.00" (Home card, Tallies row) and Weekend Trip "0.00" / "$ 0.00" (sidebar, Home card) are all `oklch(0.6 0.015 265)` (`--text-3`); every non-zero figure stays `oklch(0.975 0.004 265)` (`--text`), and the sidebar tooltip of Fresh Start now reads "€ 0.00". The design has no zero-spend case, so the screens it touches were compared: `design/desktop/home-0.png` (sidebar amounts), `design/mobile/home-1.png` (rail card), `design/desktop/tallies-0.png` and `design/mobile/tallies-0.png` (rows). `desktop/fp14-home-1440.png`, `fp14-tallies-1440.png`, `mobile/fp14-home-375.png`, `fp14-tallies-375.png`.

- **FP-15** `DATA` **Status: FIXED** (2026-10-14, seen as a delta-pass minor note). The Home greeting was a fixed "Good morning," (`home.greeting`), so at 18:03 it still said "Good morning". Rows: HO-01 D/M.
  → **FIXED**: the greeting follows the viewer's local time, computed in the browser: `HomeHeader.vue` reads `new Date().getHours()` in `onMounted` and `greetingPeriod()` (`pages/home/utils.ts`) maps it to morning (05–11), afternoon (12–17) or evening (18–04). The server knows no time zone, so nothing time-based is rendered on it; until mount the greeting is the neutral "Hello," / "Ciao,", which is also what a server render would show, so there is no hydration mismatch (in client rendering the swap lands before the first paint). `home.greeting` is now a group in `en.ts` / `it.ts`: `default`, `morning`, `afternoon`, `evening` ("Good morning," / "Good afternoon," / "Good evening,"; "Buongiorno," / "Buon pomeriggio," / "Buonasera,"). Checked with the browser clock set to 09:05, 15:05 and 21:05 at 1440 and 375: "Good morning, Sam.", "Good afternoon, Sam.", "Good evening, Sam." (and "Buon pomeriggio, Sam." in Italian at 15:05); the function returns evening for 0 and 4, morning for 5 and 11, afternoon for 12 and 17, evening for 18 and 23. The longest phrase fits on one line at 375. Compared with `design/desktop/home-0.png` and `design/mobile/home-0.png` (heading, sub-line, button position): same layout; the design's wording is "Hey, Marta" and the owner asked for time-of-day, so the words differ by decision. `desktop/fp15-home-{morning,afternoon,evening}-1440.png`, `mobile/fp15-home-{morning,afternoon,evening}-375.png`.

- **FP-16** `VISUAL` **Status: FIXED** (2026-10-14, seen as a delta-pass minor note). Page titles repeated the app name: "Home — TallyPal - TallyPal", "Spending — TallyPal - TallyPal". `AppHead` built "Title — Site" and the Inertia `title` callback (`app.ts`) then appended " - TallyPal" again; the welcome page, which passes no title, read "TallyPal - TallyPal". Rows: SH-01 and every screen.
  → **FIXED**: the Inertia `title` callback (`app.ts`, and the same in `ssr.ts`) is the only place that adds the app name, as `Title — App`; `AppHead` hands `<Head>` the bare page title and keeps "Title — Site" only for `og:title` / `twitter:title`. The other `<Head :title>` pages already went through the callback, so every title now has one app name and the same " — " separator (it was " - " there). Checked with `document.title`, at 1440 and 375 and after client-side navigation: "Home — TallyPal", "Tallies — TallyPal", "Spending — TallyPal", "You — TallyPal", "Household - Transactions — TallyPal", "Log in — TallyPal", and the title-less welcome page "TallyPal"; `og:title` and `twitter:title` read "Home — TallyPal". A viewport shot cannot show the tab title, so the evidence is those values plus the Home screens, which are unchanged: `desktop/fp16-home-1440.png`, `mobile/fp16-home-375.png`. The name is the configured `APP_NAME`, which was `TallyPal` in the local `.env`; the sidebar brand and the design write `Tallypal`.
  → **CASING** (2026-10-14, owner: the brand is "Tallypal", the F-006 wordmark): `.env.example` already defines `APP_NAME=Tallypal` (CI copies it), so the code needs no change; only an environment whose `.env` still says `TallyPal` shows the old casing, in the page title (via `VITE_APP_NAME`, compiled into the JS at build time), `og:title`, `og:site_name`, the JSON-LD names, the mail from-name, the mail layout and the invitation mails. Checked with `APP_NAME=Tallypal` (process override, `.env` untouched) and a build with `VITE_APP_NAME=Tallypal`: `document.title` "Home — Tallypal" and "Spending — Tallypal"; server-rendered title, `og:site_name` and both JSON-LD names "Tallypal"; mail from-name "Info | Tallypal"; invitation subject "You've been invited to join Tallypal and collaborate on Household" and body/footer "Tallypal". Changing the name also changes the derived session cookie (`Str::snake`: `tally_pal_session` to `tallypal_session`), so every signed-in user is signed out once; the cache and Redis prefixes use `Str::slug` and stay `tallypal`.

**Minor notes (seen side by side, no class change):**
- The Home header primary button is `size="touch"` (48px tall); design `.btn` is ≈40px.
- The Quick actions Add tile has no "Expense or income" sub; the 4th tile reads "New" with no sub.
- The sidebar "Tallies" `+` is `--text-3`; the design's is an accent link.
- Phone page padding is 20px; design `.main` is 16px at ≤640.
- Home Recent and detail dates are ISO ("2026-10-14"); the design shows "Today, 11:24" / "Apr 04". Stats activity already
  shows "Oct 14".
- Mobile detail actions run `+` · Share · gear; the design runs Share · `+`.
- The You Sign out row label is `--neg` (`SettingRow tone="neg"`); the design colours only the button.
- The sidebar user block isn't highlighted on You.
- Home rail cards differ in height when a tally shows two currencies.

### Delta pass (2026-10-14, at `63d3398`)

**This pass only verified; no code changed.** Built assets were current: no `resources/` file was newer than
`public/build/manifest.json`. DemoSeeder was re-run, then I logged in with the Resume-notes recipe as `demo@tallypal.test`, in
the dark theme. Every shot is viewport-sized, saved in `.verify/app/final/delta/`, and compared side by side with its
`.verify/design/` reference. The 860px design reference is the earlier render `.verify/desktop/fp10-design-rail-860.png`.
Scope: the rows under FP-01..FP-10, the Owed to you KPI, the FP-01 surfaces, and the By category sums. No data was created,
edited or deleted; the overlays were opened and closed without submitting.

**Harness (Playwright MCP):** in this session real keyboard and mouse input did not reach the page. A capture-phase `keydown`
listener on `window` saw nothing after `keyboard.press('n')`, and `locator.click()` / `hover()` changed nothing. The app
handlers do work. A dispatched `KeyboardEvent('keydown', {key: 'n'})` opened Add transaction, and `element.click()` opened
New tally once the page had hydrated (wait about 1.5s after `networkidle`). So the overlays were opened that way, and the
rail hover state (`--surface`) was not re-shot; FP-10's hover shot stands.

**Re-checked rows**

| Row | Vp | Class | Was | Note | Shot (`delta/`) | Design ref |
|---|---|---|---|---|---|---|
| SH-01 | D | PASS | PASS | FP-01: each tally shows the month's spending as a plain `--text` number. "· $" appears only on City Break, the only tally with USD spent this month. The `title` lists every currency. FP-03: ✈ ☕ ❄ are tinted text | `home-0-1440.png`, `tallies-0-1440.png` | `desktop/home-0.png` |
| HO-04 | D | ACCEPTED | PASS | FP-01 caption "spent in Oct" under each amount (owner decision, not in the design). Footer meta cut (DO-03). FP-03 glyphs tinted; status pills (F-033) | `home-0-1440.png`, `home-1-1440.png` | `desktop/home-0.png` |
| HO-04 | M | ACCEPTED | PASS | Same caption. The visible card surface does not stretch, so single-currency cards end about 40px above City Break's (minor note, unchanged since `mobile/fp01b-home-375.png`) | `home-1-375.png` | `mobile/home-1.png` |
| HO-05 | D | PASS | PASS | FP-02: Recent 744.44px, By category 364.23px, 16px gap (8/4) | `home-1-1440.png` | `desktop/home-1.png` |
| HO-05 | M | PASS | PASS | FP-02: stacked, 12px from Recent to By category, 16px from the rail to Recent | `home-1-375.png`, `home-2-375.png` | `mobile/home-1.png`, `home-2.png` |
| HO-06 | D | ACCEPTED | PASS | FP-04 / FP-09: Rent's bar is full, then the top five plus a real Other row. Home's design has no Other row; the owner asked for it. The rows add up (sums below) | `home-1-1440.png` | `desktop/home-1.png` |
| HO-06 | M | ACCEPTED | PASS | As D, with the "Other currencies: $ 274.50" foot line | `home-2-375.png`, `home-3-375.png` | `mobile/home-2.png` |
| TA-03 | D | ACCEPTED | PASS | FP-01: all-time spending, neutral, captioned "total spent" (owner decision). The extra line makes rows 76–136px tall against the design's 78px. FP-03 glyphs tinted | `tallies-0-1440.png` | `desktop/tallies-0.png` |
| TA-03 | M | ACCEPTED | PASS | As D. Next to the wider amount column the split label wraps ("60 / 40 / split"), the same as `mobile/fp03v-tallies-375.png` | `tallies-0-375.png` | `mobile/tallies-0.png` |
| TD-02 | D | ACCEPTED | ACCEPTED | Household and Weekend Trip: "Total spent" + Per person. City Break: "Net Balance" + Total income / Total spent (F-058). No Days (DO-03) | `detail-{household,weekendtrip,citybreak}-1440.png` | `desktop/detail-0.png` |
| TD-02 | M | ACCEPTED | PASS | As D. The design's phone hero shows DAYS (cut, DO-03) | `detail-{household,weekendtrip,citybreak}-0-375.png` | `mobile/detail-0.png` |
| ST-01 | D | PASS | PASS | FP-05: track 204.69px, 13px text, `8px 16px` padding | `stats-0-1440.png` | `desktop/stats-0.png` |
| ST-03 | D | STILL OPEN | ACCEPTED | Owed to you renders as decided: € 40.00 in `--pos`, "1 open balance", "Also $ 105.85". **FP-11:** the Daily average pill is a green ↑ for spending that rose 27.4% | `stats-0-1440.png` | `desktop/stats-0.png` |
| ST-03 | M | STILL OPEN | ACCEPTED | As D | `stats-0-375.png` | `mobile/stats-0.png` |
| ST-04 | D | PASS | PASS | FP-04: Rent 100%; Other set by its amount and capped at 100%. Same six rows as Home | `stats-0-1440.png`, `stats-1-1440.png` | `desktop/stats-0.png`, `stats-1.png` |
| ST-04 | M | PASS | PASS | As D | `stats-1-375.png` | `mobile/stats-1.png` |
| YO-02 | D | PASS | PASS | FP-06: Edit has a transparent background (`rgba(0,0,0,0)`) and a `--line` border | `you-0-1440.png` | `desktop/profile-0.png` |
| YO-02 | M | PASS | PASS | As D | `you-0-375.png` | `mobile/profile-0.png` |
| YO-04 | D | ACCEPTED | ACCEPTED | FP-06: Sign out and Delete account transparent. No Export (DO-06); Delete account added (D3) | `you-0-1440.png` | `desktop/profile-0.png` |
| YO-04 | M | ACCEPTED | PASS | As D. The design's phone Data card has "Export all data · CSV" (cut, DO-06) | `you-0-375.png` | `mobile/profile-0.png` |
| MO-01 | D | ACCEPTED | ACCEPTED | FP-07: at scroll top the footer ends at 875.33 against the dialog's 876, which leaves only the 1px border. Scrolled to the end, it sits on the 24px body padding and nothing shows through | `modal-addTx-top-1440.png`, `modal-addTx-bottom-1440.png` | `desktop/modal-addTx-0.png` |
| MO-02 | D | ACCEPTED | ACCEPTED | FP-07: no seam. FP-03: the chips ⌂ ✈ ◐ ◈ ♪ ☕ render as text. No Type, Members or Invite (B-12) | `modal-newTally-1440.png` | `desktop/modal-newTally-0.png` |
| MO-02 | M | ACCEPTED | PASS | FP-03 chips as D. The design sheet's Type, Members & split and Invite someone are cut (B-12) | `sheet-newTally-375.png` | `mobile/sheet-newTally-0.png` |

| In-between check | Class | Was | Note | Shot (`delta/`) |
|---|---|---|---|---|
| Shell 900×800 | ACCEPTED | ACCEPTED | FP-08 / FP-10: the rail panel is 71.33px wide. The Home, Tallies, Stats and Theme tiles measure 51.33 × 41 at x = 10, with 11px padding, a 19px icon and a 14px radius. The tally tiles measure 51.33 × 44 | `home-rail-900.png` |
| Rail 860×800 | — | — | Same values as at 900; same order, tint and pitch as the design render. On Household's page, its tile and the Tallies tile are `--surface-2`. City Break's `title` is "City Break · € 118.40 · $ 274.50" | `home-rail-860.png`, `rail-active-hover-860.png` |
| Home 1024×768 | PASS | PASS | FP-02: Recent and By category are stacked, both 708.67px wide. No truncation, no horizontal overflow | `home-0-1024.png`, `home-1-1024.png`, `home-2-1024.png` |

**FP-01 surfaces:** the same tally shows the same spending figure everywhere. Where a figure differs, a label says why. The
sidebar is checked at 1440 only (it is hidden below md); the other surfaces at 375 and 1440 read the same.

| Tally | Sidebar (month) | Home card | Tallies row | Detail hero |
|---|---|---|---|---|
| City Break (private, EUR + USD) | 118.40 · $ (`title` € 118.40 · $ 274.50) | € 118.40, $ 274.50, "spent in Oct" | € 118.40, $ 274.50, "total spent" | "Net Balance" € −118.40 / $ −274.50 (F-058, labelled); Total spent € 118.40 / $ 274.50 |
| Household (shared, EUR + USD) | 1,368.15 (`title` € 1,368.15 · $ 0.00; no "· $" because no USD was spent in October) | € 1,368.15, "spent in Oct" | € 9,163.40, $ 320.00, "total spent" | "Total spent" € 9,163.40 / $ 320.00 |
| Weekend Trip (shared, USD) | 0.00 (`title` $ 0.00) | $ 0.00, "spent in Oct" | $ 875.70, "total spent" | "Total spent" $ 875.70 |

Month figures (sidebar, Home card) and all-time figures (Tallies row, detail "Total spent") differ only where the period
label differs. City Break's month and all-time figures are equal because all four of its transactions are in October. Its net
differs and is labelled "Net Balance".

**By category sums (Home and Stats, 1440 and 375).** The DB was read directly: expenses with a `transaction_date` from
2026-10-01 to 2026-10-31 in the demo user's tallies.
- EUR rows in the DB: Rent 950.00, Groceries 255.35, Dining Out 126.70, Travel 120.00, Utilities 96.10, Transportation 86.40,
  Entertainment 52.00, Subscriptions 12.99.
- USD rows in the DB: Travel 210.00, Dining Out 64.50.
- Home and Stats show the same six rows: the top five above, then Other € 151.39 (86.40 + 52.00 + 12.99) at 8.9%.
- The six rows add up to € 1,699.54 (950.00 + 255.35 + 126.70 + 120.00 + 96.10 + 151.39). That equals Home's Out and Stats'
  "Out this month".
- USD has no rows: cross-tally aggregates use the primary currency (O-04). Both cards end with the line "Other currencies:
  $ 274.50", which is 210.00 + 64.50, so every currency is accounted for. Nothing is added across currencies.

**Minor notes (delta, no class change):** all four were fixed afterwards: category colours (FP-12), the income arrow (FP-13), the empty-tally figure (FP-14), the greeting (FP-15) and the titles (FP-16).
- Category colours come from a hash of the category name over c1–c6 (`categoryColorFromString`). In the demo month, Rent,
  Groceries and Dining Out share one colour, and Travel and Utilities share another, so adjacent By category rows look the same.
  The design gives each row its own colour. Income glyphs follow the same hash: Salary's ↓ is red in Stats activity and the
  detail list, where the design shows it green. Unchanged since the first pass.
- An empty tally (Fresh Start) shows "€ 0.00 spent in Oct" on its Home card but no figure in the sidebar or its Tallies row.
  Weekend Trip, which spent nothing in October, shows "0.00" in the sidebar. The figure is missing, not contradicted.
- The Home greeting is a fixed "Good morning," (`home.greeting`), so at 18:03 it still says "Good morning"; the design says
  "Hey, Marta". This is copy, so the comparison ignores it.
- Page titles repeat the app name: "Home — TallyPal - TallyPal", "Spending — TallyPal - TallyPal".

### Delta check (2026-10-14, at `be67faa`)

**Verification only; no code changed.** Built assets were current (no `resources/` file newer than
`public/build/manifest.json`). DemoSeeder re-run, then logged in with the Resume-notes recipe as `demo@tallypal.test`, dark
theme. Shots are viewport-sized, in `.verify/app/final/delta2/`, each compared side by side with its `.verify/design/`
reference. Scope: ST-03 D/M and every surface FP-11..FP-16 touched. No data was created, edited or deleted. No console errors.

**Harness:** the Playwright MCP browser context still had the clock frozen at 15:05 from the FP-15 checks (`new Date()` in the
page read 15:05 while the host read 21:12). The main shots carry that clock, so they greet "Good afternoon". The greeting was
re-checked in a fresh context on the real clock: "Good evening, Sam." at 21:18, one line at 375 (`home-realclock-{1440,375}.png`).

| Row | Vp | Class | Was | Note | Shot (`delta2/`) | Design ref |
|---|---|---|---|---|---|---|
| ST-03 | D | ACCEPTED | ACCEPTED | FP-11: Daily average delta "↑ 27.4% vs Sep 1–14" is plain text: fill `rgba(0,0,0,0)`, padding 0, radius 0, `oklch(0.6 0.015 265)` (`--text-3`), 12px / 400, the same as "1 open balance". Only the arrow differs from the design (owner). Owed to you € 40.00 `--pos`, "1 open balance", "Also $ 105.85" | `stats-0-1440.png` | `desktop/stats-0.png` |
| ST-03 | M | ACCEPTED | ACCEPTED | As D, same computed values | `stats-0-375.png` | `mobile/stats-0.png` |
| HO-02 | D / M | PASS | PASS | FP-11: the Home balance delta keeps the `pos-neg` pill; net fell, so ↓ 27.8% is `--neg` on `oklch(0.72 0.19 22 / 0.16)` | `home-0-{1440,375}.png` | `desktop/home-0.png`, `mobile/home-0.png` |
| HO-06 / ST-04 | D / M | ACCEPTED / PASS | same | FP-12: dots Rent `0.7 0.17 262`, Groceries `0.85 0.16 95`, Dining Out `0.8 0.16 195`, Travel `0.72 0.17 320`, Utilities `0.83 0.17 158`, Other `0.6 0.015 265`: six distinct, design order, grey Other. The previous delta shot had Rent/Groceries/Dining Out in one hue | `home-1-1440.png`, `home-3-375.png`, `stats-0-1440.png`, `stats-1-375.png` | `desktop/home-1.png`, `stats-1.png`, `mobile/home-2.png`, `stats-1.png` |
| ST-05 / TD-05 | D / M | ACCEPTED | ACCEPTED | FP-13: every income ↓ is `oklch(0.83 0.17 158)` (`--pos`) on a green tile: Salary on Stats activity (1440, 375) and Household's list (3 at 1440, 1 in view at 375). Home Recent has no income row this month | `stats-1-1440.png`, `stats-2-375.png`, `detail-household-1-1440.png` | `desktop/stats-1.png`, `mobile/home-2.png` |
| SH-01 / HO-04 / TA-03 | D / M | PASS / ACCEPTED / ACCEPTED | same | FP-14: Fresh Start "0.00" (sidebar), "€ 0.00" (Home card, Tallies row) and Weekend Trip "0.00" / "$ 0.00" (sidebar, Home card) all `--text-3`; non-zero figures `--text`. The previous delta shot had no Fresh Start figure in the sidebar and Weekend Trip in `--text` | `home-0-1440.png`, `home-1-{1440,375}.png`, `tallies-0-{1440,375}.png` | `desktop/home-0.png`, `tallies-0.png`, `mobile/home-1.png`, `tallies-0.png` |
| HO-01 | D / M | ACCEPTED | ACCEPTED | FP-15: greeting follows the browser clock (see Harness); layout as the design | `home-0-{1440,375}.png`, `home-realclock-{1440,375}.png` | `desktop/home-0.png`, `mobile/home-0.png` |
| every screen | D / M | — | — | FP-16: `document.title` "Home — TallyPal", "Tallies — TallyPal", "Spending — TallyPal", "Household - Transactions — TallyPal", "You — TallyPal": one app name at both widths. "TallyPal" is the local `.env` casing (FP-16 CASING) | — | — |

**Re-classified:** ST-03 D/M stay **ACCEPTED**. No other row changes class; Totals unchanged (PASS 38 · REGRESSED 0 · STILL OPEN
0 · ACCEPTED 39). Not part of these fixes and unchanged since the delta pass: "Also $ 105.85" is set in mono while "1 open
balance" is sans; the Home By category header adds "October 2026" before Details.

### Batch 1 — Shell + Home

| Row | Vp | Class | Note | App shot |
|---|---|---|---|---|
| SH-01 | D | PASS | FP-03 (✈ ☕ ❄ in the list) and FP-01 (sign) FIXED 2026-10-14; F-001..F-010 render as fixed | `desktop/home-0.png` |
| SH-02 | D | PASS | hover `--surface`, active `--surface-2`, accent icon | `desktop/hover-sidebar-nav.png` |
| SH-03 | M | PASS | tab bar, FAB, active tint | `mobile/home-0.png` |
| SH-04 | D | PASS | `2`→/tallies, `3`→/stats, `1`→/home, `n` opens Add tx, Esc closes | `desktop/kbd-shortcut-n.png` |
| HO-01 | D | ACCEPTED | no bell (DO-01); disabled search + "Search coming soon" tooltip (B-02) | `desktop/home-0.png`, `home-search-tooltip.png` |
| HO-01 | M | ACCEPTED | no bell (DO-01); round `+` | `mobile/home-0.png` |
| HO-02 | D | PASS | hero, delta pill, sparkline, In/Out; "Other currencies" line (O-04) | `desktop/home-0.png` |
| HO-02 | M | PASS | as D, In/Out below | `mobile/home-0.png` |
| HO-03 | D | ACCEPTED | Add / Split / Settle / New (D5, DO-02 Import cut) | `desktop/home-0.png` |
| HO-03 | M | ACCEPTED | same set, no subs as the design | `mobile/home-0.png` |
| HO-04 | D | ACCEPTED | FP-03 FIXED; grid, avatars, status pill (F-033) match; footer meta cut (DO-03); "spent in Oct" caption (FP-01). Delta: was PASS | `desktop/home-0.png`, `home-1.png`, `delta/home-0-1440.png` |
| HO-04 | M | ACCEPTED | FP-03 FIXED; 156px snap rail, no footer; "spent in Oct" caption (FP-01). Delta: was PASS | `mobile/home-1.png`, `delta/home-1-375.png` |
| HO-05 | D | PASS | FP-02 FIXED (8/4, 16px gap); card, table, accent pill on shared (F-037) match | `desktop/home-1.png` |
| HO-05 | M | PASS | FP-02 FIXED (12px gaps); stacked rows + inline pill match | `mobile/home-1.png`, `home-2.png` |
| HO-06 | D | ACCEPTED | FP-02, FP-04, FP-09 FIXED (top five + real Other, same rows as Stats; Other is an owner addition, not in Home's design); "Other currencies" foot line present. Delta: was PASS | `desktop/home-1.png`, `fp09-home-1-1440.png`, `delta/home-1-1440.png` |
| HO-06 | M | ACCEPTED | FP-04, FP-09 FIXED; Other row as D. Delta: was PASS | `mobile/home-2.png`, `fp09-home-categories-375.png`, `delta/home-3-375.png` |
| HO-07 | D | PASS | card and row hover `--surface-2` (the design grid also leaves the column gaps) | `desktop/hover-tally-card.png`, `hover-tx-row.png` |
| HO-08 | D | PASS | light palette | `desktop/home-light-0.png` |
| HO-08 | M | PASS | light palette | `mobile/home-light-0.png` |
| HO-09 | D / M | PASS | no "Last 12 weeks" / "Shared tally" cards | `desktop/home-1.png`, `mobile/home-2.png` |

### Batch 2 — Tallies list

| Row | Vp | Class | Note | App shot |
|---|---|---|---|---|
| TA-01 | D | ACCEPTED | "6 ledgers · 3 shared", no "€ tracked" (F-043, O-04) | `desktop/tallies-0.png` |
| TA-01 | M | ACCEPTED | as D, round `+` | `mobile/tallies-0.png` |
| TA-02 | D | ACCEPTED | All / Shared / Private, no Archived (DO-04); Sort: Recent | `desktop/tallies-0.png` |
| TA-02 | M | ACCEPTED | full-width segmented + Sort | `mobile/tallies-0.png` |
| TA-03 | D | ACCEPTED | FP-03 FIXED; pill, tx count, per-currency amounts, status + "· $" match; "total spent" caption (FP-01) adds a line. Delta: was PASS | `desktop/tallies-0.png`, `tally-glyphs-zoom.png`, `delta/tallies-0-1440.png` |
| TA-03 | M | ACCEPTED | FP-03 FIXED; short status form (F-050 decision); "total spent" caption (FP-01). Delta: was PASS | `mobile/tallies-0.png`, `delta/tallies-0-375.png` |
| TA-04 | D | ACCEPTED | no archived row (DO-04) | `desktop/tallies-0.png` |
| TA-04 | M | ACCEPTED | no archived row (DO-04) | `mobile/tallies-0.png` |
| TA-05 | D | PASS | row hover `--surface-2` | `desktop/tallies-hover.png` |

### Batch 3 — Tally detail

| Row | Vp | Class | Note | App shot |
|---|---|---|---|---|
| TD-00 | D | PASS | 5/12 · 7/12 columns | `desktop/detail-0.png` |
| TD-01 | D | ACCEPTED | gear kept (F-053); no date range (DO-03); 48px tinted glyph | `desktop/detail-0.png` |
| TD-01 | M | ACCEPTED | gear kept (F-055); actions on their own row | `mobile/detail-0.png` |
| TD-02 | D | ACCEPTED | no Days (DO-03); one block per currency (F-057/F-060) | `desktop/detail-0.png` |
| TD-02 | M | ACCEPTED | per-currency hero; no Days (DO-03); private hero is Net Balance (F-058). Delta: was PASS | `mobile/detail-0.png`, `delta/detail-{citybreak,household,weekendtrip}-0-375.png` |
| TD-03 | D | ACCEPTED | no Remind (DO-05); second line "Noah Whitfield is settled" (F-061) | `desktop/detail-skicabin.png` |
| TD-03 | M | ACCEPTED | as D | `mobile/detail-skicabin.png` |
| TD-04 | D | PASS | Split so far, EUR + USD blocks | `desktop/detail-1.png` |
| TD-04 | M | PASS | as D | `mobile/detail-1.png` |
| TD-05 | D | ACCEPTED | no Export CSV (DO-06); Paid by; B-08 appends (31 → 64 rows on scroll) | `desktop/detail-0.png`, `detail-1.png` |
| TD-05 | M | ACCEPTED | no Export CSV (DO-06) | `mobile/detail-1.png`, `detail-2.png` |
| TD-06 | D | PASS | light palette | `desktop/detail-light-0.png` |

### Batch 4 — Stats

| Row | Vp | Class | Note | App shot |
|---|---|---|---|---|
| ST-01 | D | PASS | FP-05 FIXED; sub "October 2026 · all tallies", Filter beside | `desktop/stats-0.png` |
| ST-01 | M | PASS | compact segmented top-right, Filter hidden | `mobile/stats-0.png` |
| ST-02 | D | PASS | KPI line, "Other currencies", D2 axis, mono labels | `desktop/stats-0.png` |
| ST-02 | M | PASS | 160px chart | `mobile/stats-0.png` |
| ST-03 | D | ACCEPTED | FP-11 FIXED (Daily average is plain `--text-3` text, arrow, % and caption, no pill); Owed to you adds "Also $ 105.85" (F-075 reversed in part). Delta: STILL OPEN, ACCEPTED again | `desktop/stats-0.png`, `delta/stats-0-1440.png`, `desktop/fp11-stats-1440.png` |
| ST-03 | M | ACCEPTED | as D | `mobile/stats-0.png`, `delta/stats-0-375.png`, `mobile/fp11-stats-375.png` |
| ST-04 | D | PASS | FP-04 FIXED; top 5 + Other, foot line | `desktop/stats-0.png`, `stats-1.png` |
| ST-04 | M | PASS | FP-04 FIXED | `mobile/stats-1.png` |
| ST-05 | D | ACCEPTED | no Export CSV (DO-06); latest 20 (B-09); no time (F-077) | `desktop/stats-1.png` |
| ST-05 | M | ACCEPTED | as D | `mobile/stats-2.png` |
| ST-06 | D | PASS | bar hover accent-dim; Week/Month/Year change data and URL | `desktop/stats-bar-hover.png` |

### Batch 5 — You

| Row | Vp | Class | Note | App shot |
|---|---|---|---|---|
| YO-01 | D | PASS | 26px h1 + sub | `desktop/profile-0.png` |
| YO-01 | M | PASS | 24px h1, You tab active | `mobile/profile-0.png` |
| YO-02 | D | PASS | FP-06 FIXED (Edit); `Avatar xl` | `desktop/profile-0.png` |
| YO-02 | M | PASS | FP-06 FIXED | `mobile/profile-0.png` |
| YO-03 | D | ACCEPTED | Theme `PillSelect` (D4); Default currency; no Balance reminders (DO-05) | `desktop/profile-0.png` |
| YO-03 | M | ACCEPTED | as D | `mobile/profile-0.png` |
| YO-04 | D | ACCEPTED | FP-06 FIXED; no Export (DO-06); Delete account added (D3) | `desktop/profile-0.png` |
| YO-04 | M | ACCEPTED | FP-06 FIXED; no Export (DO-06); Delete account added (D3). Delta: was PASS | `mobile/profile-0.png`, `delta/you-0-375.png` |

### Batch 6 — Modals / sheets

| Row | Vp | Class | Note | App shot |
|---|---|---|---|---|
| MO-01 | D | ACCEPTED | footer meets the dialog edge (FP-07); no Transfer (DO-07); Description kept (F-092); currency pill; Cancel | `desktop/kbd-shortcut-n.png`, `modal-addTx-footer-zoom.png` |
| MO-01 | M | ACCEPTED | grabber, 1-col Tally/Date, no Cancel, Description | `mobile/sheet-addTx-0.png`, `-1.png` |
| MO-02 | D | ACCEPTED | FP-07 FIXED (blur re-checked, `md:-bottom-6`); FP-03 (chips) FIXED; no Type/Members/Invite (B-12) | `desktop/modal-newTally-0.png`, `-zoom.png` |
| MO-02 | M | ACCEPTED | FP-03 (chips) FIXED; no Type/Members/Invite (B-12). Delta: was PASS | `mobile/sheet-newTally-0.png`, `delta/sheet-newTally-375.png` |
| MO-03 | D | ACCEPTED | no invite link, roles, toggles (DO-08/09/10); split editor 40/30/30 | `desktop/modal-shareTally-0.png` |
| MO-03 | M | ACCEPTED | as D, Done only | `mobile/sheet-shareTally-0.png`, `-1.png` |
| MO-04 | D | PASS | Tab stays inside 40/40, scrim click and Esc close | `desktop/modal-focus-trap.png` |

### Batch 7 — States + base components

| Row | Vp | Class | Note | App shot |
|---|---|---|---|---|
| ES-01 | D / M | PASS | `EmptyState` + Create new tally | `desktop/home-empty.png`, `mobile/home-empty.png` |
| ES-02 | D / M | PASS | `EmptyState`, "0 ledgers · 0 shared" | `desktop/tallies-empty.png`, `mobile/tallies-empty.png` |
| ES-03 | D / M | PASS | `EmptyState` + Add transaction | `desktop/stats-empty.png`, `mobile/stats-empty.png` |
| ES-04 | D / M | PASS | Fresh Start: Transactions `EmptyState` + Add expense, no balance card | `desktop/tally-empty-0.png`, `mobile/tally-empty-0.png` |
| ES-05 | D / M | PASS | "No tallies yet" + New tally | `desktop/empty-addtx.png`, `mobile/empty-addtx.png` |
| LE-01 | D / M | PASS | Add-tx skeletons (`rounded-card`), captured with a 2s request delay | `desktop/loading-addtx-form.png`, `mobile/loading-addtx-{picker,form}.png` |
| LE-02 | D / M | ACCEPTED | `/dev/error-page/404`; wordmark half (F-112) | `desktop/error-404-page.png`, `mobile/error-404-page.png` |
| LE-03 | D / M | PASS | `FormField` error "The name field is required." | `desktop/modal-newTally-error.png`, `mobile/sheet-newTally-error.png` |
| BC-01 | D | PASS | sans 12/600 section labels (F-106) | `desktop/design-system-0.png` |
| BC-01 | M | PASS | no horizontal overflow at 375 | `mobile/design-system-0.png` |

### Cut, removed and stubbed elements

Scanned the visible text, `aria-label` and `title` of Home, Tallies, Stats, You, Household and Ski Cabin detail, plus the
Add transaction, Share and New tally overlays, at 1440 and 375 (17 scans). Terms checked: Archived, Remind, Export, CSV,
Import, Transfer, Invite link, Copy link, Can edit, Can view, Link requires approval, Members can settle, Balance reminders,
Members & split, Invite someone, Days, receipt, Notifications, Scan. The scan also looked for a `lucide-bell` icon.
**0 hits.** Stubs render as decided:
- Search is a disabled input with the "Search coming soon" tooltip (B-02), hidden ≤1000px.
- There is no bell (DO-01).
- Tallies has no Archived segment (DO-04).
- Settle banners have no Remind (DO-05).
- There is no Export CSV on the detail, Stats or You (DO-06).
- Quick actions have no Import (DO-02).

### In-between breakpoints (900×800, 1024×768)

| Check | 900 | 1024 | Class |
|---|---|---|---|
| Shell | md icon rail, 72px | full 236px sidebar | 900 ACCEPTED: rail md–lg is the documented strategy (`MIGRATION_PLAN.md` responsive table; the design rails only at 641–860), width 72px and a glyph tile per tally, FP-08 FIXED · 1024 PASS |
| Search (≤1000 hidden) | hidden | visible | PASS |
| Home | hero 1-col, quick actions below, lower grid stacked | hero/quick stacked, Recent + By category stacked | 900 PASS · 1024 PASS (FP-02 FIXED: stacks at ≤1100) |
| Tallies | type/count cells shown (design hides them ≤860 only) | same | PASS |
| Tally detail | stacked | stacked | PASS |
| Stats | stacked | stacked | PASS |
| You | stacked | stacked | PASS |
| Horizontal overflow | none | none | PASS |

Shots: `desktop/{home,tallies,detail,stats,you}-{900,1024}.png`, `desktop/home-{900,1024}-1.png`.

### Multi-currency and data checks

- **Household** (EUR + USD, 60/40):
  - Hero: € 9,163.40 / per person € 4,581.70 and $ 320.00 / $ 160.00, never combined.
  - Banners: one per currency, "You owe Priya Shah € 4,692.24" and "$ 192.00".
  - DB check, EUR: expenses Sam 7,165.80, Priya 1,997.60, plus Sam's 15,900.00 income split 60/40. Sam owes 6,360.00 −
    1,667.76 = 4,692.24. USD: Priya paid 320.00, so Sam owes 60% = 192.00.
  - Tallies row: "+ € 6,736.60" (15,900.00 − 9,163.40) and "$ 320.00" on separate lines; status "You owe Priya Shah 4,692.24 · $".
  - Home card: € 1,368.15 only (no USD this month), status marker "· $".
- **Weekend Trip** (USD, shared 50/50):
  - Everything is in $: $ 875.70, "Noah Whitfield owes you $ 105.85" (543.70 − 437.85).
  - Home card shows "$ 0.00": no October activity, so it falls back to its own currency (O-02).
  - Stats filtered to the tally is all $: month $ 0.00, year $ 875.70, owed $ 105.85.
- **Ski Cabin** (3 members, 40/30/30): € 200.00. "Priya Shah owes you € 40.00" with "Noah Whitfield is settled" (Sam 120 vs
  80, Priya 20 vs 60, Noah 60 vs 60).
- **City Break** (EUR + USD, private):
  - Net € −118.40 and $ −274.50 in separate blocks; Home card shows two lines; sidebar "· $" marker present.
  - The sidebar sign is wrong (FP-01).
- **Cross-tally**, checked against DB sums for Oct 1–14 (EUR expense 1,699.54, income 2,650.00; USD expense 274.50):
  - Home: hero € 950.46 + "Other currencies: $ −274.50", In € 2,650.00 / Out € 1,699.54.
  - Home By category and Stats "Out this month": € 1,699.54 + "Other currencies: $ 274.50".
  - Stats year: € 9,842.76 + $ 1,470.20 (875.70 + 274.50 + 320.00).
  - Shared portion € 1,568.15 (1,368.15 + 200.00) = 92.3%.
- **demo-empty**: no figures at all; three `EmptyState`s, no sidebar rows, Add-tx opener "No tallies yet".
- **No figure adds currencies.** One status-like figure hides a non-zero currency without a marker. Stats "Owed to you"
  reads € 40.00 · "1 open balance" while Noah owes $ 105.85 on Weekend Trip: two open balances, one shown. This rested on the
  owner's F-075 decision of 2026-10-14 (no other-currency line on KPI cards); the owner reversed it the same day, so the card now adds
  "Also $ 105.85". Every other surface with a non-zero second currency shows either a second line or "· $".

### Observations (O-ids)

- O-01, O-02, O-03, O-04, O-05, O-06, O-07 and O-10 are **FIXED**; O-08 is **ACCEPTED** (deferred follow-up); O-09 was
  never raised. **No observation is open.**
- The **Totals** table's Observations row read "7 FIXED · 1 ACCEPTED · 1 OPEN" because it predated the last O-06 fixes
  (7f2729e, 369df17, ee810ad). Recounted and corrected on 2026-10-14: 8 FIXED · 1 ACCEPTED · 0 OPEN (9).

### `composer check`

**Delta check (2026-10-14, at `be67faa`, no code change):** run as `composer check; echo "exit: $?"`. It rebuilds `public/build`.

```
✓ built in 8.07s                                   (npm run build; vue-tsc reported nothing)
{"tool":"pint","result":"passed"}
 [OK] Rector is done!                               (the same warning: a skipped rule is never registered)
{"result":"passed","errors":0}                      (PHPStan)
{"result":"passed","tests":840,"passed":840,"assertions":3648, … "Total: 99.7 %"}
{"result":"passed","tests":676,"passed":676,"assertions":3116, … "1757 Mutations for 59 Files created",
 "Mutations: 3 timeout, 1754 tested", "Score: 100.00%"}
exit: 0
```

Previous run, after FP-11..FP-16 (2026-10-14, at `4433a78`): run as `composer check; echo "exit: $?"`. It rebuilds `public/build`.

```
✓ built in 7.97s                                   (npm run build; vue-tsc reported nothing)
{"tool":"pint","result":"passed"}
 [OK] Rector is done!                               (the same warning: a skipped rule is never registered)
{"result":"passed","errors":0}                      (PHPStan)
{"result":"passed","tests":840,"passed":840,"assertions":3648, … "Total: 99.7 %"}
{"result":"passed","tests":676,"passed":676,"assertions":3116, … "1757 Mutations for 59 Files created",
 "Mutations: 3 timeout, 1754 tested", "Score: 100.00%"}
exit: 0
```

Five tests were added (840 against 835): the `primary_currency` shared prop (guest, default currency, most-used fallback, once key) and
`HomeService::primaryCurrency()`.

Previous run, delta pass (2026-10-14, at `63d3398`, no code change): run as `composer check; echo "exit: $?"`. It rebuilds `public/build`.

```
✓ built in 8.02s                                   (npm run build; vue-tsc reported nothing)
{"tool":"pint","result":"passed"}
 [OK] Rector is done!                               (the same warning: a skipped rule is never registered)
{"result":"passed","errors":0}                      (PHPStan)
{"result":"passed","tests":835,"passed":835,"assertions":3617, … "Total: 99.7 %"}
{"result":"passed","tests":671,"passed":671,"assertions":3085, … "1749 Mutations for 59 Files created",
 "Mutations: 3 timeout, 1746 tested", "Score: 100.00%"}
exit: 0
```

Previous run, after FP-09 and the FP-07 / FP-08 follow-ups (it rebuilds `public/build`):

```
✓ built in ~8s                                     (npm run build; vue-tsc reported nothing)
{"tool":"pint","result":"passed"}
 [OK] Rector is done!                               (one warning: a skipped rule is never registered)
{"result":"passed","errors":0}                      (PHPStan)
{"result":"passed","tests":835,"passed":835,"assertions":3617, … "Total: 99.7 %"}
{"result":"passed","tests":671,"passed":671, … "1749 Mutations for 59 Files created",
 "Mutations: 3 timeout, 1746 tested", "Score: 100.00%"}
exit: 0
```

The 21 uncovered mutants recorded after FP-04..FP-08 are gone. They were not weak assertions: `TransactionController::edit()` / `update()` (lines 118–141, 19 mutants: the `tally` / `transaction` `only()` keys, both `authorize()` calls and the action call) and `HomeCacheService::sidebarVersion()` (line 216, 2 mutants: `Str::random(12)`). `WebTransactionControllerTest` exercised edit/update but had no `covers(TransactionController::class)`, so Pest's mutation run never attributed those tests to the class. Added the `covers()` call and tests that pin the exact edit payload (tally keys, transaction keys and values, `default_currency_uuid`, the `{id, name}` parties map, categories and currencies), a 403 on edit and on update for someone outside the tally (the update test also asserts the row is unchanged), and the 12-character `sidebarVersion` token (stable until `forgetForUser()`). Per class: `TransactionController` and `HomeCacheService` 100%, 0 uncovered, 0 surviving; `CategoryBars` (new) 100%. Pest's `--class` filter is not a reliable gate (it showed 100% before these tests existed); the full run above is.

### Verdict

**Delta check (2026-10-14, at `be67faa`): SHIP.**
- ST-03 D/M re-shot at 375×812 and 1440×900 and compared with `design/{desktop,mobile}/stats-0.png`: **ACCEPTED** (Daily
  average delta is plain `--text-3` text, measured; only the owner's arrow differs from the design).
- Every surface FP-11..FP-16 touched renders as fixed at both widths (see **Delta check** above). 0 REGRESSED, 0 STILL OPEN.
- **Totals:** PASS 38 · REGRESSED 0 · STILL OPEN 0 · ACCEPTED 39 (unchanged).
- `exit: 0` (840 tests, 99.7% coverage, mutation score 100.00%).
- Not a code issue, for the deploy: an environment whose `.env` still says `APP_NAME=TallyPal` shows that casing in titles and
  mails (FP-16 CASING).

Previous verdict, after FP-11..FP-16 (2026-10-14): **the delta pass's blocker is gone.**
- **FP-11 is decided and fixed:** the Colour rule makes spending deltas neutral, so the Stats Daily average is plain `--text-3` text (no pill, as the design draws it) and Home's balance delta keeps its tint. FP-12..FP-16 (colour collisions, the red income arrow, the empty-tally figure, the greeting, the titles) are fixed too.
- **Totals:** PASS 38 · REGRESSED 0 · STILL OPEN 0 · ACCEPTED 39.
- **How it was checked:** each fix side by side at 375×812 and 1440×900 with the `.verify/design/` screens it touches (named under each finding); the other rows were not re-shot, as none of these changes reaches them. The one judgement call: the income arrow is `--pos` (FP-13), not neutral.

Previous verdict, delta pass (2026-10-14, at `63d3398`): **NO-SHIP until FP-11 is decided.**
- **Why not ship:** Stats paints a 27.4% rise in daily spending as a green ↑ gain. That is the same misreading FP-01 was
  blocked for, and it needs an owner call (neutral or reversed tone) plus a small change.
- **What holds:**
  - 0 REGRESSED rows. FP-01..FP-10 render as fixed at 375, 1440, 1024, 900 and 860.
  - The FP-01 figures agree on every surface, and they differ only where a period label says why.
  - The By category rows add up to € 1,699.54, and the $ 274.50 line accounts for USD.
  - "Owed to you" shows "Also $ 105.85".
  - `exit: 0` (835 tests, 99.7% coverage, mutation score 100.00%).
- **Totals:** PASS 38 · REGRESSED 0 · STILL OPEN 2 (FP-11) · ACCEPTED 37.
- **Resolved since the previous verdict:** the Home tally card amount is now labelled ("spent in Oct").

Previous verdict:

**FP-01 is fixed** (2026-10-14; every per-tally amount is spending, labelled by period), so the data blocker is gone. The checks were green (`exit: 0`), nothing regressed, every recorded decision renders as
decided, and no cut element survives. FP-02..FP-08 are FIXED too (layout split and stacking, glyph tint, bar scaling, segmented size, outline fill, footer seam, rail width), each compared side by side with its `.verify/design/` reference.
"Owed to you" now shows the open $ 105.85 balance (F-075 reversed in part). Open for the owner: the Home tally card amount is an unlabelled expense total.

---

## Categories batch — income categories + category picker (approved 2026-10-14)

**Source of truth:** `Tallypal Categories.html` (Claude Design project `019dc420-c153-725e-9f8b-4ee4e47b22df`) for
the income categories and the picker; `Tallypal Desktop.html` stays the source for everything else. Local copy:
`.verify/design/categories/tallypal-categories.html`. References (132 PNGs) in `.verify/design/categories/`:
`{desktop,mobile}-{dark,light}-full.png`, `…-section-s1..s6.png`, `…-tile-states.png`,
`{desktop,mobile}-frame-NN-<caption>-<theme>[-en|-it].png` (frames 01–10 forms, 11–13 rows) and
`{desktop,mobile}-interactive-*.png` (grid "no match", phone sheet filtered). The design draws its phone frames at 393 px,
so at 375 px they are cropped on the right; use the `desktop-frame-05..10` captures for the full phone frame.

Branch: `feature/categories-restyle` (from `dev`, merged back to `dev` for online testing, then `master`).

### What the design text instructs (not only drawn)

- Repayments between members are **never a category** (a separate "Record a payment" flow will own them). The
  Expense / Income control has **two options**, no Transfer.
- 11 income categories (table below). Labels are short nouns; Italian phrased like a bank app. Glyphs are plain Unicode
  text tinted by CSS colour, none shared with the expense set, none with a colour-emoji form.
- Each category has search **aliases** (EN + IT) that the picker filter matches together with both labels.
- Picker = direction **A** (frequent row + "All") with **B**'s glyph grid and **C**'s filter inside "All".
- Row: 5 frequent tiles + "All N" on desktop, 4 + "All N" on the phone. Frequency is **per type, last 90 days**; the
  defaults apply until a category has been used **twice**. Defaults: Groceries, Dining Out, Transportation, Bills,
  Subscriptions / Salary, Side work, Refunds & returns, Gifts, Investments. A selected category that is not frequent is
  **pinned to the first slot** (also the saved category in Edit).
- Switching Expense ↔ Income swaps the row; an invalid pick is **cleared** and a one-line `--neg` notice names what was
  dropped (`<b>Groceries</b> is an expense category — cleared.`) until the next pick, with a short nudge animation.
- Keyboard: the row is a radio group (← → Home End, Space picks); Enter on "All" opens the grid **with the filter
  focused on desktop**; in the grid ↑ ↓ step a row of four, ← → one tile, ↑ from the first row returns to the filter,
  Enter in the filter picks the first match, Esc closes and returns focus to "All".
- Phone: tiles ≥ 65 × 76 px; "All" opens a **second bottom sheet at 80 % height** titled "Expense categories" /
  "Income categories"; the filter **never takes the keyboard unless tapped**.
- Category is **optional**: empty shows "◌ No category" where the pick would be; Save stays enabled.
- Rows: no new anatomy. The icon tile takes the category's glyph and colour token, the sub-line names the category, the
  amount keeps the existing rule (income `+` in `--pos`). The old generic "↓" salary row becomes "¤ Salary".
- Notes (out of the design's scope, kept here): Stats "By category" is expenses only (it already is, see audit);
  "Paid by" reads "Received by" on income (already shipped, `transactions.fields.received_by`).

### Income categories (seed rows, exactly as the design lists them)

| # | slug | EN | IT | Glyph | Colour | Aliases |
|---|---|---|---|---|---|---|
| 1 | `salary` | Salary | Stipendio | ¤ U+00A4 | `c3` | paycheck busta paga |
| 2 | `side_work` | Side work | Lavoro extra | ✦ U+2726 | `c2` | freelance invoice fattura lavoretti |
| 3 | `bonus` | Bonus | Bonus e premi | ✪ U+272A | `c4` | commission provvigioni premio tredicesima |
| 4 | `investments` | Investments | Investimenti | ▲ U+25B2 | `c1` | dividends interest crypto dividendi interessi |
| 5 | `rental_income` | Rental income | Affitti | ◫ U+25EB | `c1` | tenant inquilino canone |
| 6 | `refunds_returns` | Refunds & returns | Resi e rimborsi | ↺ U+21BA | `c2` | rimborso return reso cashback cancellation storno |
| 7 | `gifts_received` | Gifts | Regali | ❖ U+2756 | `c6` | regalo present birthday compleanno |
| 8 | `sold_items` | Sold items | Vendite | ⬡ U+2B21 | `c4` | ebay vinted subito second hand usato |
| 9 | `pension_benefits` | Pension & benefits | Pensione e sussidi | ⊚ U+229A | `c1` | inps naspi welfare sussidio |
| 10 | `winnings` | Winnings | Vincite | ⚄ U+2684 | `c5` | lottery lotteria bet scommessa prize premio |
| 11 | `other_income` | Other income | Altre entrate | ⊕ U+2295 | `c3` | altro misc |

**U+FE0E rule (F-037):** checked against Unicode's `Emoji` property: **none of the 11 has an emoji form**, so none is
stored with U+FE0E (`asTextGlyph()` still normalises every glyph at render). Expense glyphs that need it stay as they are
(✂ ☕ ♥ ✈). Slugs are proposed (stable keys); the design's own ids (`side`, `invest`, `gifts_in`, …) are shorthand.

Expense set, from the design (IT labels, colours and aliases are new to the app; glyphs are the D1 map unchanged):

| slug | EN | IT | Glyph | Colour | Aliases |
|---|---|---|---|---|---|
| `bills` | Bills | Bollette | ▤ | `c4` | bolletta |
| `clothing` | Clothing | Abbigliamento | ✂︎ | `c6` | vestiti |
| `dining_out` | Dining Out | Ristoranti | ☕︎ | `c6` | restaurant bar pizza |
| `education` | Education | Istruzione | ✎ | `c1` | scuola corso |
| `entertainment` | Entertainment | Svago | ★ | `c6` | cinema concerto |
| `gifts_donations` | Gifts & Donations | Regali e donazioni | ♥︎ | `c5` | charity |
| `groceries` | Groceries | Spesa | ◈ | `c4` | supermercato |
| `healthcare` | Healthcare | Salute | ✚ | `c3` | farmacia medico pharmacy |
| `household_supplies` | Household Supplies | Prodotti per la casa | ▦ | `c4` | pulizie cleaning |
| `insurance` | Insurance | Assicurazioni | ▣ | `c1` | polizza |
| `personal_care` | Personal Care | Cura personale | ✿ | `c6` | parrucchiere |
| `rent` | Rent | Affitto | ⌂ | `c1` | canone |
| `subscriptions` | Subscriptions | Abbonamenti | ♪ | `c2` | netflix spotify |
| `transportation` | Transportation | Trasporti | ◎ | `c2` | taxi metro benzina fuel |
| `travel` | Travel | Viaggi | ✈︎ | `c2` | volo hotel flight |
| `utilities` | Utilities | Utenze | ↯ | `c5` | luce gas acqua |

The design's expense list also has **Housing** (Casa, ⌂, `c1`). It was an error in the design brief (owner, C-D5):
it is **not** added. The app has 16 expense categories and the "All" tile reads **"All 16"** (the design's "All 17" is
wrong).

### Audit of the current category model (2026-10-14)

- **Storage.** `categories` (`category_id`, `uuid`, `name`, `description`, `icon` (always null), timestamps). The 16
  rows are inserted **inside the create migration** (`2025_10_31_102705`), ids 1–16 in alphabetical order; uuids
  backfilled by `2026_03_29_185413`. **No `type`, no `color`, no slug.** `Category::$fillable` lists `color` but the
  column does not exist (DESIGN_TOKENS §7 planned it). Names are English strings from the DB, shown untranslated
  (Italian users see "Groceries").
- **`category_code`.** A derived two-letter code (`TransactionStatsRepository::categoryCode()`), sent in the Home recent /
  Stats activity payloads; used only as a colour-hash fallback in `HomeRecentTransactions.vue`. It collides; D1 already
  bans it as a key.
- **D1 glyph map.** `ui/category-glyph/categoryGlyphs.ts`, `CATEGORY_GLYPHS` keyed on `category_id` 1–16.
  `categoryGlyph(id, type)` returns **↓ for every income regardless of category**, ◌ when unmapped.
- **Colour.** Three different rules today: the form chips colour by id cycle (`categoryColorFromId`), the rows by a hash of
  the name (`transactionGlyphColor` → `categoryColorFromString`), and income rows are always `--pos` (FP-13). The By
  category cards colour by rank (FP-12) and are not affected by this batch.
- **Validated.** `StoreTransactionRequest` and `UpdateTransactionRequest` (shared by the web and the API controllers):
  `category_id` `required|exists:categories,category_id`, `type` `in:income,expense`. **No check that the category fits
  the type.** `transactions.category_id` is `NOT NULL` with a FK.
- **Seeded.** By the create migration only (no seeder). `DemoSeeder` looks categories up by English name and seeds the
  monthly **Salary as `INCOME` with category `Bills`** (the exact mismatch this batch forbids).
- **Shown.** Form chips (`ChipGroup` in `AddTransactionForm.vue`, used by `AppOverlays` sheet/dialog and the
  `transactions/Create` page; and in the `transactions/Edit` **page**); rows in Home Recent
  (`HomeRecentTransactions.vue`), Tally detail (`tallies/transactions/Index.vue`), Stats All activity
  (`StatsActivity.vue`); By category on Home (`HomeCategoriesBreakdown.vue`) and Stats (`StatsCategoryBreakdown.vue`).
  Form data comes from `TransactionController::formData()` → `Category::all(['category_id', 'name'])`.
- **Breakdowns already spending-only.** Home and Stats both use `TransactionStatsRepository::categoriesBreakdown()`,
  which filters `transactions.type = expense` (and the primary currency, `transaction_date` range). Covered at the
  repository level (`categoriesBreakdown only adds up expenses in the given currency` excludes a USD income); **no
  endpoint test** asserts that an income category never appears in the Home / Stats payloads. B-16 adds it.
- **Currencies.** Same pattern: the 10 rows are inserted inside `2025_10_31_103005_create_currencies_table`; no seeder.
  `DatabaseSeeder` only creates a test user. The repo has no deploy script (`deploy/` holds only the SSR unit), so
  migrate/seed steps live in the deploy configuration outside the repo.

### Owner decisions (C-D) — decided 2026-10-14

| ID | Question | Decision |
|---|---|---|
| C-D1 | Category optional ("◌ No category", Save enabled)? | **Yes.** `transactions.category_id` nullable (`nullOnDelete`), requests `nullable`. Uncategorised **spending** appears as an **"Uncategorised" row (◌)** in Home and Stats By category, so the rows still add up to the total (FP-09). |
| C-D2 | Glyph map key | **`slug`** (amends D1); payloads send `category_slug`. |
| C-D3 | Labels | **i18n by slug**, EN/IT for all 27; DB `name` stays the English fallback. |
| C-D4 | Per-category colour | **Yes, scoped:** a category's stored `color` tints its **glyph tile** in rows and in the picker. The By category cards keep **FP-12's rank colours**. Rule: **"the glyph identifies a category everywhere; colour only separates rows within a view."** Income amounts stay `--pos`; an uncategorised income keeps ↓ in `--pos`. |
| C-D5 | Add "Housing"? | **No.** It was an error in the design brief. "All 16". |
| C-D6 | Frequency | **Build it** (B-15). |
| C-D7 | Aliases | **Frontend catalogue** keyed by slug. |
| C-D8 | Edit is a page | **Keep the Edit page**, with the picker inside it. |
| C-D9 | Wrong-type category | **Reject (422)**, `validation.category_type_mismatch`. |
| C-D10 | Reference data on deploy | **Yes.** `deploy/CHECKLIST.md` holds the step, with the restyle deploy steps. |
| C-D11 | Reset scope | **As listed** in B-17. |
| C-D12 | Stats By category type toggle | **No toggle** (already expenses only). |

### BACKEND mini-specs

**B-13 — Category type, slug, colour + reference-data seeder** (C-D1..C-D4, C-D10)
- **Status: PLANNED**
- Schema (one new migration, no edits to old ones): `categories` + `type` (enum `expense|income`, default `expense`),
  `slug` (string, unique), `color` (enum `c1`…`c6`, nullable). Backfill `slug`/`color` of the 16 existing rows **by
  current `name`** (only rows that exist), then make `slug` non-null + unique. `transactions.category_id` → nullable,
  FK `nullOnDelete` (C-D1). Must run cleanly on a **fresh DB** and on a DB that already has users (and, until the
  reset, transactions).
- Enum `App\Enums\CategoryType` (`EXPENSE`, `INCOME`), cast on `Category::$type`; `Category::scopeOfType()`.
- `Database\Seeders\ReferenceDataSeeder`: **idempotent upsert** — currencies by `code` (the 10 current rows, same
  values), categories by `slug` (16 expense + 11 income, `name` = EN label, `type`, `color`). Never deletes. Called by
  `DatabaseSeeder` and by the deploy (C-D10). `DemoSeeder` stops relying on the migration's rows and looks categories
  up by `slug`. The create migrations keep their inserts (history; fresh DBs still get them, the upsert then matches).
- Payloads: `formData()` / `edit()` send `categories: {category_id, slug, type, name, color}[]`; Home recent, Stats
  activity and Tally detail rows send `category_slug` and `category_color` (and stop needing `category_code`).
  Bump `HomeCacheService::KEY_VERSION` (`v11` → `v12`).
- Frontend: `categoryGlyphs.ts` keyed on slug (C-D2) + the 11 income glyphs; `categoryGlyph(slug, type)` returns ↓ only
  for an **uncategorised** income. i18n `categories.<slug>` EN/IT for all 27 (C-D3).
- **Must-have: the migration backfills** `slug`, `type` and `color` onto the 16 existing category rows (matched by their
  current `name`), so `ReferenceDataSeeder` matches them and never inserts duplicates. Currencies are keyed on their
  existing `code` column, so they need no backfill and no new index (`updateOrCreate` by `code`; a unique index was
  dropped from the plan because ~66 tests deliberately create a second EUR/USD/GBP/JPY row; production's codes are
  already distinct).
- Tests: **migrate a database holding the old 16 category rows (and the 10 currencies), run the seeder twice, assert
  exactly 27 categories and 10 currencies, with the original ids kept** (1–16 unchanged, same `uuid`s); a second run
  changes nothing; a fresh `migrate:fresh` + seeder gives the same 27 / 10; `Category` cast and scope.

**B-14 — Category must match the transaction type** (C-D9) — **tests first**
- **Status: DONE (R2)** — `App\Rules\CategoryMatchesType`; `category_id` is `nullable` + `exists` + same type in both requests
  (web and API); `TransactionDTO` takes a null category. Tests: `tests/Feature/Http/TransactionCategoryValidationTest.php`.
- Rule `App\Rules\CategoryMatchesType` (or a closure `Rule::exists(...)->where('type', $type)`), applied to
  `category_id` in `StoreTransactionRequest` and `UpdateTransactionRequest`: `nullable` (C-D1) + `exists` + same type.
  Error key `category_id`, message `validation.category_type_mismatch` (EN/IT).
- Tests (written before the rule): expense + expense category → ok; income + income category → ok; income + expense
  category → 422 on `category_id`; expense + income category → 422; no category → ok; update that flips `type`
  without changing the category → 422; same on the API routes (`Api\TransactionController`).

**B-15 — Frequent categories per type** (C-D6)
- **Status: DONE (R3)** — keyed on `transactions.party_id` (no creator column exists; `party_id` is the payer, or the receiver of an
  income). The repository also pads with `CategoryType::defaultSlugs()`, so `frequent_categories` is always 5 slugs per type (the
  picker shows 4 on phones); sent with the Add/Edit/Create form data. Two queries, no cache (query-count test in
  `WebTransactionControllerTest`).
- `TransactionStatsRepository::frequentCategories(int $userId, CategoryType $type, CarbonImmutable $now): list<string>`
  — slugs used ≥ 2 times by the user's tallies in the last 90 days (`transaction_date`), most used first, ties by most
  recent. Sent with the form data as `frequent_categories: {expense: string[], income: string[]}`; the picker pads it
  with the design defaults to 5 (desktop) / 4 (phone).
- Tests: window edges (day 90 in, day 91 out), the ≥ 2 threshold, per-type split, another user's tallies excluded.

**B-16 — Breakdowns: no income, and an "Uncategorised" row** (audit: already filtered by type; C-D1)
- **Status: DONE (R3) backend + row UI** — rows carry `slug` and `is_uncategorised`; `KEY_VERSION` v13; `categories.uncategorised` EN/IT;
  the ◌ row is in `HomeCategoriesBreakdown.vue` and `StatsCategoryBreakdown.vue` (screenshotted in R4: CA-19/CA-22 pass).
- Income: tests only, unless they fail. `HomeControllerTest` and `StatsControllerTest`: a user with an income in an
  income category (and a large amount) in the range → its category never appears in `categories_breakdown` /
  `categories`, and the totals equal the expenses only.
- Uncategorised spending: `categoriesBreakdown()` already groups `NULL` categories (today as the literal
  `'Uncategorised'`). Rows gain `slug: string|null` and `is_uncategorised: bool`; the label comes from i18n
  (`categories.uncategorised`, "Uncategorised" / "Senza categoria"), not the SQL literal. It ranks by amount like any
  category (it folds into Other when outside the top five). Its row shows **◌ in `--text-3` in place of the dot**; the
  bar keeps the rank colour rule (FP-12). Bump `KEY_VERSION`.
- Tests (Home and Stats): expenses with no category appear as one Uncategorised row; **the named rows + Uncategorised
  + Other add up to the period's expense total** (FP-09); an uncategorised **income** never appears.

**B-17 — `tallypal:reset-activity`** (C-D11)
- **Status: DONE (R4)** — `App\Console\Commands\ResetActivityCommand`; also refuses unless `ALLOW_ACTIVITY_RESET=true`
  (`config('app.allow_activity_reset')`), see `deploy/CHECKLIST.md` step 9. Tests: `tests/Feature/Console/Commands/ResetActivityCommandTest.php`.
- `php artisan tallypal:reset-activity --force`. Without `--force`: prints the row counts it would delete and exits
  **1** without touching anything (no interactive prompt, so it cannot be confirmed by accident in a pipeline).
- With `--force`, in one DB transaction: delete `transactions`, `tally_invitations`, `tally_user`, `tallies` (that order;
  `tally_user` has no cascade). Then `HomeService::forgetCacheForUser()` for every user. Keeps `users`, roles,
  personal access tokens, `currencies`, `categories`. Prints the deleted counts.
- Tests: without `--force` nothing is deleted and exit code is 1; with `--force` the four tables are empty, users /
  currencies / categories counts unchanged, a user's Home cache key is gone.
- Production runbook: `deploy/CHECKLIST.md` (migrate → `db:seed --class=ReferenceDataSeeder --force` → … →
  `tallypal:reset-activity --force`).

**DemoSeeder** (part of R4): income transactions with income categories (Salary → `salary` monthly; plus Side work,
Refunds & returns, Gifts, Investments, Sold items spread over the solo/household tallies and months), keeping every
fixed tally UUID; no expense category on any income; one income with no category (C-D1) for the ◌/↓ state.

### Components

- **`CategoryPicker`** (new, COMPONENT_INVENTORY **E7**) replaces `ChipGroup` for categories in Add transaction (sheet
  < md, dialog ≥ md, `transactions/Create` page) and the `transactions/Edit` page. `ChipGroup` stays for its other users.
- Values: DESIGN_TOKENS **§7.1** (category colours) and **§7.2** (picker values).

### Runs (one session each, in order)

| Run | Model | Scope | Done when |
|---|---|---|---|
| R1 | Opus 5.5 | **Data**: B-13 (migration **with the backfill**, enum, `ReferenceDataSeeder`, payloads, `KEY_VERSION`, glyph map by slug + income glyphs, i18n labels). `DemoSeeder` lookups switched to slugs (no new data yet). | The 16-row → 27 / 10 test passes with original ids kept; migrations pass on `migrate:fresh` and on a DB with users; `composer check` exit 0 |
| R2 | Sonnet 5.5 | **Validation**: B-14, tests first (red, then green). | Tests listed in B-14 pass; `composer check` exit 0 |
| R3 | Sonnet 5.5 | **Frequency + breakdowns**: B-15 and B-16 (no income; Uncategorised row ◌; rows add up to the total). | Both test sets pass; `composer check` exit 0 |
| R4 | Sonnet 5.5 | **Reset + demo**: B-17 command; `DemoSeeder` incomes with income categories, fixed UUIDs kept. | Command tests pass; `db:seed --class=DemoSeeder` twice is clean; `composer check` exit 0 |
| R5 | Opus 5.5 | **Build first: `CategoryPicker`** (E7) with a `/dev/design-system` example of every state (CA-01, CA-02 rows), both themes, 375 / 1440. Not adopted yet. | Screens side by side with `…-tile-states.png` and frames 01–08; `composer check` exit 0 |
| R6 | Sonnet 5.5 | **Adopt in Add transaction** (sheet, dialog, Create page), type switch clears visibly. | CA-03..CA-12 D/M verified with screenshots; `composer check` exit 0 |
| R7 | Sonnet 5.5 | **Adopt in Edit + rows**: Edit page; Home Recent, Tally detail, Stats All activity glyph/colour by slug. | CA-13..CA-18 D/M verified; `composer check` exit 0 |
| R8 | Opus 5.5 | **Delta verification**: every CA row both viewports and themes, plus a regression pass over Batches 1, 3, 4, 6 rows touching categories; re-run DemoSeeder after any browser edits. | Verdict + `exit: 0` quoted |

**R1 done (2026-10-14).** Migrations `2026_10_14_211827` (categories `type`/`slug`/`color` + backfill, unknown rows get
`<name>_<id>`) and `2026_10_14_211828` (`transactions.category_id` nullable, `nullOnDelete`); `CategoryType` enum, `Category`
cast + `ofType` scope + `CategoryFactory`; `ReferenceDataSeeder` (called by `DatabaseSeeder` and `DemoSeeder`); form data
sends `{category_id, slug, type, name, color}`; Home recent / Stats activity rows add `category_slug` + `category_color`
(`KEY_VERSION` v12); glyph map keyed by slug with the 11 income glyphs; `categories.<slug>` EN/IT. `TransactionFactory`
picks a category of the transaction's own type (none for income unless the seeder ran). Deviations: **no unique index on
`currencies.code`** (see B-13); **`category_code` kept** in the payloads (removing it means deleting two existing tests:
needs owner approval). Found on the dev DB: a hand-added 17th category "Housing" (id 17, no transactions) — the
migration gave it slug `housing_17`; it will show in the local picker until deleted. `exit: 0` (848 tests, 99.8%
coverage, mutation score 100.00%).

**Follow-ups (recorded in R2, not done yet).**
- **R4 — delete unused non-reference categories (DONE R4: migration `2026_10_15_090000`).** Add a migration that deletes the categories that are not in the
  `ReferenceDataSeeder` set (matched by `slug`) and have no transactions; this removes the hand-added "Housing"
  (`housing_17`) in every environment. Test: a non-reference category that **is** in use is kept (and an unused one is deleted,
  a reference one is never deleted).
- **After this branch — unique `currencies.code`.** First fix the test fixtures that create duplicate currency codes
  (see B-13: ~66 tests create a second EUR/USD/GBP/JPY row), then add a unique index on `currencies.code`.

**R4 done (2026-10-15).** `tallypal:reset-activity` (guarded by `ALLOW_ACTIVITY_RESET`), migration `2026_10_15_090000` deleting unused
non-reference categories (dev DB: "Housing" gone, 27 categories, 16 expense), `DemoSeeder` (incomes use income categories, 4 uncategorised
rows, relative-dated "recent habits" so the demo user's frequent row is groceries / dining_out / transportation and salary / side_work).
The uncategorised total is sized (184.50) to rank in the top five, otherwise it folds into Other and no ◌ row shows.
Seen while screenshotting, not changed: an uncategorised expense in Home Recent / Stats activity shows the sub-line "expense" (the type)
instead of a label such as "Uncategorised".

**R5 done (2026-10-15).** Vitest + `@vue/test-utils` (happy-dom) run by `npm run test`, in `composer check` before the PHP steps.
`ui/category-picker`: `CategoryPicker` + `CategoryTile`; rules in `pickerLogic.ts` (alias search over EN + IT labels and the C-D7
catalogue `categoryCatalogue.ts`, case/accent-insensitive; frequent row padded with the defaults, deduplicated, selection pinned
first; clear rule; keyboard model), 40 unit tests written first and run red, plus 8 component tests. Extends `CategoryGlyph`
(`size="tile"`, `color` optional) and `BottomSheet` (`contentClass`, `closeAutoFocus`); `nudge` / `drop` keyframes in `app.css`;
`category_picker.*` EN/IT. `/dev/design-system` shows every state for both types in a dialog frame and a phone-sheet frame, plus
a live picker in `ResponsiveModal`. A Playwright keyboard-only run passed 38/38 checks (row ← → Home End Space, Enter on All → filter focused,
grid ↑ ↓ ← →, ↑ from the top → filter, Enter picks the first match, Esc → All with the dialog kept open; phone: second sheet,
filter not focused, Esc closes only it). Notes: the scrim is the app's `--color-scrim` (.55 + blur), not the design's .45;
radius 14px is `var(--radius)` because the app's `rounded-sm` resolves to 12px; the phone sheet keeps the design's second close
button beside the filter. Not adopted anywhere yet (R6/R7). Screens: `{desktop,mobile}/r5-picker-*`.

**ACCEPTED differences (owner, 2026-10-15)** — recorded so R8 does not re-flag them:
- **Phone tile width 62 px at 375** (CA-02 M gap): a 5-column row inside the 20 px sheet gutter. The design draws the phone at 393 px,
  where the same row is 65 px; the tiles keep the design's 76 px height and are **≥ 57 px at 360, with no label clipped** (corrected
  2026-10-15: the first note said "≥ 62 px at 360", which is arithmetically wrong; 57 px is the Add sheet at 360 with a classic scrollbar).
- **Scrim:** the picker's sheets and the dialog use the app's `--scrim` (`--color-scrim`, .55 + blur), not the design's .45.
- **Second close button in the phone sheet** (beside the filter, as drawn): it closes the same category sheet as the sheet's own
  header close, so both keep the accessible name "Close" (`common.close`). A button that did something else would need its own name.

**R6 done (2026-10-15).** `CategoryPicker` replaces the chip wrap in `AddTransactionForm` (so the desktop dialog, the phone sheet and the
`transactions/Create` page); `AppOverlays` / `Create.vue` pass `frequent_categories`. `form.category_id` is `number | null` and Save no
longer needs a category (C-D1). The type comes from the Expense / Income control; a type switch clears an invalid pick and shows the
`--neg` notice. A 422 on `category_id` shows in the E1 error state: the picker row takes `aria-invalid` + `aria-describedby` from
`FormField` (new `for="category_id"`), a 1 px `--neg` outline stands in for the control border, and the message is the 12 px
`InputError`. `AmountInput` gains `tone="positive"` (a `+` before the currency, figure in `--pos`) for CA-05; the form passes it on Income.
Keyboard containment: `CategoryPicker` exposes `dismiss()` (panel first, then the notice) and the form's `@keydown.esc` calls it, so
Esc closes one layer at a time; Enter in the filter or on a tile never submits. Focus after a pick returns to the picked tile in the row
(E7); Esc returns it to "All". The row is one tab stop, straight after Title: the selected tile, else "All".
**Browser tests are now Pest browser tests** (`pestphp/pest-plugin-browser`, Playwright underneath): `tests/Browser/AddTransactionFormTest.php`,
29 cases (frequent-tile expense and Italian "stipendio" income at 1440 / 375 / 360 × dark / light with a no-truncation check, type-switch
notice, server-422 state, Enter containment, two-step Esc for the panel and for the notice, tab order and focus return, each at dialog and
sheet). Outside the PHPUnit suites (so outside coverage and mutation), run by the new `composer test:browser`, the last step of
`composer check`; it always uses the built assets (`npm run build` runs first). Screens: `{desktop,mobile}/r6-*`.

**Approved dependency (owner, 2026-10-15):** `pestphp/pest-plugin-browser` (with `playwright` in `package.json`) is approved. `composer check`
now needs a Playwright Chromium wherever it runs: locally `npx playwright install chromium`; in CI the `Install Playwright Chromium` step of
`.github/workflows/shared-ci.yml` (`npx playwright install --with-deps chromium`).

**R7 done (2026-10-15).** Edit page: `CategoryPicker` replaces the chip wrap (prefill, type flip clears with the notice, 422 in the E1 error state, `+` in `--pos` on
Income, Esc closes one layer at a time); `EditTransactionFormTest` (browser). Rows in Home Recent, Tally detail and Stats All activity (C-D4, scoped): the tile takes
the category's stored `color` (income categories included), the glyph is the category's own (↓ only for an uncategorised income, ◌ in `--text-3` for an uncategorised
expense), amounts keep `--pos`; the sub-line is the i18n label by slug (`useCategoryLabel`), "Uncategorised" / "Senza categoria" when there is none, never the type.
Stats activity now reads "category · date" as drawn. The Tally detail payload sends `category.slug` and `category.color` (B-13 had planned it; they were missing, which
the first screenshots showed). `category_code` removed from every payload, type and the repository, with its two tests (owner-approved); `KEY_VERSION` v13 → v14.
Shared browser-test helpers moved to `tests/Browser/Support.php`.

**R8 final verification (2026-10-15, at `77158b9`).** DemoSeeder re-run before and after (the run saved two incomes and a
`demo-empty` tally through the UI). Built assets (`public/hot` set aside: Vite 504 "Outdated Optimize Dep"), no console errors.
188 viewport shots in `.verify/app/categories-final/`, 1440×900 + 375×812, dark + light, EN + IT wherever the design has an IT frame
(frames 01–10).
- **Totals (44 rows):** PASS 41 · REGRESSED 0 · STILL OPEN 2 (CA-13 M, CA-14 M) · ACCEPTED 1 (CA-02 M).
- **STILL OPEN — Edit page at phone widths.** `tallies/transactions/Edit.vue` renders the picker inside the page card, so it gets
  255 px at 375 (the Add sheet gives 335): tiles 49 px at 375 / 46 at 360. It clips "Refunds & returns", "Groceries" and "Transportation" (EN)
  and "Trasporti" (IT, 360). R7 passed it in error: its own screenshot shows "Refunds &…". The Add sheet is clean at 375 and 360.
- **Picker at 360×800 (Add):** 4 + All on both types, no label truncated (EN + IT, row and grid, dark + light), no horizontal scroll.
- **Regressions:** F-089 holds (submit inside the viewport at 1440×900, 1280×720, 375, 360, with All open too). F-092 / B-11 hold
  (pill menu of 10 currencies; untouched form follows Household EUR ↔ Weekend Trip USD; manual GBP survives two tally switches; the
  category survives too). By category on Home and Stats: EUR rows 950.00 + 346.85 + 184.50 (◌) + 158.30 + 149.20 + Other 316.09 =
  2,104.94 = OUT = the DB's October EUR expenses; "Other currencies: $ 274.50" = the DB's USD expenses. A € 3,200 Salary added in the UI
  raised IN, not By category. Rows on Home Recent (¤ Salary, ↺ Refunds & returns after the UI adds), Tally detail and Stats activity match frames 11–13.
- **Data rules:** an income with Groceries and an expense with Salary are both rejected (forced through the real form: E1 state,
  `aria-invalid`, "The category must match the transaction type.", dialog kept open, nothing saved; the JSON request returns **422**
  on `category_id`). "Housing": no category, slug or label in the app or DB; the word remains only in the marketing features tile's sample
  copy (pre-branch, separate context) and `/dev/design-system` fixtures. "All 16" / "All 11" ("Tutte 16" / "Tutte 11").
- **`deploy/CHECKLIST.md`:** covers migrations (3), `ReferenceDataSeeder` right after migrate (4) and the `ALLOW_ACTIVITY_RESET`
  flow (9). It does **not** mention Chromium for the gate: only CI installs it (`shared-ci.yml`), so a manual `composer check` before
  deploy on a fresh machine fails at `test:browser`.
- **Observations (not CA rows):** the phone "All" sheet is content-sized (`max-h-[80%]`, 407 px), so it shrinks to 235 px when a filter
  leaves one match; on the Edit page and in the IT Add dialog the date reads "October 15, 2026" (not localized, pre-branch); titles stop at
  25 characters (`transactions.title` is `string(25)`, pre-branch), so the design's "Trenitalia — cancelled train" cannot be entered;
  `Rules/CategoryMatchesType` reports **0.0 %** line coverage although `TransactionCategoryValidationTest` exercises it (total still 99.4 %).
- **Gate:** `composer check; echo "exit: $?"` → `exit: 0` — Vitest 59 passed; Pest 904 passed (3,900 assertions), coverage
  **99.4 %**; mutation **100.00 %** (1,765 mutations, 3 timeouts); browser 40 passed.
- **Verdict: no-merge** until CA-13 M / CA-14 M are fixed (Edit-page labels clip at 375 and 360). Everything else is green.

**R9 fixes (2026-10-15).**
- **CA-13 M / CA-14 M:** `tallies/transactions/Edit.vue` — the form card is `sm:` only (`sm:rounded-card sm:border sm:bg-surface sm:p-6 md:p-7`), so below
  640 px the form spans the content width. Picker 335 px at 375, 320 at 360 (= the Add sheet); desktop (1440) and tablet (700) keep the card
  (1 px border, 28 / 24 px padding). No label clipped at 375 / 360, dark + light, EN + IT, expense + income (incl. "Stipendio" after the flip).
- **Phone "All" sheet:** `CategoryPicker` holds the grid's unfiltered height (`min-height`) while a filter narrows it, so the sheet stays 493 px
  at 375 for the full list, 1 match, several matches and the no-match line (was 407 → 235). Vitest + a Pest browser test cover it.
- **`Rules/CategoryMatchesType` at 0 %:** it **is** the only enforcement — with the rule removed from both requests, 9 of 31
  `TransactionCategoryValidationTest` cases fail (302 / 201 / 200 instead of 422). The 0 % was attribution: the test's `covers()` named only the two
  requests, so the rule's lines counted for no one. `covers()` now names the rule too and it reports 100 %.
- **Gate:** `composer check; echo "exit: $?"` → `exit: 0` — Vitest 61 passed; Pest 904 passed (3,900 assertions), coverage **99.8 %**; mutation **100.00 %** (1,771 mutations, 3 timeouts); browser 49 passed.
- **Docs:** `npx playwright install chromium` in `deploy/CHECKLIST.md` and `CLAUDE.md`; CA-02 M acceptance corrected to ≥ 57 px at 360, no label clipped.

**Follow-ups (record only, R9).**
- **Italian dates:** the Edit page and the Add dialog format the date with `en-US` (`DateFormatter('en-US', …)`), so Italian shows "October 15, 2026".
  Localise it from the active locale.
- **Transaction title is capped at 25 characters** (`transactions.title` `string(25)`, `max:25` in both requests, `maxlength="25"` in the forms), so the
  design's "Trenitalia — cancelled train" cannot be entered. Widen it with the O-03 pattern (migration + `max:` in the requests + forms + tests) in the
  settle-up branch.

**Delta check (2026-10-15, at `7c7f81d`).** DemoSeeder re-run first; nothing saved through the browser. Built assets (`public/hot` set aside:
the Vite dev server fails "Failed to fetch dynamically imported module"), Debugbar hidden for the shots, no console errors. 96 shots in
`.verify/app/categories-delta/` (375×812 and 360×800 × dark/light × EN/IT), measurements in `measurements.json`.
- **CA-13 M / CA-14 M: PASS (re-confirmed).** Edit page, income "Sold items" (pinned first, selected, `+€` in `--pos`) and expense "Travel",
  each also flipped to the other type (pick cleared, `--neg` notice). Picker 335 px at 375 / 320 at 360, tiles 62.2 / 59.2 px, **0 truncated labels
  in 32 states**, no horizontal scroll. Compared with `desktop-frame-09-…-{dark,light}-{en,it}`, `desktop-frame-10-…`, `desktop-frame-06-…-it`.
- **Phone "All" sheet while filtering: PASS.** Expense: full 16 → "tra" 2 → "spesa" 1 → "zzz" none; income: full 11 → "vin" 2 (Winnings + Sold items
  via the "vinted" alias) → "stipendio" 1 → "zzz" none. Sheet height constant in every state (**expense 493 px, income 407 px**, both widths),
  filter not focused on open, no label truncated. Compared with `desktop-frame-07-dark-income-all-sheet-open-*` and
  `desktop-interactive-phone-all-sheet-filtered-*` ("re" gives the design's same 9 tiles).
- **Mutation scope (report only).** `test:mutate` = `pest --no-tia --parallel --mutate --min=90`, **no `--everything`** (not in `composer.json`,
  `tests/Pest.php` or CI). Pest only mutates classes named in `covers()` / `mutates()` and runs only the tests that declare them: the run says
  "1771 Mutations for **60 Files**" (725 tests). 37 of the 97 class-likes in `app/` are named by no test, so they get no mutants; 2 of them were added
  on this branch (`ResetActivityCommand`, `CategoryType`). The third class added here, `Rules/CategoryMatchesType`, is named (R9).
- **Gate:** `composer check; echo "exit: $?"` → `exit: 0`. Vitest 61 passed; Pest 904 passed (3,900 assertions), coverage **99.8 %**; mutation
  **100.00 %** (1,771 mutations, 3 timeouts); browser 49 passed.
- **Verdict: merge.** No CA row open.

**Follow-up (record only): `transactions.created_by`.** Frequent categories count on `transactions.party_id` (the payer, or the
receiver of an income), because no column records who created a row. Add `created_by` and switch `frequentCategories()` to it.

**Follow-up (record only) — FIRST task of the next branch: mutation scope.** 37 of the 97 classes in `app/` are never mutated, because
`test:mutate` mutates only classes named in `covers()` / `mutates()` and does not use `--everything`. (`ResetActivityCommand` was one of the
37; it is now covered by its existing test: 24 mutations, 100.00 %, so 36 remain.) Priorities: `GetTallyPerPersonAction`, `TallyObserver`,
`TallyUserObserver`, `TransactionObserver`, `PreferencesController`, `DeleteUserAccountAction`, `TallyService`. Fix: add `covers()` where
tests exist, plus an architecture test that fails when any class in `app/Actions`, `app/Rules`, `app/Services`, `app/Observers` or
`app/Console` is named by no `covers()` / `mutates()`. Caveat: Pest's matcher may not match enum files (`CategoryType`), so check that
`covers()` on an enum actually produces mutants before relying on it, and exempt enums explicitly if it does not.

**R3 done (2026-10-15).** `TransactionStatsRepository::frequentCategories()` (+ `CategoryType::defaultSlugs()`), `frequent_categories`
in the form data; `categoriesBreakdown()` returns `slug` / `is_uncategorised` (label from i18n), `CategoryBars` adds them to Other.

R1 comes before everything; R2–R4 only need R1; R5 needs R1 (slugs, colours, labels) and R3 (frequency shape); R6–R7
need R5 and R2.

### Checklist

`D` = desktop 1440×900, `M` = mobile 375×812. Every row is checked in **dark and light**. Refs are relative to
`.verify/design/categories/` (`desktop-` prefix unless noted; `-<theme>[-en]` suffix omitted). Where the design draws a
state at one size only, the other viewport is compared against the same state's anatomy in the drawn frame plus the
responsive rule (inline panel ≥ md, second sheet < md).

| ID | Vp | Item | Design ref | State | Result |
|---|---|---|---|---|---|
| CA-01 | D | Glyph map row in `/dev/design-system`: 16 expense + 11 income + ↓ + ◌, each tinted by its `--cN`, none a colour emoji | `section-s1` (table, strip) | default | [x] PASS (R5, dark + light): tinted by the §7.1 colour (was an index cycle), ↓ `--pos`, ◌ `--text-3`; `desktop/r5-picker-glyph-map-{dark,light}-1440.png` |
| CA-01 | M | same | `mobile-*-section-s1` | default | [x] PASS (R5): `mobile/r5-picker-glyph-map-{dark,light}-375.png` |
| CA-02 | D | Tile states: default, hover, focus, selected, selected + focus, "All N", "All N" open | `tile-states` | all | [x] PASS (R5, dark + light): measured 76 min-height, r14, `9px 4px 8px`, 30/r10/14px square, 10.5/500/−.015em label, 1.5px inset accent ring, 2px accent outline offset 2; `desktop/r5-picker-tile-states-{dark,light}-1440.png` |
| CA-02 | M | same, tile ≥ 65 × 76 | `mobile-*-tile-states` | all | [x] PASS, gap ACCEPTED (R5; owner 2026-10-15): states identical; at 375 a 5-column row inside the 20px sheet gutter is **62 px** wide (design drawn at 393 → 65); `mobile/r5-picker-tile-states-{dark,light}-375.png`. **R8:** inside the Add sheet, desktop Chromium's 10 px classic scrollbar makes it 60.3 px at 375 and 57.2 at 360 (62 / 59 with overlay scrollbars, as on phones). The ACCEPTED note's "≥ 62 px at 360" was arithmetically wrong; the acceptance is corrected to **≥ 57 px at 360, no label clipped** (owner, 2026-10-15). No label truncates at either width. |
| CA-03 | D | Add · Expense · Groceries selected, 5 frequent + All | `frame-01-dark-expense-selected` | selected | [x] PASS (R6, dark + light): row of 5 + All 16, Groceries selected (accent ring), amount/title/tally/date unchanged; `desktop/r6-add-expense-selected-1440-{dark,light}.png` vs `desktop-frame-01-dark-expense-selected-{dark,light}-en` |
| CA-03 | M | Add · Expense · selected, 4 frequent + All | `frame-05-dark-expense-selected` | selected | [x] PASS (R6, 375 + 360, dark + light): 4 + All 16, sheet scrolls, submit sticky; `mobile/r6-add-expense-selected-{375,360}-{dark,light}.png` vs `desktop-frame-05-dark-expense-selected-{dark,light}-en` |
| CA-04 | D | Add · switched to Income · pick cleared, `--neg` notice, nudge | `frame-02-dark-switched-to-income-cleared` | cleared | [x] PASS (R6): Groceries cleared on Expense → Income, `--neg` notice under the row; `desktop/r6-add-switched-cleared-it-1440-{dark,light}.png` vs `desktop-frame-02-dark-switched-to-income-cleared-{dark,light}-it` |
| CA-04 | M | same | `frame-06-light-switched-to-income-cleared` | cleared | [x] PASS (R6, 375 + 360): `mobile/r6-add-switched-cleared-it-{375,360}-{dark,light}.png` vs `desktop-frame-06-light-switched-to-income-cleared-{dark,light}-it` |
| CA-05 | D | Add · Income · Salary selected, amount `+` in `--pos` | `frame-03-light-income-selected` | selected | [x] PASS (R6): Salary selected via "stipendio", `+` and figure in `--pos` (`AmountInput tone`); `desktop/r6-add-income-selected-it-1440-{dark,light}.png` vs `desktop-frame-03-light-income-selected-{dark,light}-it` |
| CA-05 | M | same | `frame-03…` anatomy + `frame-06` row | selected | [x] PASS (R6, 375 + 360): `mobile/r6-add-income-selected-it-{375,360}-{dark,light}.png` vs `desktop-frame-03…` anatomy |
| CA-06 | D | Add · Expense · "All" open inline: filter (focused, `esc` hint), close, 4-col grid, keyboard focus ring in grid | `frame-04-light-expense-all-open-keyboard-focus-in-grid` | open + focus | [x] PASS (R6): All open inline, filter focused with `esc` hint, close, 4-column grid inside the dialog scroll; `desktop/r6-add-all-open-expense-1440-{dark,light}.png` vs `desktop-frame-04-light-expense-all-open-keyboard-focus-in-grid-{dark,light}-en` |
| CA-06 | M | Add · Expense · "All" second sheet (80 %), filter **not** focused | `frame-07-dark-income-all-sheet-open` anatomy | open | [x] PASS (R6): second sheet, filter not focused; `mobile/r6-add-all-open-expense-375-{dark,light}.png` vs `desktop-frame-07` anatomy |
| CA-07 | D | Add · Income · "All" open inline (11 tiles) | `frame-04…` anatomy, income list | open | [x] PASS (R6): 11 income tiles inline; `desktop/r6-add-all-open-income-1440-{dark,light}.png` vs `desktop-frame-04` anatomy |
| CA-07 | M | Add · Income · "All" sheet, title "Income categories" | `frame-07-dark-income-all-sheet-open` | open | [x] PASS (R6): title "Income categories"; `mobile/r6-add-all-open-income-375-{dark,light}.png` vs `desktop-frame-07-dark-income-all-sheet-open-{dark,light}-en` |
| CA-08 | D | Add · empty: "◌ No category", Save enabled | `frame-08-light-income-empty` anatomy | empty | [x] PASS (R6): "◌ No category", Save enabled with amount + title only; `desktop/r6-add-empty-1440-{dark,light}.png` vs `desktop-frame-08-light-income-empty` anatomy |
| CA-08 | M | same | `frame-08-light-income-empty` | empty | [x] PASS (R6): `mobile/r6-add-empty-375-{dark,light}.png` vs `desktop-frame-08-light-income-empty-{dark,light}-en` |
| CA-09 | D | Filter with no match: "No match for “zzz”" | `interactive-desktop-grid-no-match` | no match | [x] PASS (R6): "No match for “zzz”"; `desktop/r6-add-all-no-match-1440-{dark,light}.png` vs `desktop-interactive-desktop-grid-no-match-{dark,light}` |
| CA-09 | M | same, in the second sheet | `mobile-interactive-desktop-grid-no-match` + `frame-07` | no match | [x] PASS (R6): in the second sheet; `mobile/r6-add-all-no-match-375-{dark,light}.png` vs `mobile-interactive-desktop-grid-no-match-{dark,light}` + `desktop-frame-07` |
| CA-10 | D | Filter matches EN, IT and aliases ("re", "stipendio", "ebay") | `interactive-desktop-grid-no-match` anatomy | filtered | [x] PASS (R6): "stipendio" (IT label) finds Salary; EN/alias matching is unit-tested (`pickerLogic.test.ts`); `desktop/r6-add-all-search-stipendio-it-1440-{dark,light}.png` |
| CA-10 | M | same | `interactive-phone-all-sheet-filtered` | filtered | [x] PASS (R6, 375 + 360): `mobile/r6-add-all-search-stipendio-it-{375,360}-{dark,light}.png` vs `desktop-interactive-phone-all-sheet-filtered-{dark,light}` |
| CA-11 | D | Keyboard: row ← → Home End Space; Enter on All opens with filter focused; grid ↑ ↓ ← →, ↑ from top → filter; Enter picks first match; Esc closes → focus on All | `section-s3` rules | interaction | [x] PASS (R6): Pest browser tests for Enter (filter and tile: no submit), Esc (panel, then dialog), tab order and focus return; arrows / grid / Home End were run in R5; filter focused on open, `desktop/r6-add-all-open-expense-1440-*.png` |
| CA-11 | M | Touch: tap All → sheet, filter only focuses when tapped; tap tile picks and closes | `section-s3` rules | interaction | [x] PASS (R6): All opens the sheet with the filter unfocused (`filter focused on open: false` at 375), a tile pick closes it; Pest browser tests at 375 for Enter / Esc / tab order |
| CA-12 | D | Italian labels: hyphenation, 2-line clamp, cleared copy "… è una categoria di uscita: rimossa." | `frame-01…-it`, `frame-02…-it`, `frame-04…-it` | it | [x] PASS (R6): no Italian label truncated in the row or the grid (measured `scrollHeight`/`scrollWidth`, 1440); cleared copy "Spesa è una categoria di uscita: rimossa."; `desktop/r6-add-switched-cleared-it-1440-*.png` vs `desktop-frame-02…-it` |
| CA-12 | M | same | `frame-05…-it`, `frame-06…-it`, `frame-07…-it` | it | [x] PASS (R6): same check at 375 and 360, none truncated ("Resi e rimborsi" wraps to two lines); `mobile/r6-add-switched-cleared-it-{375,360}-*.png` vs `desktop-frame-06…-it`, `desktop-frame-07…-it` |
| CA-13 | D | Edit · Income · saved non-frequent "Sold items" pinned first | `frame-09-desktop-dark-income-sold-items-not-frequent-pinned-first` | pinned | [x] PASS (R7, dark + light): saved "Sold items" (not frequent) pinned first and selected, `+` in `--pos`; `desktop/r7-edit-income-sold-1440-{dark,light}.png` vs `desktop-frame-09-desktop-dark-income-sold-items-not-frequent-pinned-first-{dark,light}-en` |
| CA-13 | M | same | `frame-09…` anatomy | pinned | [x] PASS (R9, 375 + 360, dark + light, EN + IT): below `sm` the Edit page drops the card (no border, background or padding), so the picker is 335 px at 375 / 320 at 360, the Add sheet's width, tiles 62 / 59 px. "Refunds & returns", "Side work", "Stipendio" (after the flip to Income) and "Lavoro extra" are not clipped; `EditTransactionFormTest` asserts it. `categories-final/ca13-edit-income-m-{375,360}-{dark,light}-{en,it}.png` vs `desktop-frame-09-…`. **Delta (`7c7f81d`): PASS re-confirmed**, incl. the flip to Expense; `categories-delta/ca13-*.png` |
| CA-14 | D | Edit · Expense · Travel | `frame-10-phone-light-expense-travel` anatomy | selected | [x] PASS (R7): Travel selected and pinned first; `desktop/r7-edit-expense-travel-1440-{dark,light}.png` vs `desktop-frame-10-phone-light-expense-travel-{dark,light}-en` anatomy |
| CA-14 | M | same | `frame-10-phone-light-expense-travel` | selected | [x] PASS (R9, 375 + 360, dark + light, EN + IT): same cause and fix as CA-13 M; "Groceries", "Trasporti" and "Stipendio" fit whole. The only break is the design's own soft hyphen, "Transpor-tation", drawn in frame 10 and identical in the Add sheet at the same width. `categories-final/ca14-edit-expense-m-{375,360}-{dark,light}-{en,it}.png` vs `mobile-frame-10-phone-light-expense-travel-{dark,light}-en`. **Delta (`7c7f81d`): PASS re-confirmed**, incl. the flip to Income; `categories-delta/ca14-*.png` |
| CA-15 | D | Edit · type flip clears + notice, Save stays enabled, 422 never reached | `section-s4` | cleared | [x] PASS (R7): Pest browser `EditTransactionFormTest` (1440, dark + light): flip clears, `--neg` notice, Save saves with no category (422 never reached); server 422 shows the E1 error state |
| CA-15 | M | same | `section-s4` | cleared | [x] PASS (R7): same Pest browser tests at 375 |
| CA-16 | D | Home Recent: income rows with income glyph/colour, sub = category, `+` `--pos`; Salary "¤ Salary" | `frame-11-home-recent` | data | [x] PASS (R7): income glyph/colour by category, sub = category, Uncategorised ◌ in `--text-3`; `desktop/r7-home-recent-1440-{dark,light}.png` vs `desktop-frame-11-home-recent-{dark,light}` |
| CA-16 | M | same, stacked rows | `frame-11-home-recent` anatomy | data | [x] PASS (R7): stacked rows, tally tag inline; `mobile/r7-home-recent-375-{dark,light}.png` vs `mobile-frame-11-home-recent-{dark,light}` |
| CA-17 | D | Tally detail rows (Refunds & returns ↺, Sold items ⬡) | `frame-12-tally-detail-lisbon-trip-transactions` | data | [x] PASS (R7): Sold items ⬡, Side work ✦, Investments ▲, Gifts ❖ in their colours, `+` in `--pos`; `desktop/r7-detail-{solo,household}-1440-{dark,light}.png` vs `desktop-frame-12-tally-detail-lisbon-trip-transactions-{dark,light}` |
| CA-17 | M | same, stacked | `frame-12…` anatomy | data | [x] PASS (R7): `mobile/r7-detail-{solo,household}-375-{dark,light}.png` vs `mobile-frame-12-…` |
| CA-18 | D | Stats All activity rows (Bonus ✪, Gifts ❖, Investments ▲, Salary ¤) | `frame-13-stats-all-activity` | data | [x] PASS (R7): sub = "Category · date"; `desktop/r7-stats-activity-1440-{dark,light}.png` vs `desktop-frame-13-stats-all-activity-{dark,light}` |
| CA-18 | M | same, stacked | `frame-13…` anatomy | data | [x] PASS (R7): `mobile/r7-stats-activity-375-{dark,light}.png` vs `mobile-frame-13-…` |
| CA-19 | D | Home + Stats By category: no income category, ever (B-16) | Batch 1/4 refs (`../desktop/home-1.png`, `../desktop/stats-0.png`) | data | [x] PASS (R4, 2026-10-15): no income row on either card despite 9 income categories in the month; `desktop/ca19-22-home-1440.png`, `ca19-22-stats-1440.png` |
| CA-19 | M | same | `../mobile/home-2.png`, Stats mobile ref | data | [x] PASS (R4): `mobile/ca19-22-home-375.png`, `ca19-22-stats-375.png` |
| CA-20 | D | Frequent row: `demo-empty` (defaults) vs `demo` (learned order, ≥ 2 uses / 90 days) | `section-s3` text | data | [x] PASS (R8, dark + light): `demo-empty` (a tally created for the check, then re-seeded) shows the design defaults Groceries, Dining Out, Transportation, Bills, Subscriptions / Salary, Side work, Refunds & returns, Gifts, Investments; `demo` shows Groceries, Dining Out, Transportation, Entertainment, Personal Care, the DB's ≥ 2-use order (ties by most recent), income padded with Gifts, Investments. `categories-final/ca20-{defaults,learned}-{expense,income}-d-{dark,light}.png` |
| CA-20 | M | same, 4 tiles | `section-s3` text | data | [x] PASS (R8): 4 + All on both accounts; `categories-final/ca20-{defaults,learned}-{expense,income}-m-{dark,light}.png` |
| CA-21 | D | Uncategorised income row: ↓ in `--pos`, sub = "Uncategorised" (owner, R7) | — (app rule, FP-13) | data | [x] PASS (R7): "Cash from a friend" in `desktop/r7-detail-solo-1440-{dark,light}.png` |
| CA-21 | M | same | — | data | [x] PASS (R7): `mobile/r7-detail-solo-375-{dark,light}.png` |
| CA-22 | D | Home + Stats By category: "Uncategorised" row with ◌ (`--text-3`), rows add up to the total | Batch 1/4 refs (`../desktop/home-1.png`, `../desktop/stats-0.png`) | data | [x] PASS (R4, dark): row anatomy (marker, name, bar, value, pct) identical to the design's rows; ◌ is a hollow ring in `--text-3`, faint on `surface-1` but visible; 950.00 + 346.85 + 184.50 + 158.30 + 149.20 + Other 316.09 = 2,104.94 = Stats total; `desktop/ca19-22-{home,stats}-1440.png` |
| CA-22 | M | same | `../mobile/home-2.png` | data | [x] PASS (R4, dark): same rows at 375, no overflow; `mobile/ca19-22-{home,stats}-375.png` |

## Deferred and accepted decisions (2026-10-15)

- **Leave tally** — **Status: ACCEPTED** (deferred to the settle-up branch). A member may leave only when
  they are settled in every currency; the rule lands with the settle-up flow, which is what makes "settled"
  checkable (see O-08 for the share-history follow-up).
- **In-app accept / decline of invitations** — **Status: ACCEPTED** (deferred to a small branch after
  settle-up). It is the first real use of the notifications bell (DO-01), which stays out until then.
- **Remove member** — **Status: ACCEPTED**. The Share dialog is the deliberate entry point; no separate
  control on Edit tally.
