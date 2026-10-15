# Component Inventory

Every reusable UI pattern in `Tallypal Mobile.html`, mapped to the existing Vue component kit
under `resources/js/components/`. The kit is **shadcn-vue on top of reka-ui v2**, styled with
Tailwind v4 + `class-variance-authority` (CVA).

Legend for **Replaces**:
- **existing** → reuse/restyle a component that already exists.
- **extend** → existing component needs a new variant/size/prop.
- **new** → no equivalent; must be built.

> ## ✅ Design decisions applied
> The **mobile system is the single design language** (editorial removed — see
> `DESIGN_TOKENS.md`). The chrome switches at **`md` (768px)**: **< md** = bottom tab bar +
> bottom sheets; **≥ md** = desktop shell with the existing left **`ui/sidebar`** (rail on
> md–lg, expanded ≥ lg). Headings are **sans everywhere** (no serif), amounts are Geist Mono.
> Each pattern's tablet/desktop behavior below reflects these decisions.

---

## A. Atoms

### A1. Button
- **Source classes:** `.btn`, `.btn-p` (primary), `.btn-g` (ghost/secondary), `.btn-full`.
  Pill radius, `14.5px/600`, padding `14px 22px`, `gap:8px`, `transition .2s`.
  Primary = `--accent` bg / `--accent-ink` text, hover `brightness(1.08)`.
  Secondary = `--surface-2` bg, hover `--surface-3`.
- **States:** default, hover, active, disabled (add), loading (add).
- **Replaces:** **extend** `components/ui/button` (`Button.vue` + CVA `buttonVariants`).
  Existing `default`/`secondary` map to `btn-p`/`btn-g`; radius is already `rounded-full`.
  Add a taller touch size (source `h≈46px` vs kit `default h-9`).
- **Props/slots:** `variant`, `size` (add `touch` ≈ `h-12 px-6 text-[14.5px]`), `as-child`,
  default slot for label + leading `<svg>`.

### A2. Icon button
- **Source classes:** `.icb` (38px circle, `--surface` bg, `1px --line`), back `.dt-hd .bk`,
  sheet `.icb` close.
- **States:** default, hover, active, disabled.
- **Replaces:** **extend** `ui/button` `variant="outline" size="icon"`. Consolidate the
  hand-rolled icon buttons in `WebHeader`, `ThemeToggle`, `tallies/Index` into it.
- **Props/slots:** `variant`, `size="icon"`, `aria-label`, icon slot.

### A3. Avatar (+ stacked group)
- **Source classes:** `.ava` (38px), `.ava.s` (26px), `.ava.xs` (22px); color variants
  `.a/.b/.c/.d` = soft-mix of `--c1/--c2/--c3/--c6`; group `.avs` overlap `-7px` + `2px --surface` ring.
- **States:** image vs initials fallback; ring-on-surface (in group).
- **Replaces:** **existing** `ui/avatar` for singles; **new** `AvatarGroup` for the stack + "+N".
- **Props/slots:** `Avatar` — `size` (`xs` 22 / `sm` 26 / `md` 32 / `lg` 38, **add `xl` 56px**),
  fallback color (`c1..c6`), `src`, `alt`; `AvatarGroup` — `max`, `size`, `items[]`.
- **`xl` (owner decision 2026-10-11, F-083):** 56×56 circle, initial **18px/600/`.02em`**, same
  c-tint recipe as the other sizes (bg `color-mix(in oklch, var(--cN) 26%, var(--surface))`, text
  `--cN`, 1px `color-mix(--cN 30%)` border). Used by the You identity card.
- **No 34px size.** The desktop sidebar user block (F-007) uses the existing `md` (32px).

### A4. Badge / Pill
- **Source classes:** `.pill` (10px/600, `--surface-2`/`--text-3`), `.pill.sh` (accent-mix bg +
  accent text). In tx `.sub`.
- **States:** default (neutral), `shared` (accent), `ok` (`.pill.ok`, `--pos` 16% mix + `--pos` text: owed to you, F-033).
- **Replaces:** **extend** `ui/badge` — add `variant="pill"`, `variant="accent"` and `variant="ok"`.
- **Props/slots:** `variant`, default slot.

### A5. Delta badge (trend)
- **Source classes:** `.delta` — pill, `pos`-mix bg + `pos` text, arrow + `%`.
- **States:** positive (`--pos`), negative (mirror with `--neg`), and `neutral` (no pill: plain 12px `--text-3` text, arrow,
  value and caption kept, as the design draws the Stats delta; FP-11). `pos-neg` is only for a direction that is unambiguous
  for the user (the Home balance delta); a spending delta is always `neutral` (Stats Daily average).
- **Replaces:** **new** `TrendBadge` (or Badge `variant="pos"/"neg"`).
- **Props/slots:** `tone` (required: `pos-neg` / `neutral`), `direction`, `value`, `label`.

### A6. Category / status dot
- **Source classes:** `.dotc` (7px circle, `--cN`/`--pos`/`--neg`).
- **Replaces:** **new** trivial `Dot` (or utility). Low priority.
- **Props:** `color` (`c1..c6`, `pos`, `neg`, `text-3`), `size`.

### A7. Progress bar (inline mini)
- **Source classes:** `.bar > i` (5px track `--surface-2`, filled width% in `--cN`). Category + split rows.
- **States:** value 0–100%.
- **Replaces:** **new** `MiniBar` (no progress component in kit).
- **Props:** `value`, `color`.

### A8. Segmented control
- **Source classes:** `.seg` + buttons, `.on` = `--surface-3`. Pill container. Used for Tallies
  filter, Stats range, Add-tx type.
- **States:** default, active (`on`), hover.
- **Replaces:** **existing** `ui/segmented-control/SegmentedControl.vue` — restyle to mobile.
- **Props/slots:** `v-model`, `options[]` / item slots.

### A9. Stepper (± numeric)
- **Source classes:** `.stp` — pill with `−` / value / `+`; member split %.
- **States:** default, per-button hover, min/max clamp (0–100).
- **Replaces:** **new** `NumberStepper`. ⚠️ Existing `ui/stepper/Stepper.vue` is a reka-ui
  **wizard** stepper — NOT this; don't confuse them.
- **Props/slots:** `v-model`, `min`, `max`, `step` (source `5`), `suffix` (`%`).

### A10. Chip (selectable)
- **Source classes:** `.chips button` (pill `--surface-2`), `.on` = accent; optional leading `.dotc`.
  Category picker + colour/icon picker.
- **States:** default, hover, selected (`on`).
- **Replaces:** **new** `ChoiceChip` / `ChipGroup` (single or multi).
- **Props/slots:** `v-model`, `options[]` (label, dot color, icon), `multiple?`.
- **Categories batch:** no longer used for the transaction category; `CategoryPicker` (E7) replaces it there.

### A11. Tally glyph / tx icon (soft-tinted square)
- **Source classes:** `.tally .glyph` (34px r11), `.tl .glyph` (44px r15), `.tx .ic` (42px r14);
  bg `color-mix(--cN 20–22%, transparent)`, colored glyph.
- **Replaces:** **new** `CategoryGlyph` (tinted square holding emoji/char or lucide icon).
- **Props:** `color` (`c1..c6`), `size`, `glyph`/icon slot.
- **Sizes (detail header, F-054/F-055, owner decision 2026-10-13):** `xl` = 48px / r16 / 20px glyph
  (desktop `.dt-hd .glyph`), `header` = 40px / r13 / 17px glyph (mobile `.dt-hd .glyph`); both 22% fill.
  Used by the tally detail header (`DetailHeader` `#glyph` slot on mobile, inline on `md+`).
- **Sidebar row (F-010, 2026-10-13):** `xs` = 22px / r7 / 11px glyph, 22% fill (design `.snv .g`); used by the
  per-tally list in `AppSidebar`.
- **Category → glyph mapping (F-037, decided 2026-10-11):** keyed on `categories.category_id`
  (never the derived two-letter `category_code`); full table in VERIFICATION.md F-037.
- **Glyphs render as text, tinted by CSS `color`** (`--cN`), never as colour emoji. Every glyph
  that has an emoji form (`Emoji=Yes` in Unicode `emoji-data.txt`: today ✂ ☕ ♥ ✈) is stored
  with **U+FE0E** (VARIATION SELECTOR-15) appended. Any glyph added later follows the same rule.
  The glyph run verifies this with screenshots at 375×812 and 1440×900.
- **Income categories (Categories batch):** 11 more glyphs (¤ ✦ ✪ ▲ ◫ ↺ ❖ ⬡ ⊚ ⚄ ⊕), none with an emoji form. The map is
  re-keyed on the category `slug` (C-D2, approved 2026-10-14; supersedes the `category_id` key above); ↓ stays for an **uncategorised** income only. Colour comes from
  the category's persisted `color` (DESIGN_TOKENS §7.1). Table: VERIFICATION.md, Categories batch.

---

## B. Molecules / rows

### B1. Screen header
- **Source classes:** `.hdr` — `h1` (28px sans) + `.hdr-sub` + right cluster (theme `.icb`, avatar).
- **Replaces:** **extend** `pages/home/sections/HomeHeader.vue` to the sans mobile style.
- **Props/slots:** `title`, `subtitle`, right-actions slot.
- **Tablet/desktop (#11):** **sans everywhere**, size adapts up (e.g. 28px → ~`text-3xl/4xl`
  at ≥ md). No serif.

### B2. Detail header (with back)
- **Source classes:** `.dt-hd` — round back button + name/sub + trailing `.icb`.
- **Replaces:** **new** `DetailHeader`.
- **Props/slots:** `title`, `subtitle`, `back-href`, actions slot.
- **Tablet/desktop (#15):** back-arrow on mobile; **breadcrumbs at ≥ md** (reuse
  `components/Breadcrumbs.vue`). `DetailHeader` renders back-arrow < md, breadcrumb ≥ md.

### B3. Section title / "see all" link
- **Source classes:** `.sec-t` — uppercase mono eyebrow + optional trailing link.
- **Replaces:** **existing** `.eyebrow` utility wrapped in a small **new** `SectionHeader`.
- **Props/slots:** `label`, action slot / `action-href`.

### B4. Balance hero
- **Source classes:** `.bal` — label + `.amt` (52px, `.cur` + int + `.dec`) + `.delta`. Reused
  on tally detail as "Total spent".
- **Replaces:** **new** `BalanceHero` (replaces `HomeBalanceGrid`'s 3-cell editorial grid).
- **Props/slots:** `label`, `amount`, `currency`, `decimals`, `delta?`.
- **Tablet/desktop (#12):** **single big hero at ≥ md** (same component, larger type). `InOutCards`
  (B5) sit beside/under it; do not revert to the editorial 3-cell grid.

### B5. In/Out split cards
- **Source classes:** `.io` grid (2 cards), `.l` (dot + label) + `.v` (22px; `.p`=pos).
- **Replaces:** **new** `InOutCards`.
- **Props:** `income`, `expense`, `currency`.
- **Tablet/desktop:** stays two cards; may widen beside the hero.

### B6. Quick actions
- **Source classes:** `.quick` — 4-col grid of vertical buttons (icon circle + label).
- **Replaces:** **new** `QuickActions`.
- **Props/slots:** `actions[]` (icon, label, handler/href, primary?).
- **Tablet/desktop (#16):** ~~keep the 4-up grid at desktop~~ **superseded** by the Desktop design
  (F-024): a titled card with a 2×2 grid at every width.
- **Action set (owner decision 2026-10-11, F-025):** **Add / Split / Settle / New tally**, in that
  order. You is dropped (it is reachable from the tab bar and the sidebar user block). Import is cut
  (DO-02). Settle needs B-04's `settle_suggestion` and stays out until B-04 lands; until then the
  card shows Add / Split / New tally.

### B7. Tally rail card (horizontal)
- **Source classes:** `.tally` (min-w 172, r20) — glyph + avatar stack + name/mem + amount;
  `.tally.new` dashed add card. Container `.rail` horizontal scroll.
- **States:** default, hover, `new`.
- **Replaces:** **new** `TallyRailCard` (+ `TallyRail` scroller for mobile).
- **Props/slots:** `tally` (name, member/desc, amount, glyph, color, members[]), `variant`, `amountCaption`
  (the amount's period, e.g. "spent in Oct"). A per-tally amount is always spending, neutral (FP-01).
- **Tablet/desktop (#13):** Home "Your tallies" becomes a **grid at ≥ md** (reuse the Tallies
  card grid look). Rail < md, grid ≥ md.

### B8. Tally list row
- **Source classes:** `.tl` — glyph + name/sub(avatars + meta) + right (value + status). `.st.owe`
  in `--pos`.
- **States:** default, hover; status (settled / owes / count / archived).
- **Replaces:** **new** `TallyListRow` (< md).
- **Props/slots:** `tally`, right-status slot, `amountCaption` (e.g. "total spent"). The amount is
  spending, neutral, never `--pos` (FP-01).
- **Tablet/desktop (#14):** at **≥ md render the existing bordered card grid**
  (`tallies/Index.vue`) **restyled to the new tokens** — full-width rows only < md.

### B9. Transaction row
- **Source classes:** `.tx` — icon glyph + `.mid` (name + sub w/ pill) + `.amt` (`.p`=pos);
  `1px --line` dividers; grouped by `.day`.
- **States:** default, hover; pos/neg; with/without pills.
- **Replaces:** **new** shared `TransactionRow` (extract from `HomeRecentTransactions` +
  `tallies/transactions/Index`).
- **Props/slots:** `transaction` (title, date, category, amount, split label, glyph color),
  `decimals`, `clickable`.

### B10. Day group header
- **Source classes:** `.day` — date left + running total right (mono).
- **Replaces:** **new** `TxDayHeader`.
- **Props:** `label`, `total?`.

### B11. Category breakdown row
- **Source classes:** `.cat` — dot + name + `MiniBar` + value + pct.
- **Replaces:** **existing** `HomeCategoriesBreakdown.vue` restyled, using `MiniBar` + `Dot`.
- **Props:** `category` (name, color, amount, pct).

### B12. Split member row (detail)
- **Source classes:** `.spl` — avatar + name + `MiniBar` + value + pct. "Split so far".
- **Replaces:** **new** `SplitMemberRow`.
- **Props:** `member` (avatar, name, amount, pct, color).

### B13. Settle banner
- **Source classes:** `.settle` — accent-soft box, "X owes you €Y" (amount in accent) + `Remind`.
- **States:** owes-you / you-owe / settled.
- **Replaces:** **new** `SettleBanner`.
- **Props/slots:** `whoOwes`/`amount`, action slot.

### B14. Chart — vertical bars
- **Source classes:** `.chart` (bars `.b`, active `.b.on`) + `.axis`. Stats screen.
- **Replaces:** **existing** `ui/chart` (@unovis) wrappers + `HomeWeeklyHistory.vue`, restyled.
- **Props:** `bars[]` (height %, active), `axis[]`.
- **Tablet/desktop (#17):** **unify on @unovis across all breakpoints** — drop the CSS-bar
  approach; restyle @unovis to mobile tokens (bar `--surface-3`, active `--accent`, axis mono).
- **Desktop values (owner decision 2026-10-11, F-073):** bar radius `--radius-bar`
  (`6px 6px 2px 2px`); axis `font-mono text-axis` (10.5px, `.04em`, `--text-3`).
- **Axis labels (F-073):** **≤ 7 buckets → label every bar. > 7 buckets → 6 labels** at evenly
  spaced indexes that always include the first and the last: `round(i × (n − 1) / 5)` for
  `i = 0…5` (12 buckets → 0, 2, 4, 7, 9, 11). Unlabelled bars keep their slot; only the text is
  omitted.

### B15. Card header  ★ new (Desktop design `.card-h`)
- **Source classes:** `.card-h` + `.card-h h3` + `.card-h a`, as the first child inside a `.card`.
- **Values:** flex, `align-items:center`, `justify-content:space-between`, `margin-bottom:14px`.
  Title is an **`h3`**, 15px/600/`-.02em`, `--text`, no margin. Optional trailing link
  12.5px/500, `--accent`, hover `--text`.
- **States:** title only; title + link; title + link + trailing meta (e.g. a month label).
- **Replaces:** **new** `CardHeader` (`components/ui/card-header/`). It does **not** replace
  `SectionHeader` (B3), which stays the uppercase eyebrow row *outside* a card (Home "Your
  tallies", sidebar "Tallies"). Any section that the design draws as a card swaps its
  `SectionHeader` for a `CardHeader` inside the card.
- **Props/slots:** `title`, `as` (default `h3`), `#action` slot (link/button), optional
  `actionHref` + `actionLabel` shorthand.
- **Used by:** F-036 (Home Recent → "All activity"), F-039 (Home By category → "Details"), F-063
  (Tally detail Split so far → "Edit split"), F-065 (Tally detail Transactions), F-076 (Stats By
  category), F-085 (You Preferences / Data cards).

### B16. Setting row  ★ new (Desktop design `.sw`)
- **Source classes:** `.sw`, `.sw .mid`, `.sw .nm`, `.sw .em`.
- **Values:** flex, `align-items:center`, `gap:12px`, padding `13px 0`, `border-top:1px --line`;
  the first row in a card has no border and `padding-top:4px`. Title 13.5px/500/`-.01em` `--text`;
  sub 11.5px `--text-3`, `margin-top:2px`; `.mid` is `flex:1`; the control sits on the right with
  `flex-shrink:0`.
- **States:** default; `tone="neg"` (control text `--neg`, e.g. Sign out / Delete account);
  with / without a sub; row as a link (whole row navigates, trailing chevron in `--text-3`).
- **Replaces:** **new** `SettingRow` (`components/ui/setting-row/`). Closest existing pattern is
  `.mem-row` (E3: 11px padding, 14px name), which stays separate.
- **Props/slots:** `title`, `description`, `tone` (`default` | `neg`), `href` (link row),
  `#control` slot.
- **Used by:** F-085 (You → Preferences: Theme, Default currency; Data: Sign out, Delete account;
  Account: Password). The You page rows are listed in F-082.

### B17. Data table  ★ new (Desktop design `.tbl`)
- **Source classes:** `.tbl`, `.tbl .th`, `.tbl .tx` (`display:contents`), `.tbl.cols3`, `.cell`,
  `.hide`, `.amt`.
- **Values:**
  - Grid `minmax(0,2fr) minmax(0,1fr) minmax(0,1fr) auto` (4 cols) or
    `minmax(0,2fr) minmax(0,1fr) auto` (`cols3`), `column-gap:16px`.
  - Header cell: **uppercase** 11px/600/`.06em`, `--text-3`, padding `0 0 10px`,
    `border-bottom:1px --line`; `.r` right-aligns (amount column).
  - Body cell: padding `12px 0`, `border-bottom:1px --line` (none on the last row), flex,
    centered, `min-width:0`. Secondary cells 13px `--text-2`. Amount mono 14.5px/600/`-.025em`,
    right-aligned, `--pos` when positive.
  - First cell = `TransactionRow` anatomy: 40px glyph (radius 13px), `gap:13px`, name
    14px/500/`-.015em` (ellipsis), sub 12px `--text-3`.
  - **Hover:** a `--surface-2` band across the whole row (every cell, `padding-box`), same
    band as F-041.
- **Responsive:** `≤ 1100px` the `.hide` columns drop (4 → 3 columns). **`≤ 640px` it collapses
  to stacked rows:** no header, each row a flex line (`gap:12px`, `12px 0`, `--line` divider),
  first cell `flex:1`, amount `flex-shrink:0`, secondary cells hidden, the tally pill moves
  inline into the sub (`.mp`, `margin-left:6px`). No hover band on phones.
- **Replaces:** **new** `DataTable` (`components/ui/data-table/`), rendering `TransactionRow`
  content in its first cell. `TransactionRow` (B9) stays the stacked-row form.
- **Props/slots:** `columns[] { key, label, align?, hideBelow? ('xl'=1100) }`, `rows[]`,
  `rowHref?`, a `#cell-{key}` slot per column.
- **Used by:** F-036 (Home Recent: Transaction / Tally / Date / Amount), F-065 (Tally detail:
  Item / Paid by / Date / Amount), F-069 (same, phone layout). B-09's All activity (F-077) reuses
  it as `cols3` once the data exists.

---

## C. Layout & navigation

### C1. Bottom tab bar (+ center FAB)  ★ mobile primary nav
- **Source classes:** `.nav` — fixed bottom, 5 slots: Home / Tallies / **FAB** / Stats / You;
  active `--accent`; blurred translucent bg; `.fab` = 52px accent circle + glow. Content reserves
  bottom padding; `.home-ind` is demo chrome (skip).
- **States:** per-tab active/inactive; FAB hover.
- **Replaces:** **new** `BottomNav` + `BottomNavItem`. Active from `usePage().url`; FAB opens
  the Add-tx sheet.
- **Props/slots:** items (`icon`, `label`, `route`, `active`), center FAB action.
- **Tablet/desktop (#7/#8):** **hidden at ≥ md.** Rendered only `< md`. Add
  `env(safe-area-inset-bottom)` padding.

### C2. Desktop shell — left sidebar (≥ md)
- **Replaces:** **existing** `ui/sidebar/*` + `AppSidebar.vue` + `layouts/app/AppSidebarLayout.vue`
  (today used only for admin `Dashboard`). Reuse as the **app-wide desktop nav ≥ md**, restyled to
  mobile tokens; nav items = Home / Tallies / Stats / You (+ admin when Super Admin).
- **Tablet/desktop:** **rail/collapsed on md–lg, expanded ≥ lg** (reka sidebar supports this).
  The "Add transaction" entry at ≥ md is a **primary button in the sidebar/header that opens the
  Add-tx dialog** (D1), replacing the mobile FAB.

### C3. App screen shell
- **Source classes:** `.screen` — scroll container, `px-5`, bottom padding to clear nav.
- **Replaces:** **extend** `layouts/WebLayout.vue` into the **single responsive shell** (#9):
  `< md` → content + `BottomNav` + bottom padding, no top header on primary tabs; `≥ md` →
  `ui/sidebar` shell. One layout, responsive chrome (no separate `MobileTabLayout`).
- **Props/slots:** default content slot, header slot, `activeTab`.

### C4. Status bar (`.status`) — **demo chrome, do NOT build.**

---

## D. Overlays

### D1. Bottom sheet (+ scrim + grabber)  →  desktop dialog
- **Source classes:** `.sheet` (fixed bottom, `radius-lg` top, slide-up
  `cubic-bezier(.32,.72,0,1)`, `max-h 88%`), `.grab`, `.sh-hd`, `.sh-body`, `.scrim`. Used for
  **Add transaction** and **New tally**.
- **States:** closed / open; scrim on/off.
- **Replaces:** **extend** `ui/sheet` with `side="bottom"` + grabber + rounded top (mobile);
  **`ui/dialog`** for the same content ≥ md.
- **Props/slots:** `v-model:open`, `title`, header/body/footer slots.
- **Tablet/desktop (#18):** **bottom sheet < md, `ui/dialog` modal ≥ md** (recommended). Keep the
  full-page routes (`tallies/Create`, `transactions/Create`) as deep-link fallbacks. Provide a
  `useAddTransaction()` / `useNewTally()` opener that picks sheet vs dialog by viewport.

### D2. Modal (share)
- **Replaces:** **existing** `components/tallies/TallyShareDialog.vue` (on `ui/dialog`) — restyle
  to mobile tokens; on `< md` present it as a bottom sheet variant.

---

## E. Forms

### E1. Field (label + control)
- **Source classes:** `.fld` (uppercase mono label + control), `.fld-2` (2-col); inputs/selects
  are **filled** (`--surface-2` bg, `radius-sm`, focus `--accent`).
- **States:** default, focus, placeholder, (add) error, disabled.
- **Replaces:** **existing** `ui/input`/`ui/label`/`ui/select`/`ui/textarea` restyled to the
  filled look; **new** `FormField` wrapper (label + control + error via `InputError.vue`).
- **Props/slots:** `label`, `id`, error slot; control via default slot.
- **Error state (F-105; the design draws none, values mirror the focus rule):**
  - Control: `border-color: --neg` (1px, the same border slot focus turns `--accent`); focus
    keeps `--neg` while the error stands.
  - Message: 12px (`--text-meta`) `--neg`, `margin-top:6px`, rendered by `FormField` through
    `InputError`, replacing today's 14px line.
  - A11y: the control gets `aria-invalid="true"` and `aria-describedby` → the message id.
  - Server errors only: forms keep native `required` off the inputs (or `novalidate` on the form)
    so the browser's unstyled bubble never shows. Add-tx keeps its disabled-until-valid submit.

### E2. Amount entry (big)
- **Source classes:** `.amt-in` — centered 46px amount (`.cur` + value + `.z` faded) + hint.
- **Replaces:** **new** `AmountInput` (tap-to-edit big numeric).
- **Props:** `v-model`, `currency`, `decimals`.

### E3. Member/split editor
- **Source classes:** `.mem-row` (avatar + name/email + `.stp`) + `.tot` (`.bad` when ≠ 100) +
  "Invite someone" ghost button.
- **Replaces:** **new** `SplitEditor` composing `Avatar`/`AvatarGroup` + `NumberStepper`.
- **Props/slots:** `members[]` (v-model of percentages), invite action.
- **Where (owner decision 2026-10-11):** **Share dialog only.** B-12 is cut, so New tally has no
  "Members & split" block and no "Invite someone"; it creates a solo tally and splits are edited
  afterwards through Share.
- **Extras (values recorded in F-096, used by F-097):**

  | Part | Values |
  |---|---|
  | Container `.split` | `--surface-2`, `--radius-sm` (14px), padding `12px 14px` |
  | Head | flex, space-between, `margin-bottom:12px`, `gap:10px` |
  | Total | label 12px `--text-3`; value mono/600 `--pos`, `--neg` when ≠ 100 |
  | **Split bar** | 8px tall, pill radius, `--surface-3` track, `gap:2px`, `margin-bottom:4px`; one segment per member in its `--cN`, `transition: width .25s` |
  | Row | `gap:10px`, padding `10px 0`, `--line` divider; 26px avatar; name 13.5px/500/`-.01em` (ellipsis); sub 11.5px `--text-3` |
  | **% stepper** `.pct` | `--surface` pill, `2px` padding; 26px round ± in `--text-2`, hover `--surface-3`; input 34px wide, mono 13px/600, right-aligned; unit `%` 12px mono `--text-3` |
  | **Lock** | 28px transparent circle, `--text-3`, hover `--surface-3`/`--text`; locked `--accent`; `visibility:hidden` in Equal mode |
  | Equal mode | steppers `opacity:.45`, inert |
  | **Small segmented** `.seg-s` (Equal / Custom) | `--surface` track, `2px` padding; buttons `6px 12px`, 12px |
  | Hint | 11.5px `--text-3`, `margin-top:10px`, `line-height:1.45` |

  - **Split bar:** **new** `SplitBar` (`segments[] { value, color }`); `MiniBar` (A7) is a
    single-value bar and is not stretched to this.
  - **% stepper:** **extend** `NumberStepper` (A9) with a `size="sm"` (`.pct` values above) and an
    editable mono input; keep `step`/`min`/`max`/`suffix`.
  - **Lock:** **new** `SplitLockButton` (icon button, `aria-pressed`); a locked row keeps its value
    when another row changes.
  - **Small segmented:** **extend** `SegmentedControl` (A8) with `size="sm"`.
  - Pending invitees are listed in Members only (F-099). They get **no** split row: their share
    exists only after they join (B-12 cut).

### E4. Colour & icon picker
- **Source classes:** New-tally "Colour & icon" `.chips` (dot color + glyph). Sets the tally's
  persisted `color`/glyph (decision #5).
- **Replaces:** **new** — a `ChipGroup` (A10) configured with `c1..c6` swatches + glyph options.

### E5. Toggle / switch — **not needed** (owner decision 2026-10-11)
- **Source:** the design's `.tg` switch (40×23 track, 17px knob) appears in four places, and none
  survives the decisions:
  - You → "Dark theme" (F-086): replaced by a `PillSelect` (Light / Dark / System), see E6.
  - You → "Balance reminders": DO-05, left out.
  - Share → "Link requires approval" (F-101, DO-09): cut.
  - Share → "Members can settle up" (F-102, DO-10): cut.
- **Surviving findings that use it:** **none**. Do not build `ui/switch`. The `.tg` values stay
  recorded in F-086 in case a boolean toggle appears later.

### E6. Pill select  ★ extend (Desktop design `.role`)
- **Source classes:** `.role` (`.fld .role`).
- **Values:** `width:auto`, `flex-shrink:0`, `--surface-2` bg, no border, `--radius-pill`,
  `--text-2` 12px/500 sans, padding `7px 26px 7px 11px`; 11px chevron at `right 9px` in
  `--text-3` (the design's hardcoded `#888` stroke becomes the token); hover/focus `--text`.
- **States:** default, hover, focus, open, disabled.
- **Replaces:** **extend** `ui/select` — a `pill` variant on `SelectTrigger` (today
  `default | filled`), with the existing `SelectContent`/`SelectItem` menu. No native `<select>`.
- **Props/slots:** `SelectTrigger variant="pill"`; the rest is the stock `Select` API.
- **Used by:**
  - F-087: You → Preferences → "Default currency" (EUR € / USD $ / GBP £ …, plus "None"), B-10.
  - F-086: You → Preferences → **"Theme"** row (D4): **Light / Dark / System**, bound to
    `useAppearance().updateAppearance`. It replaces the design's two-state toggle because the
    settled default is `system`.
  - Not the Share role select: member roles are cut (DO-08).

### E7. Category picker  ★ new (`Tallypal Categories.html`, Categories batch)
- **Source classes:** `.cat-row`, `.cat-grid`, `.ct` (`.g` glyph, `.l` label), `.ct.on`, `.ct.more` (`.open`),
  `.cat-st` (`.warn`), `.cat-panel`, `.cp-hd`, `.cp-q` (+ `.k` kbd hint), `.cat-empty`; phone `.sheet2`.
- **Anatomy:** a radio-group **row** of frequent tiles + an "All N" tile (N from data: "All 16" / "All 11"); a **status line** under it; "All" opens the full
  set as a 4-column **glyph grid** with a **filter** field and a close button — inline panel under the row **≥ md**, a
  second bottom sheet (80 % height, title "Expense categories" / "Income categories") **< md**.
- **Values:** DESIGN_TOKENS §7.2. Tile glyph colour = the category's stored `color` (§7.1 rule).
- **States:** tile default / hover / focus / selected / selected + focus; "All" default / open; status empty
  ("◌ No category") / cleared (`--neg`, nudge) / none (a pick is shown); grid filtered / no match ("No match for “q”").
- **Behaviour:**
  - Row = 5 frequent (≥ md) or 4 (< md) for the current type, padded with the design defaults; the selected category, if
    not among them, is pinned first (Edit shows the saved one this way).
  - `type` change: row swaps; an invalid selection is cleared (`modelValue` → `null`) and the status names it until the
    next pick.
  - Filter matches EN label, IT label and the slug's aliases (frontend catalogue), case-insensitive; Enter picks the
    first match. Auto-focused on open **only ≥ md**.
  - Keyboard: row ← → Home End (roving tabindex), Space/Enter picks; Enter on "All" opens; grid ↑ ↓ (±4) ← →, ↑ from the
    first row → filter, ↓ from the filter → first tile; Esc closes and returns focus to "All". Picking closes the panel and
    focuses the picked tile in the row.
- **Replaces:** **new** `ui/category-picker/CategoryPicker.vue` (+ `CategoryTile.vue`). Replaces `ChipGroup` (A10) for
  categories in Add transaction (sheet / dialog / Create page) and the Edit page. Reuses `CategoryGlyph` colours, `Input`
  styles for the filter, `IconButton` for close, `ui/sheet` `side="bottom"` for the phone panel.
- **Props/slots:** `v-model` (`number | null` category id), `type: 'expense' | 'income'`, `categories:
  {category_id, slug, type, color}[]`, `frequent: {expense: string[], income: string[]}`; emits `cleared(slug)`.
  Labels via `t('categories.<slug>')`. **Built (R5):** also `v-model:open`, `v-model:query` (optional) and
  `layout: 'auto' | 'inline' | 'sheet'` (`auto` follows md; the others pin a shape for `/dev/design-system`). `CategoryTile`
  takes `preview: 'hover' | 'focus'` for static state swatches only. Rules in `ui/category-picker/pickerLogic.ts`.
- **Used by:** Categories batch R6 (Add transaction), R7 (Edit). Checklist CA-01..CA-15.

---

## F. Empty / loading / error states (design required — decision #20)

Not in the mock; must be designed on the mobile system for **every** screen:
- **Empty:** Home (no tallies), Tallies (none), Stats (no data), Tally detail (no tx). Rework
  `HomeEmptyState.vue` and the empty branch of `tallies/Index.vue` to the new tokens.
- **Loading:** use `ui/skeleton/Skeleton.vue` with pulsing skeletons for Inertia v3 deferred
  props (per project Inertia guidelines) on each tabbed screen.
- **Error:** restyle `pages/ErrorPage.vue`.

### F1. Empty state  ★ new (F-103; the design draws none, values from existing tokens)
- **Values:**
  - Surface: a `--surface` card, `--radius-card` (20px), padding `20px` (`16px` ≤ 640px),
    content centered, `gap:6px` between title and body, CTA `margin-top:14px`.
  - Title: 15px/600/`-.02em` `--text` (the `CardHeader` h3 step), **always smaller than the page
    h1** above it. Plain sentence, no trailing period (the editorial voice is gone).
  - Body: 13px (`--text-hdr-sub`) `--text-3`, max ~44ch.
  - CTA: one primary `Button` (`btn-p`), optional.
- **States:** with / without CTA.
- **Replaces:** **new** `EmptyState` (`components/ui/empty-state/`), replacing the three
  hand-rolled copies: `HomeEmptyState.vue`, the empty branch of `tallies/Index.vue`,
  `StatsEmptyState.vue`. Also the right shape for the Tally detail "no transactions" card (ES-04).
- **Props/slots:** `title`, `description`, `#action` slot (or `actionLabel` + `actionHref` /
  `@action`).
- **CTA target rule (F-104):** an empty Stats range opens Add transaction when the user already has
  a tally; "New tally" only when they have none.

---

## G. Screens with NO mobile mock — re-skin to the mobile system (editorial removed)

Under decision #1 these also move onto the mobile tokens/components. No mock exists, so each
needs design as part of its Phase-4 batch (not "keep editorial"):

- **Marketing/landing:** now **designed** — `Tallypal Site.html`. Moved out of this list; see
  **§H** for its components and `MIGRATION_PLAN.md` Batch 4.3 for the build order. It keeps its
  own header/footer and does **not** use the app shell.
- **Admin:** `pages/Dashboard.vue`, `AppSidebar*`, `ui/sidebar/*`, `layouts/app/*` — the sidebar
  is now the **app-wide desktop shell** (C2), restyled.
- **Auth:** `pages/auth/*`, `layouts/auth/*`, `AuthLayout.vue` — re-skin to tokens; ensure touch
  targets.
- **Settings ("You" tab):** `pages/settings/*`, `layouts/settings/Layout.vue`, `AppearanceTabs`,
  `DeleteUser`, `LanguageSwitcher`.
  **Now designed (Desktop `profile` screen) — owner decisions 2026-10-11 (F-082, F-084):**
  - **You is a hub page** (one page on the 12-col grid: identity card c5 beside a c7 stack of
    Preferences and Data cards). The tab bar's You and the sidebar user block both lead here.
  - The **existing settings pages stay** as destinations, unchanged in role: Profile (Name/Email
    form), Password, Appearance.
  - Identity card (`Avatar size="xl"`, name, email): **"Edit" → the existing profile settings page**.
  - **Route:** the hub gets its own new route. The bottom-nav **You** tab (C1, today
    `profile.edit`) and the sidebar user block (C2a) both link to it; the tab stays active on the
    hub and on the settings pages under it.
  - Rows (`SettingRow`, B16):
    - Preferences: "Theme" (`PillSelect` Light / Dark / System), "Default currency" (`PillSelect`,
      B-10).
    - Account: "Password" → the existing password page (link row).
    - Data: "Sign out" (`tone="neg"`, F-088), then **"Delete account"** (`tone="neg"`) at the bottom
      of the Data card, below Sign out, opening the existing `DeleteUser` confirmation dialog.

### C2a. Sidebar user block (F-007; owner decision 2026-10-11)
- Do it **after F-088** (Sign out on You, done 2026-10-11).
- The block becomes a **plain link to the You hub route** (§G), not `settings/profile`: `Avatar size="md"` (32px, c1 tint), name, email
  (ellipsis), above a `--line` top border. The `NavUser` dropdown (`UserMenuContent`) is
  **removed** from the sidebar; Sign out lives on You.
- **Misc primitives:** `Breadcrumbs` (now the ≥ md detail nav), `Heading`/`HeadingSmall`,
  `TextLink`, `Icon`, `AppLogo`, `seo/*`, `PlaceholderPattern`, `NavMain/NavUser/NavFooter`,
  `UserInfo`, `UserMenuContent` — token pass.

---

## H. Marketing (source: `Tallypal Site.html`)

The landing page. Tokens for everything here are in `DESIGN_TOKENS.md §8`; the page reuses the
app's color, font and radius tokens verbatim and adds only display type, a container, two glows,
a soft hairline, an inset surface and the device frame.

> ### Decisions — separate context, own shell (#M1, `MIGRATION_PLAN.md` Batch 4.3)
> Where the site and the app disagree, they **stay** different: `--text-kicker` is marketing-only,
> the header blur is not the app's `--blur-nav`, and the page keeps the source's own breakpoint
> ladder (880/900/820/760/520) instead of the app's `md` switch.
>
> ### Shell — the marketing page does **not** use the Phase 3 app shell
> The design ships its own chrome: a sticky blurred **top** header (brand + anchor nav + theme
> toggle + Log in + Get Tallypal) and a four-column **footer**. There is no bottom tab bar and no
> sidebar anywhere on the page — the only bottom nav in the design is *drawn inside the phone
> mock*, as a picture of the app. So `Welcome.vue` keeps a standalone `MarketingLayout`
> (header + `<main>` + footer), and `WebLayout` (sidebar/bottom-nav/overlays) is for signed-in
> screens only. This costs nothing: `WebHeader.vue` is already used by `Welcome.vue` alone, so the
> marketing header replaces it rather than forking it.

### H1. Marketing layout
- **Source:** `body` → `header.chrome` + `main#top` + `footer`; `html { scroll-behavior: smooth }`.
- **States:** guest (design's default) / signed-in (**#M5 — keep today's behavior**: the header
  carries the app nav links + avatar dropdown exactly as `WebHeader` does now, restyled to the
  site chrome; no redirect away from `/`).
- **Replaces:** **new** `layouts/MarketingLayout.vue`. NOT `WebLayout`.
- **Props/slots:** default slot; the header and footer are fixed parts.
- **Responsive:** identical structure at every width; only the header's inner nav changes.

### H2. Chrome header
- **Source classes:** `.chrome` (sticky, `z:60`, `--chrome-bg` + `blur(22px) saturate(160%)`,
  `--chrome-line` bottom), `.chrome-in` (container, `14px 28px`, flex, `gap:20px`), `.brand`
  (32px accent `mark` + wordmark 19px + `.tag` "Beta"), `.nav` (`.lnk` pills, `.icb` theme
  toggle, ghost "Log in", primary "Get Tallypal").
- **States:** link hover (`--surface` pill), scrolled (no change — always translucent).
- **Replaces:** **new** `MarketingHeader.vue`, **replacing** `components/WebHeader.vue`
  (its only consumer is `Welcome.vue`). Brand mark reuses `AppLogo`/`AppLogoIcon`; theme button
  reuses `ThemeToggle` (and therefore `useAppearance`, not the source's own `localStorage` script).
- **Props/slots:** none; reads `auth.user` from `usePage()`.
- **Responsive:** the source hides `.lnk` **and** the Beta tag below `880px`, leaving brand +
  toggle + both buttons. **#M4 — add a hamburger** in their place: an icon button (`.icb` shape)
  opening `ui/sheet` `side="left"` with the four anchors, plus the app nav links and the user
  block when signed in. The current `WebHeader` already implements exactly this sheet — port it
  rather than rebuilding. Marketing keeps its own switch point (`880px`), not the app's `md`.
- **Chrome blur (#M1):** `blur(22px)` over `--bg 78%` — deliberately **not** the app's
  `--blur-nav` (`blur(24px)` / `82%`). Do not unify.

### H3. Site container
- **Source classes:** `.wrap` — `max-width: var(--maxw)` (1240px), `margin: 0 auto`, `padding: 0 28px`.
- **Replaces:** **new** `MarketingContainer.vue` (or a single `.wrap` utility in `app.css`).
  Not the app's `px-5` screen padding.
- **Props/slots:** default slot, optional `as`.

### H4. Section wrapper + head
- **Source classes:** `.sec` (`padding: clamp(56px,7vw,104px) 0`), `.sec-line` (top hairline),
  `.sec-head` (2-col grid: heading block left, `.sec-p` right, `align-items:end`, `mb:44px`),
  `.kicker` (mono uppercase accent), `h2.sec-h`, `.sec-p`.
- **States:** with / without the right-hand paragraph; with / without the top rule.
- **Replaces:** **new** `MarketingSection.vue` + `MarketingSectionHead.vue`. Distinct from the
  app's `SectionHeader` (B3), which is the in-app `.sec-t` eyebrow row — do not reuse it here.
- **Props/slots:** `id`, `rule?`, `kicker`, `heading`, `lede?`, default slot.
- **Responsive:** collapses to one column below `820px` with `align-items:start`, `gap:16px`.

### H5. Marketing buttons
- **Source classes:** `.btn` (pill, 14.5/600, `12px 20px`), `.btn-p` (accent, hover
  `brightness(1.08)`), `.btn-g` (`--surface` bg + `--line` border, hover `--surface-2`),
  `.btn-lg` (`15px 26px`, 15.5px), full-width in plan cards.
- **States:** default, hover, focus-visible, full-width.
- **Replaces:** **extend** `ui/button` — `.btn-p` = existing `default`; `.btn-g` needs a **new
  `surface` variant** (the kit's `outline` sits on `bg-background`, not `--surface`); add a
  `pill-lg` size for `.btn-lg`. The existing `touch` size (h-12) is close but not the same
  padding.
- **Watch-out:** the kit's `outline`/`ghost` hover is `hover:bg-accent`, and `@theme inline` maps
  `--color-accent` to the **brand accent**, not to a neutral hover surface (`--accent-color` from
  `DESIGN_TOKENS.md §5.1` is never wired up). Verify the hover fill before reusing those variants.

### H6. Hero
- **Source classes:** `.hero` (+ `::before` `--glow-hero`), `.hero-grid`
  (`1.05fr / .95fr`, `gap: clamp(32px,5vw,72px)`, centered), left column = `.badge` + `h1.big`
  + `.lede` + `.cta` + `.hero-proof`; right column = `.stage` → `.anchor` → device + floats.
- **Replaces:** **new** `welcome/sections/MarketingHero.vue` — rewrites `WelcomeHeroSection.vue`.
- **Props/slots:** copy via i18n; `auth`-aware primary CTA.
- **Responsive:** single column below `900px` (device below the copy); floats hidden below `820px`;
  device shrinks to `min(300px,86vw)` below `520px`.

### H7. Status badge (hero)
- **Source classes:** `.badge` — pill on `--surface` + `--line`, 12.5px, leading `.d` dot in
  `--pos` with a `4px` `color-mix(pos 22%)` halo.
- **States:** one (static). The halo is a ring, not an animation.
- **⚠️ Copy (#M7):** the design's "11 currencies · iOS, Android, web" is **not true** — there is no
  iOS or Android app. Build the component; ship it only with a true line (e.g. the beta status or
  the web-app fact), supplied by the user.
- **Replaces:** **extend** `ui/badge` with a `status` variant, or **new** trivial `StatusBadge`
  composing `Dot` (A6). Existing `pill`/`accent` variants are 10px — too small here.
- **Props/slots:** `dotColor`, default slot.

### H8. Proof stats (hero)
- **Source classes:** `.hero-proof` (flex-wrap, `gap:26px`, top rule, `mt:38px/pt:26px`),
  `.k` (mono 24/600/`-.04em`), `.l` (12px `--text-3`).
- **Replaces:** **new** `ProofStats.vue`. **Absorbs `WelcomeStatsSection.vue`** — the design has no
  standalone stats band; the three figures live in the hero. Delete the old section either way.
- **Props:** `items[] { value, label }`.
- **Responsive:** wraps; no column rules (the editorial version's vertical rules are gone).
- **⚠️ Content (#M7):** "38,400 tallies", "€2.4M settled" and "4.9 · 1.2k ratings" are **invented**.
  Build `ProofStats`, but **do not render it** in the hero until the user supplies real figures.
  The hero must read correctly without it (rule above the row is part of the component).

### H9. Device frame
- **Source classes:** `.stage` (`min-height:520px`), `.anchor` (330px positioning context),
  `.phone` (`--radius-device`, `10px` padding, `--shadow-device`), `.phone .in`
  (`--radius-device-inner`, `1px --line`), `.pstatus` (9:41 / "Home"), `.pbody`.
- **Inside it** the source hand-builds a ~0.85× copy of the Home screen (`.app-hdr`, `.app-bal`,
  `.io`, `.quick`, `.txlist`, `.pnav`). **#M6 — do not rebuild it in Vue.**
- **Replaces:** **new** `MarketingDeviceFrame.vue` = frame + status bar + a **static screenshot**
  of the real Home screen. No app components inside, so no scaled duplicates to maintain.
- **Props/slots:** `src` / `srcDark`, `alt`, `label` (status-bar right text).
- **Screenshot rules:** one asset per theme (the page is theme-aware; a single image would break
  in one of them), captured at 2× the rendered `330px` frame and served from `public/`; the frame,
  its bezel ring and the status bar stay DOM so they keep using `--line`/`--surface`/`--shadow-device`.
  Capture from the real `/home` screen with seeded demo data. **Re-capture whenever Home changes
  visibly** — add that to Batch 4.4's exit list.
- **Responsive:** `330px` → `min(300px,86vw)` below `520px`; the frame is `flex-shrink:0`.
- **Accessibility:** decorative — `alt=""` plus `aria-hidden`, and no page copy may live only
  inside the image. Use `loading="lazy"` only below the fold; the hero image is LCP, so preload it.

### H10. Floating stat card
- **Source classes:** `.float` (158px, `--surface`, `--line`, `--r`, `--shadow-float`,
  `z:3`), `.l` (10.5/600/`.07em` uppercase `--text-3`), `.v` (mono 19/600), positioned
  `f1` (left of the frame, `top:26%`) and `f2` (right, `bottom:14%`), each overlapping by 18px.
- **States:** positive value (`--pos`) / neutral.
- **Replaces:** **new** `FloatingStatCard.vue` — **DOM, not part of the screenshot** (#M6), so it
  stays crisp and theme-aware over the image edge.
- **Props:** `label`, `value`, `tone` (`pos` / `neutral`), `position`.
- **Content:** its figures are illustrative product UI ("Luca owes you", "Rent · 60/40"), not
  business claims — they are unaffected by #M7.
- **Responsive:** `display:none` below `820px` (they have no stacked layout — do not invent one).

### H11. Step card ("How it works")
- **Source classes:** `.steps` (`auto-fit, minmax(240px,1fr)`, `gap:14px`), `.step`
  (`--surface`, `--line-soft`, `--r`, `22px 22px 24px`), `.n` (30px `--surface-2` square,
  `radius:10px`, mono 12/600), `h3` 18/600, `p` 14.5 `--text-2`.
- **States:** static (no hover in source).
- **Replaces:** **new** `StepCard.vue` + the `HowItWorks` section. **New section** — the current
  landing has no equivalent.
- **Props/slots:** `number`, `title`, default slot.
- **Responsive:** intrinsic `auto-fit` — 3 → 2 → 1 columns with no media query.

### H12. Feature bento + tile
- **Source classes:** `.bento` (6-col grid, `gap:14px`), `.tile` (`--surface`, `--line-soft`,
  `--r-lg` 28px, `26px`, `gap:16px`, hover `translateY(-2px)` + accent-mix border), `.tile.w3`
  (span 3) / `.w2` (span 2) / `.w6`, `.hd` (34px `CategoryGlyph` + `h3` 21px), `p` (max 46ch),
  `.demo` (inset panel: `--surface-inset`, `--line`, `--r`, `16px`, `margin-top:auto`).
- **Layout in the design:** two `w3` tiles, then three `w2` tiles (3+3 / 2+2+2). `.w6` is defined
  but unused — keep the class for later.
- **States:** default, hover.
- **Replaces:** **new** `FeatureBento.vue` + `FeatureTile.vue` — rewrites
  `WelcomeFeaturesSection.vue` (6 numbered editorial cells → 5 bento tiles).
- **Props/slots:** `span` (2 | 3 | 6), `glyph`, `color` (`c1..c6`), `title`, default slot (copy),
  `demo` slot.
- **Responsive:** below `900px` the grid is 2 columns and every tile spans both.

### H13. Tile demos — all five reuse shipped app components
| Tile | Source markup | Build from |
|---|---|---|
| Splits that actually fit | `.spl` rows + `.settle` | `SplitMemberRow` (B12) + `SettleBanner` (B13) — both exist; `.settle` here has a **button** ("Settle up"), so `SettleBanner`'s action slot is used |
| One place for every ledger | `.chart` bars + `.axis` + `.catrow` rows | `ui/chart` (@unovis, per decision #17) + `Dot` (A6); `.catrow` is B11 without the `MiniBar` |
| Multi-currency | `.fxrow span` chips | `ChoiceChip`/`ChipGroup` (A10) — static, `selected` on the first |
| Recurring | two `.tx` rows | `TransactionRow` (B9) at the mock's smaller scale |
| Your data, yours | `.plist` + `.ck` check circles | **new** `CheckList` (H14) — ⚠️ its three lines (end-to-end encrypted sync, CSV & PDF export, delete without email) are claims, see #M7 |

### H14. Check list
- **Source classes:** `.plist` / `.plist li` (14px `--text-2`, `gap:10px`), `.ck` (18px circle,
  `color-mix(pos 20%)` bg, `--pos` tick). Pricing reuses the shape at 17px with `--surface-2`
  bg, accent-mix in the highlighted plan, and an `.off` state (transparent bg, `--line` border,
  em-dash, `--text-3` label).
- **States:** `on` (tick) / `off` (dash, muted) / `accent` (inside the highlighted plan).
- **Replaces:** **new** `CheckList.vue` + `CheckListItem.vue`, shared by the privacy tile and both
  pricing plans.
- **Props:** `items[] { label, state }`, `tone`.

### H15. Quote card
- **Source classes:** `.quotes` (`auto-fit, minmax(260px,1fr)`, `gap:14px`), `.quote`
  (`--surface`, `--line-soft`, `--r`, `24px`), `p` 15.5/`-.015em`, `.by` (avatar + `b` name +
  `--text-3` context, pinned with `margin-top:auto`).
- **Replaces:** **new** `QuoteCard.vue` + a `Testimonials` section. **New section** — no
  equivalent today. Avatars reuse `Avatar` `size="sm"` with `c1..c3` fallbacks (A3).
- **Props/slots:** `quote`, `author`, `context`, `initials`, `color`.
- **Responsive:** intrinsic `auto-fit`, 3 → 2 → 1.
- **⚠️ Content (#M7): the three testimonials are invented** (Luca B., Marta & Gio, Sara R.).
  Attributed quotes from people who never said them cannot ship. Build the component; **leave the
  section out of the page** until the user has real, consented quotes. The page reads fine without
  it: How-it-works → Features → Pricing → FAQ → CTA.

### H16. Pricing plan card
- **Source classes:** `.prices` (`auto-fit, minmax(280px,1fr)`, `gap:16px`, `align-items:start`),
  `.plan` (`--surface`, `--line`, `--r-lg`, `28px`), `.plan.hi` (accent-mix border +
  `--shadow-plan-hi`), `.top` (name + `.tg` tag), `.pr` (mono 46px + `.cur` + sans `.per`),
  `.ds`, `ul` of H14 items, full-width CTA.
- **States:** standard / highlighted (`hi`); feature `on` / `off`.
- **Replaces:** **new** `PricingPlanCard.vue` — rewrites `WelcomePricingSection.vue` (which renders
  the two plans as halves of one bordered slab with serif prices and strike-through features).
- **Props/slots:** `name`, `tag`, `highlighted`, `price`, `currency`, `per`, `description`,
  `features[]`, `ctaLabel`, `ctaHref`.
- **Responsive:** intrinsic — two side-by-side cards, stacking under ~600px. The highlighted card
  is **not** scaled up; it is marked by border + glow only.
- **⚠️ Content (#M7):** the design's limits ("1 shared tally, up to 4 members", "Receipt scanning,
  50 / month", "Multi-currency with auto-FX") must match what the product actually does and bills;
  today's `en.ts`/`it.ts` copy differs. Confirm each line before shipping.

### H17. FAQ
- **Source classes:** `.faq` (`auto-fit, minmax(320px,1fr)`, `gap: 12px 28px` — a **two-column
  masonry-ish grid**, `height: fit-content` per item), `details` (`--surface`, `--line-soft`,
  `--r-sm`, `16px 18px`), `summary` (15/500, marker removed, `+` → `–` via `::after` in mono),
  `.a` (14.5 `--text-2`). First item `open`.
- **States:** collapsed / open.
- **Replaces:** **new** `FaqAccordion.vue` + `FaqItem.vue` — rewrites `WelcomeFaqSection.vue`
  (single-column rules, serif questions). The kit has **no accordion** (`ui/stepper` and
  `ui/collapsible` are unrelated); native `<details>` matches the design and needs no library.
- **Props/slots:** `items[] { question, answer }`, `defaultOpen`.
- **Responsive:** one column below ~`680px` via the intrinsic minmax.
- **⚠️ Content (#M7):** the six answers assert guest-link sharing, encryption at rest, an EU
  database, one-click export and self-service deletion. Keep only the ones that are true today.

### H18. CTA band
- **Source classes:** `.band` (`--surface`, `--line`, `--r-lg`, `clamp(32px,5vw,58px)`, centered,
  `overflow:hidden`) + `::before` `--glow-band` bleeding off the bottom; `h2`
  `clamp(26px,3.6vw,44px)`, `p` (max 48ch), centered `.cta` pair.
- **Replaces:** **new** `CtaBand.vue`. **New section** — no equivalent today.
- **Props/slots:** `heading`, `body`, actions slot.
- **Responsive:** padding and heading both clamp; buttons wrap.
- **#M8 — one button, not two:** there is no live demo, so the "Open the live demo" CTA is
  **removed** and the band ships with the single primary "Get Tallypal free" → `register`. Same
  rule for every link target: `#signup` → `register`, `#login` → `login`, `#demo` → deleted.
  The hero's secondary CTA stays, pointing at the in-page `#how` anchor.

### H19. Marketing footer
- **Source classes:** `footer` (top rule, `52px 0 40px`, `margin-top: clamp(56px,7vw,96px)`),
  `.fgrid` (`1.6fr + 3×1fr`, `gap:32px`, bottom rule at `36px`), brand + tagline (`--text-3`,
  32ch), `.fcol h5` (mono 11/600/`.12em` uppercase — matches the repo `.eyebrow` utility exactly),
  `.fcol a` (14px, `4px` vertical), `.fbot` (mono 11.5/`.04em`, copyright ↔ `tallypal.it`).
- **Replaces:** **new** `MarketingFooter.vue` — rewrites `WelcomeFooterSection.vue` (same three
  columns, so only tokens/typography change plus the brand block).
- **Props/slots:** columns via i18n.
- **Responsive:** `2×2` below `760px`.

### H20. Reveal-on-scroll
- **Source classes:** `.rv` → `.rv.in`; `opacity 0 → 1`, `translateY(14px) → none`, `.7s ease`,
  `IntersectionObserver` at `threshold .12`, unobserved after first hit, delay
  `(index mod 3) × 70ms`.
- **Replaces:** **new** `v-reveal` directive (or `useReveal()` composable).
- **Props:** `delay?`.
- **Required additions (not in the source):** SSR guard (`IntersectionObserver` is client-only —
  render content visible when it is absent) and a `prefers-reduced-motion: reduce` branch that
  skips both the transform and `scroll-behavior: smooth`.

---

## Summary: net-new components to build

`AvatarGroup`, `TrendBadge`, `Dot`, `MiniBar`, `NumberStepper`, `ChoiceChip`/`ChipGroup`,
`CategoryGlyph`, `DetailHeader`, `SectionHeader`, `BalanceHero`, `InOutCards`, `QuickActions`,
`TallyRail` + `TallyRailCard`, `TallyListRow`, `TransactionRow` (extract), `TxDayHeader`,
`SplitMemberRow`, `SettleBanner`, `BottomNav` + `BottomNavItem`, `AmountInput`, `SplitEditor`,
`FormField`.

**Desktop design (added 2026-10-11):** `CardHeader` (B15), `SettingRow` (B16), `DataTable` (B17),
`EmptyState` (F1), `SplitBar` + `SplitLockButton` (E3 extras). Extends: `ui/select` `pill`
trigger (`PillSelect`, E6), `ui/avatar` `xl`, `NumberStepper` `sm`, `SegmentedControl` `sm`,
`FormField` error state (E1). **Not built:** `ui/switch` (E5).

**Marketing (§H):** `MarketingLayout`, `MarketingHeader` (replaces `WebHeader`),
`MarketingContainer`, `MarketingSection` + `MarketingSectionHead`, `MarketingHero`,
`StatusBadge`, `ProofStats`, `MarketingDeviceFrame`, `FloatingStatCard`, `StepCard`,
`FeatureBento` + `FeatureTile`, `CheckList`, `QuoteCard`, `PricingPlanCard`, `FaqAccordion` +
`FaqItem`, `CtaBand`, `MarketingFooter`, `v-reveal`.

Components to **extend**: `ui/button` (touch size, icon consolidation, **`surface` variant +
`pill-lg` size** for marketing), `ui/badge` (pill/accent/pos/neg + **`status`** for the hero
badge), `ui/avatar` (xs/sm + color), `ui/sheet` (bottom side + grabber),
`ui/segmented-control` (restyle), `ui/input`/`select`/`textarea` (filled variant),
`ui/sidebar` + `AppSidebar` (app-wide desktop shell), `ui/dialog` (Add-tx/New-tally ≥ md).
