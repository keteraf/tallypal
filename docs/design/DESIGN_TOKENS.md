# Design Tokens

Source of truth is the Claude Design file `Tallypal Mobile.html` for the app (§1–§7) and
`Tallypal Site.html` for the marketing page (§8) — project
`019dc420-c153-725e-9f8b-4ee4e47b22df`. Every value below is extracted verbatim from those
files' `<style>` blocks, then named as a CSS custom property.

> ## ✅ RESOLVED — one design system
>
> The design project also contains a desktop file (`Tallypal.html`) in a separate
> **"editorial"** style (terracotta, Newsreader serif, sharp radii) that the repo
> currently implements. **Decision: the editorial style is removed completely.** The
> mobile system in this document is the **single** design language for the whole app
> (marketing, auth, admin, and the authed app), at every viewport.
>
> Consequences baked into this doc:
> - **#1** Editorial tokens (`--paper/--ink/--brand/--rule/--brand-soft`, Newsreader) are
>   **deleted**; the mobile tokens become canonical.
> - **#2** Default theme stays **`system`** (repo behavior) — *not* dark-first. Both light
>   and dark token sets are required (the mobile file provides both).
> - **#3** Monospace font is **Geist Mono** (replaces JetBrains Mono).
> - **#4** **No serif.** Newsreader is dropped entirely; headings use Geist (sans).
> - **#5** Category/tally color = **user-chosen at creation and persisted** (`c1..c6`), with
>   a deterministic fallback for unset rows. See §7.
> - **#6** Spacing = **snap to Tailwind's default 4px scale** (use `p-4`, `gap-3`, …), not
>   bespoke px tokens. The px table in §3 is reference for picking the nearest step.

---

## 1. Color tokens (canonical)

Authored in `oklch()`. The app is `system`-default: `:root` = **light**, `.dark` = **dark**.
(The mobile source file is dark-first via `data-theme`; here the values are re-homed onto the
repo's `.dark`-class mechanism — see §5.)

### 1.1 Semantic surface / text / line

| Token | Light (`:root`) | Dark (`.dark`) | Role |
|---|---|---|---|
| `--bg` | `oklch(0.975 0.006 265)` | `oklch(0.155 0.018 265)` | App background (screen) |
| `--surface` | `oklch(1 0 0)` | `oklch(0.205 0.02 265)` | Cards, tally cards, sheet, inputs base |
| `--surface-2` | `oklch(0.955 0.008 265)` | `oklch(0.255 0.022 265)` | Raised / hover fill, quick-action bg, chips, field bg |
| `--surface-3` | `oklch(0.91 0.012 265)` | `oklch(0.31 0.024 265)` | Highest raise: segmented active, chart bar base, grabber, stepper hover |
| `--text` | `oklch(0.2 0.02 265)` | `oklch(0.975 0.004 265)` | Primary text |
| `--text-2` | `oklch(0.42 0.018 265)` | `oklch(0.78 0.012 265)` | Secondary text |
| `--text-3` | `oklch(0.58 0.016 265)` | `oklch(0.6 0.015 265)` | Tertiary/muted text, labels, meta |
| `--line` | `oklch(0.9 0.01 265)` | `oklch(0.29 0.02 265)` | Hairline borders / row dividers |

### 1.2 Accent + semantic status

| Token | Light | Dark | Role |
|---|---|---|---|
| `--accent` | `oklch(0.55 0.19 262)` | `oklch(0.7 0.17 262)` | Primary brand action (FAB, primary btn, active nav, focus ring) |
| `--accent-ink` | `oklch(1 0 0)` | `oklch(0.16 0.02 265)` | Foreground on accent |
| `--accent-dim` | `oklch(0.88 0.07 262)` | `oklch(0.4 0.11 262)` | Dimmed accent (disabled/subtle) |
| `--pos` | `oklch(0.58 0.16 158)` | `oklch(0.83 0.17 158)` | Positive amounts / income / "in" |
| `--neg` | `oklch(0.58 0.2 22)` | `oklch(0.72 0.19 22)` | Negative amounts / over-100% warning |

### 1.3 Categorical palette (charts, category dots, tally glyphs, avatars)

Six hues. Used directly (`var(--c1)`) and via `color-mix(in oklch, var(--cN) 20-26%, transparent)`
for soft fills.

| Token | Light | Dark | Approx hue |
|---|---|---|---|
| `--c1` | `oklch(0.55 0.19 262)` | `oklch(0.7 0.17 262)` | indigo (== accent) |
| `--c2` | `oklch(0.6 0.15 195)` | `oklch(0.8 0.16 195)` | cyan |
| `--c3` | `oklch(0.58 0.16 158)` | `oklch(0.83 0.17 158)` | green (== pos) |
| `--c4` | `oklch(0.68 0.15 85)` | `oklch(0.85 0.16 95)` | amber |
| `--c5` | `oklch(0.6 0.19 25)` | `oklch(0.75 0.18 25)` | red |
| `--c6` | `oklch(0.56 0.18 320)` | `oklch(0.72 0.17 320)` | magenta |

Soft-fill recipe (glyph/icon backgrounds): `color-mix(in oklch, var(--cN) 20%, transparent)`
for tx icons, `22%` for tally glyphs, `26%` for avatars; border `color-mix(in oklch, var(--cN) 30%, transparent)`.

**By category rows (Home, Stats)** take their hue by rank within the card, in the design's order `--c1`, `--c4`, `--c2`,
`--c6`, `--c3` (indigo, amber, cyan, magenta, green), so rows shown together never share one. `--c5` (red) is not used there
(the design never gives a category that hue; it reads as `--neg`). The "Other" row is `--text-3` (dot) and `--surface-3` (bar)
(FP-12, `breakdownRowColors()` in `lib/category.ts`).

### 1.4 Demo-only (NOT app tokens)

These style the phone mockup frame and are **not** part of the app surface:
`body` bg `oklch(0.11 0.014 265)` (dark) / `oklch(0.88 0.01 265)` (light);
phone shadow `0 40px 100px -20px rgba(0,0,0,.6), 0 0 0 1px var(--line)`. Ignore on import.

---

## 2. Typography

### 2.1 Families

| Token | Value | Notes |
|---|---|---|
| `--font-sans` | `'Geist', ui-sans-serif, system-ui, sans-serif` | All UI **and headings** (no serif) |
| `--font-mono` | `'Geist Mono', ui-monospace, Menlo, monospace` | **All numerals / amounts / labels-as-data** |

Load Geist `300;400;500;600;700` and **Geist Mono** `400;500;600`.
**Decisions #3/#4:** `--font-mono` becomes **Geist Mono** (was JetBrains Mono) and
`--font-serif` (Newsreader) is **removed**. Any `font-serif` utility / `--font-serif`
reference in existing views must be replaced with sans during migration (Phase 4/5).

### 2.2 Type scale (every distinct size in the mobile file)

Named as `--text-*` custom properties. `w` = font-weight, `ls` = letter-spacing.

| Token | size | w | ls | Used by |
|---|---|---|---|---|
| `--text-display` | `52px` | 600 | `-0.045em` | Balance hero amount (`.bal .amt`) |
| `--text-display-cur` | `30px` | 500 | — | Currency mark in hero |
| `--text-amount-lg` | `46px` | 600 | `-0.045em` | Sheet amount entry (`.amt-in .v`) |
| `--text-h1` | `28px` | 600 | `-0.03em` | Screen title (`.hdr h1`) |
| `--text-io` | `22px` | 600 | `-0.03em` | In/Out card value |
| `--text-tally-v` | `19px` | 600 | `-0.03em` | Tally rail card amount |
| `--text-sheet-title` | `19px` | 600 | `-0.025em` | Bottom-sheet heading (`.sh-hd h2`) |
| `--text-detail-name` | `18px` | 600 | `-0.025em` | Detail header name |
| `--text-tl-v` | `16px` | 600 | `-0.03em` | Tallies-list amount |
| `--text-tl-nm` | `15.5px` | 600 | `-0.02em` | Tallies-list name |
| `--text-body` | `15px` | 400/500/600 | — | Tally name, tx amount, field input, base |
| `--text-tx-nm` | `14.5px` | 500 | `-0.015em` | Transaction row title |
| `--text-btn` | `14.5px` | 600 | `-0.01em` | Buttons |
| `--text-sm` | `14px` | 400/500/600 | — | cat/spl name+value, settle body(13.5), field label sizes |
| `--text-hdr-sub` | `13px` | 400 | — | Header subtitle, balance label |
| `--text-chip` | `13px` | 500 | — | Category chips, stepper number |
| `--text-seg` | `12.5px` | 500 | `-0.01em` | Segmented control button |
| `--text-meta` | `12px` | 500/600 | — | tx sub, day group, tl sub, tot |
| `--text-eyebrow` | `12px` | 600 | `0.06em` | Section titles (uppercase) `.sec-t` |
| `--text-mem` | `11px` | 500/600 | `0.06em` | Field labels, io labels, pill(10), mem-em(11.5) |
| `--text-nav` | `10.5px` | 500 | `-0.005em` | Bottom-nav labels |
| `--text-pill` | `10px` | 600 | `0.03em` | Pills / badges |
| `--text-axis` | `10.5px` | — | `0.04em` | Chart axis labels (always with `font-mono`). **Wired in `app.css`** (`text-axis`). Was `10px` from the mobile file; `Tallypal Desktop.html` is the source of truth (owner decision 2026-10-11, F-073) |
| `--text-brand` | `16px` | 600 | `-0.02em` | Sidebar wordmark "Tallypal" in `--text` (desktop `.logo span`). **Wired in `app.css`** (`text-brand`). Owner decision 2026-10-11, F-006 |

Uppercase editorial labels use `text-transform: uppercase` + mono + `letter-spacing`
`0.03em`–`0.12em`. Prefer Tailwind's built-in text-size steps where they land within ±1px of
these values; use an arbitrary size only for the display/hero numbers.

Avatar sizes (`ui/avatar` `size`, text 600/`.02em` initial): `xs` 22px / 9px, `sm` 26px / 10px,
`md` 32px, `lg` 38px / 13px, **`xl` 56px / 18px** (identity card, F-083; owner decision
2026-10-11). There is **no 34px size**: the desktop sidebar user block (F-007) uses the existing
`md` (32px).

---

## 3. Spacing — snap to Tailwind's 4px scale (decision #6)

Do **not** encode bespoke px spacing tokens. Use Tailwind spacing utilities (`p-4`, `gap-3`,
`px-5`…). The table maps the recurring source px values to the nearest Tailwind step so
migration is mechanical:

| Source px | Tailwind step | Common use |
|---|---|---|
| 2px | `0.5` | seg padding, avatar overlap fine-tune |
| 4px | `1` | nav gaps, tiny gaps |
| 6px | `1.5` | icon gaps, delta gap |
| 8px | `2` | quick grid gap, header button gap |
| 10px | `2.5` | io gap, sheet field gaps |
| 11–12px | `3` | rail gap, tx/cat/spl vertical padding, nav h-padding |
| 14px | `3.5` | card→content gaps, btn v-padding |
| 16px | `4` | tally/card inner padding |
| 18px | `4.5` | `.card` padding |
| 20px | `5` | screen h-padding, sheet body h-padding |
| 22px | `5.5` | quick margin-top |
| 26px | `6.5` | section-title margin-top |

Layout constants that are **not** spacing (keep as explicit values): bottom **nav height
`88px`**, **FAB `52px`**, sheet `max-height 88%`, tally rail card `min-width 172px`
(new-card `112px`). Screen bottom padding must clear the nav: `pb-[88px]` **plus**
`env(safe-area-inset-bottom)` (the mock's fixed `108px` is a framed-phone shortcut).

---

## 4. Radius, shadow, transition, blur

### 4.1 Radius

| Token | Value | Use |
|---|---|---|
| `--radius-card` | `20px` (`--r`) | Cards, tally cards, in/out cards, settle box |
| `--radius-sm` | `14px` (`--r-sm`) | Quick-action buttons, inputs/selects, tally-rail card fields |
| `--radius-lg` | `28px` (`--r-lg`) | Bottom-sheet top corners |
| `--radius-pill` | `999px` | Buttons, pills, segmented, chips, steppers, delta, avatars-as-circle |
| `--radius-glyph` | `11px` / `14px` / `15px` | Tally glyph (11 rail / 15 list), tx icon (14) |
| `--radius-full` | `50%` | Avatars, icon buttons, nav FAB, dots |
| `--radius-mark` | `9px` | 28px accent brand mark in the desktop sidebar (F-006) — `rounded-mark` |
| `--radius-spark` | `3px 3px 1px 1px` | Balance-hero sparkline bars (F-021) — `rounded-spark` |
| `--radius-bar` | `6px 6px 2px 2px` | Stats chart bars (F-073) — `rounded-bar`; the @unovis SVG bars read the same value through `var(--radius-bar)` |

The last three come from `Tallypal Desktop.html` (owner decision 2026-10-11) and are wired in
`app.css`'s `@theme inline`, so the utilities exist.

The shadcn base `--radius` (was `0.25rem`) must be raised so kit components (dialog, dropdown,
popover, select…) match the rounder mobile look — set `--radius: 14px` and derive
`--radius-card`/`--radius-lg` explicitly for the bespoke pieces.

### 4.2 Shadow (the system is largely flat — surfaces separate by lightness)

| Token | Value | Use |
|---|---|---|
| `--shadow-fab` | `0 8px 24px -6px color-mix(in oklch, var(--accent) 70%, transparent)` | Bottom-nav FAB |
| `--scrim` | `rgba(0,0,0,.55)` + `backdrop-filter: blur(4px)` (`--blur-scrim`) | Sheet overlay |
| `--shadow-modal` | `0 40px 100px -20px rgba(0,0,0,.6), 0 0 0 1px var(--line)` | `≥ md` dialog shell (`ui/dialog` `DialogContent`, F-090) — `shadow-modal`. Wired in `app.css` |

No card/panel drop-shadows exist in the mobile app surface. The one exception is the desktop
dialog: `--shadow-modal` is the `.modal` shadow from `Tallypal Desktop.html` (owner decision
2026-10-11). It has the same value as the §1.4 phone-mock shadow, which stays demo-only.

### 4.3 Transition

| Token | Value | Use |
|---|---|---|
| `--transition-fast` | `.2s` | Buttons, hovers, chips, seg, steppers, quick, icon btn |
| `--transition-med` | `.3s` | Theme swap, chart bars, scrim fade |
| `--transition-sheet` | `transform .34s cubic-bezier(.32,.72,0,1)` | Bottom-sheet slide-in |

### 4.4 Backdrop blur

| Token | Value | Use |
|---|---|---|
| `--blur-nav` | `blur(24px) saturate(160%)` | Bottom nav bar (`background: color-mix(in oklch, var(--bg) 82%, transparent)`) |
| `--blur-scrim` | `blur(4px)` | Dialog + sheet scrim (`backdrop-blur-scrim`; matches the design's `.scrim`) |

---

## 5. Tailwind v4 mapping (the version this repo uses)

**Detected setup:** Tailwind **v4** via `@tailwindcss/vite`; **no `tailwind.config.js`**.
Theme lives in `resources/css/app.css` using `@theme inline { … }` + `:root` / `.dark`
custom properties. Dark mode = **`.dark` class** on `<html>` (`@custom-variant dark (&:is(.dark *))`),
toggled by `useAppearance.ts` (default `system`). **Do not add a `tailwind.config.js`.**

Because editorial is being removed (decision #1), the plan **overwrites** the token values.
To keep the ~200 existing components rendering during the migration, keep the existing
**semantic alias names** (`--background`, `--foreground`, `--primary`, `--border`,
`--muted-foreground`, `--card`, `--ring`, …) but **re-point them at the mobile tokens**, and
add the mobile-specific names. A small **compatibility shim** re-points the editorial names
still referenced by views (`--paper`, `--ink`, `--brand`, `--rule`, `--rule-2`, `--brand-soft`)
to mobile values; Phase 5 deletes the shim after views stop using those names.

### 5.1 `:root` / `.dark` custom properties (replace the palette in `resources/css/app.css`)

```css
:root {
  /* canonical mobile tokens — light */
  --bg:        oklch(0.975 0.006 265);
  --surface:   oklch(1 0 0);
  --surface-2: oklch(0.955 0.008 265);
  --surface-3: oklch(0.91 0.012 265);
  --text:      oklch(0.2 0.02 265);
  --text-2:    oklch(0.42 0.018 265);
  --text-3:    oklch(0.58 0.016 265);
  --line:      oklch(0.9 0.01 265);
  --accent:    oklch(0.55 0.19 262);
  --accent-ink: oklch(1 0 0);
  --accent-dim: oklch(0.88 0.07 262);
  --pos:       oklch(0.58 0.16 158);
  --neg:       oklch(0.58 0.2 22);
  --c1: oklch(0.55 0.19 262); --c2: oklch(0.6 0.15 195);
  --c3: oklch(0.58 0.16 158); --c4: oklch(0.68 0.15 85);
  --c5: oklch(0.6 0.19 25);   --c6: oklch(0.56 0.18 320);

  --radius: 14px;            /* shadcn base — was 0.25rem */
  --radius-card: 20px;
  --radius-lg-sheet: 28px;

  /* shadcn semantic aliases → mobile tokens */
  --background: var(--bg);            --foreground: var(--text);
  --card: var(--surface);             --card-foreground: var(--text);
  --popover: var(--surface);          --popover-foreground: var(--text);
  --primary: var(--accent);           --primary-foreground: var(--accent-ink);
  --secondary: var(--surface-2);      --secondary-foreground: var(--text);
  --muted: var(--surface-2);          --muted-foreground: var(--text-3);
  --accent-color: var(--surface-2);   --accent-foreground: var(--text);
  --destructive: var(--neg);          --destructive-foreground: var(--accent-ink);
  --border: var(--line);              --input: var(--line);   --ring: var(--accent);

  /* compatibility shim — DELETE in Phase 5 once views stop referencing these */
  --paper: var(--bg);       --paper-2: var(--surface-2);
  --ink: var(--text);       --ink-2: var(--text-2);   --ink-3: var(--text-3);
  --rule: var(--line);      --rule-2: var(--surface-2);
  --brand: var(--accent);   --brand-soft: color-mix(in oklch, var(--accent) 14%, transparent);
}

.dark {
  --bg:        oklch(0.155 0.018 265);
  --surface:   oklch(0.205 0.02 265);
  --surface-2: oklch(0.255 0.022 265);
  --surface-3: oklch(0.31 0.024 265);
  --text:      oklch(0.975 0.004 265);
  --text-2:    oklch(0.78 0.012 265);
  --text-3:    oklch(0.6 0.015 265);
  --line:      oklch(0.29 0.02 265);
  --accent:    oklch(0.7 0.17 262);
  --accent-ink: oklch(0.16 0.02 265);
  --accent-dim: oklch(0.4 0.11 262);
  --pos:       oklch(0.83 0.17 158);
  --neg:       oklch(0.72 0.19 22);
  --c1: oklch(0.7 0.17 262);  --c2: oklch(0.8 0.16 195);
  --c3: oklch(0.83 0.17 158); --c4: oklch(0.85 0.16 95);
  --c5: oklch(0.75 0.18 25);  --c6: oklch(0.72 0.17 320);
  /* aliases + shim inherit the same var() indirections as :root */
}
```

### 5.2 `@theme inline` mapping (utility generation)

Replace the editorial color block inside `@theme inline { … }` with the mobile tokens so
utilities like `bg-surface-2`, `text-text-3`, `text-c4`, `border-line`, `rounded-card` exist.
Keep the shadcn `--color-*` aliases (they now resolve to mobile values via §5.1):

```css
@theme inline {
  --font-sans: 'Geist', ui-sans-serif, system-ui, -apple-system, sans-serif;
  --font-mono: 'Geist Mono', ui-monospace, Menlo, monospace;
  /* --font-serif REMOVED */

  --color-bg:        var(--bg);
  --color-surface:   var(--surface);
  --color-surface-2: var(--surface-2);
  --color-surface-3: var(--surface-3);
  --color-text:      var(--text);
  --color-text-2:    var(--text-2);
  --color-text-3:    var(--text-3);
  --color-line:      var(--line);
  --color-accent:    var(--accent);
  --color-accent-ink: var(--accent-ink);
  --color-accent-dim: var(--accent-dim);
  --color-pos:       var(--pos);
  --color-neg:       var(--neg);
  --color-c1: var(--c1); --color-c2: var(--c2); --color-c3: var(--c3);
  --color-c4: var(--c4); --color-c5: var(--c5); --color-c6: var(--c6);

  /* existing shadcn aliases (--color-background, --color-primary, …) stay,
     now resolving to mobile values; keep them so kit components keep compiling */

  --radius-card: var(--radius-card);
  --radius-lg:   var(--radius-lg-sheet);

  /* desktop-design additions (2026-10-11) */
  --radius-mark:  9px;
  --radius-spark: 3px 3px 1px 1px;
  --radius-bar:   6px 6px 2px 2px;
  --text-brand: 16px;  --text-brand--font-weight: 600;  --text-brand--letter-spacing: -0.02em;
  --text-axis: 10.5px; --text-axis--letter-spacing: 0.04em;   /* use with font-mono */
  --shadow-modal: 0 40px 100px -20px rgba(0,0,0,.6), 0 0 0 1px var(--line);
}
```

### 5.3 Utilities in `@layer utilities`

The repo already ships `.eyebrow`, `.font-mono`, `.tabular` in `app.css`. Mobile's `.num` ==
`.tabular` + `font-mono`; reuse them. No new global utilities are strictly required.

---

## 6. Editorial tokens being removed (Phase 5 delete-list)

These implement the old `Tallypal.html` look and are **live in the repo today**. Under
decision #1 they are removed. During migration the §5.1 shim keeps them working; Phase 5
deletes both the shim and every view reference. Grep targets to purge:

- Custom props: `--paper`, `--paper-2`, `--ink`, `--ink-2`, `--ink-3`, `--rule`, `--rule-2`,
  `--brand`, `--brand-soft`, `--radius: 0.25rem`, `--font-serif`.
- Utilities/classes in views: `font-serif`, `bg-paper*`, `text-ink*`, `border-rule*`,
  `text-brand`, `bg-brand-soft`, inline `style="font-style: italic; color: var(--brand)"`,
  `text-[clamp(...)]` serif headers.
  **Note (2026-10-11):** Phase 5 finished this purge. `text-brand` is now the §2.2 wordmark
  *type step* (`--text-brand`), not the editorial brand colour, so a grep hit on it is no longer
  a leftover.
- The desktop file's shipped-but-unused **"tweaks panel"** (`data-voice` editorial/playful/
  brutalist, `data-density`, hue slider) is not in the repo — ignore entirely.

---

## 7. Category / tally color source (decision #5 — resolved)

**Chosen at creation and persisted.** The mobile New-tally sheet has a *Colour & icon* picker
and the Add-tx sheet has colored category chips — so color is a user choice, not derived.

- Add a `color` field (enum `c1`…`c6`) to **tallies** and to **categories**; the pickers set it.
- Unset / legacy rows fall back to a **deterministic cycle**: `c{(id % 6) + 1}` (stable per row).
- The frontend maps the stored token to `var(--cN)` and its soft-mix fills; never hardcode hex.

> Backend dependency (out of this planning scope, flagged for the migration): a migration adds
> the `color` columns and the create/edit endpoints accept it. Until then the frontend uses the
> deterministic fallback so screens render.

---

## 8. Marketing site extensions (source: `Tallypal Site.html`)

The design project's third file, `Tallypal Site.html`, is the **marketing page** design
(Batch 4.3). It is the same system, not a second one:

- **Colors: reused verbatim.** Its `:root` (dark) and `body[data-theme="light"]` blocks are
  character-for-character the §1 dark and light sets — `--bg/--surface/--surface-2/--surface-3`,
  `--text*`, `--line`, `--accent*`, `--pos/--neg`, `--c1..--c6`. Nothing to merge. The file is
  dark-first via `data-theme` on `<body>`; the repo's `:root`-light + `.dark`-class mechanism
  (§5) stays authoritative, and its theme button must use `useAppearance.ts`, not the file's
  `localStorage['tallypal-site-theme']` script.
- **Fonts: reused verbatim.** Geist `300..700` + Geist Mono `400..600`, `--sans`/`--mono`
  identical to §2.1. No serif.
- **Radii: reused.** `--r: 20px`, `--r-sm: 14px`, `--r-lg: 28px` — same values as §4.1.
- **`.num`: reused.** Same mono + tabular + `-.02em` recipe as the repo utility.

What it genuinely adds is below: a display type scale, a page container, two accent glows,
a softened hairline, an inset panel surface, marketing spacing, a reveal transition, and the
device-frame block. All are **extensions**, named with the existing scheme.

### 8.1 Layout container

| Token | Value | Source | Role |
|---|---|---|---|
| `--container-site` | `1240px` | `--maxw` | `.wrap` max width — hero, every section, footer |
| `--gutter-site` | `28px` (`px-7`) | `.wrap` padding | Side gutter at **every** width, mobile included |

The app screen gutter stays `20px` (`px-5`, §3) — different surface, no conflict. The editorial
marketing views currently use `max-w-330` (1320px); that is pre-decision markup, superseded by
`--container-site`.

### 8.2 Display typography (extends §2.2)

Marketing-only steps. `w` = weight, `ls` = letter-spacing, `lh` = line-height.

| Token | size | w | ls | lh | Used by |
|---|---|---|---|---|---|
| `--text-hero` | `clamp(40px,6.2vw,74px)` | 600 | `-0.045em` | 1.02 | Hero `h1.big` (`text-wrap: balance`) |
| `--text-section` | `clamp(28px,3.6vw,46px)` | 600 | `-0.04em` | 1.06 | Every section `h2.sec-h` (`max-width: 22ch`) |
| `--text-lede` | `clamp(16px,1.4vw,18.5px)` | 400 | — | 1.55 | Hero `.lede` (`max-width: 50ch`) |
| `--text-sec-p` | `16.5px` | 400 | — | 1.55 | Section paragraph `.sec-p` (`max-width: 56ch`) |
| `--text-tile-h` | `21px` | 600 | `-0.03em` | — | Feature tile `h3` |
| `--text-quote` | `15.5px` | 400 | `-0.015em` | 1.5 | Testimonial body |
| `--text-price` | `46px` | 600 | `-0.05em` | — | Plan price (mono) |
| `--text-price-cur` | `24px` | 600 | — | — | Plan currency mark |
| `--text-price-per` | `13.5px` | 400 | `0` | — | Plan "/ month" (sans, inside a mono line) |
| `--text-proof-k` | `24px` | 600 | `-0.04em` | — | Hero proof figure (mono) |
| `--text-plan-nm` | `17px` | 600 | `-0.02em` | — | Plan name |
| `--text-btn-lg` | `15.5px` | 600 | `-0.01em` | — | `.btn-lg` (hero + band CTAs) |
| `--text-nav-lnk` | `14px` | 500 | — | — | Chrome nav link, footer column link |
| `--text-badge` | `12.5px` | 500 | — | — | Hero status badge, CTA fine print, settle line |
| `--text-float-v` | `19px` | 600 | `-0.035em` | — | Floating stat-card value (mono) |
| `--text-tag` | `9.5px` | 500 | `0.14em` | — | Brand "Beta" tag (mono, uppercase) |
| `--text-kicker` | `11px` | 500 | `0.16em` | — | Section kicker (mono, uppercase, `--accent`) — see §8.3 |

Reused from §2.2 with no change: `--text-btn` (14.5/600 — `.btn`), `--text-detail-name`
(18/600/`-.025em` — step `h3`), `--text-sheet-title` (19/600 — brand wordmark),
`--text-body`, `--text-sm`, `--text-meta`, `--text-pill`, `--text-mem`, `--text-axis` (now
`10.5px`, see §2.2).

The CTA band `h2` is `clamp(26px,3.6vw,44px)` — 2px below `--text-section` at both ends.
Fold it into `--text-section` rather than adding a token.

### 8.3 Section eyebrow — three definitions, deliberately kept apart

> ## ✅ RESOLVED — the marketing page does not align with the app
> **Decision (#M1): keep them separated.** The site is its own visual context; app and marketing
> values are not merged, and neither is "wrong" where they differ.
>
> | Where | Font | Size | Weight | Tracking | Color |
> |---|---|---|---|---|---|
> | `.eyebrow` utility (repo, live) | mono | 11px | — | `0.12em` | `--text-3` |
> | `--text-eyebrow` (§2.2, mobile `.sec-t`) | sans | 12px | 600 | `0.06em` | `--text-3` |
> | `--text-kicker` (site, **marketing only**) | mono | 11px | 500 | `0.16em` | **`--accent`** |
>
> `--text-kicker` is a marketing-only token: mono 11px/500, `0.16em`, uppercase, `--accent`. Do
> not use it in app screens, and do not retune the app eyebrows to match it. The footer column
> heading (`.fcol h5`, mono 11px/600/`0.12em`/`--text-3`) happens to match the repo `.eyebrow`
> utility exactly — reuse the utility there.

### 8.4 Color / surface extensions

| Token | Light | Dark | Role |
|---|---|---|---|
| `--line-soft` | `color-mix(in oklch, var(--line) 70%, transparent)` | same | Card hairline on steps, tiles, quotes, FAQ; softer than `--line` |
| `--surface-inset` | `var(--surface-2)` | `var(--bg)` | `.demo` panel **inside** a feature tile — inverts direction per theme |
| `--chrome-bg` | `color-mix(in oklch, var(--bg) 78%, transparent)` | same | Sticky header fill under blur |
| `--chrome-line` | `color-mix(in oklch, var(--line) 60%, transparent)` | same | Header bottom border |

> ## ✅ RESOLVED — chrome blur is not unified
> **Decision (#M1, same rule):** the site header keeps `blur(22px) saturate(160%)` over
> `--chrome-bg` (`--bg 78%`); the app bottom nav keeps `--blur-nav` (`blur(24px)` over `82%`,
> §4.4). Two surfaces, two contexts, no merge.

`--surface-inset` is the one genuinely new *semantic* surface: in dark it steps **down** to `--bg`
inside a `--surface` card, in light it steps **up** to `--surface-2`. Encode it per theme; do not
resolve it to a single value.

### 8.5 Glows and shadows (the app surface is flat; marketing is not)

| Token | Value | Use |
|---|---|---|
| `--glow-hero` | `radial-gradient(ellipse at center, color-mix(in oklch, var(--accent) 26%, transparent), transparent 68%)` | `1100×620` ellipse, `top:-280px`, centered, `filter: blur(20px)`, behind the hero |
| `--glow-band` | `radial-gradient(ellipse at center, color-mix(in oklch, var(--accent) 40%, transparent), transparent 70%)` | `240px` tall, bleeding off the CTA band's bottom edge |
| `--shadow-plan-hi` | `0 30px 70px -30px color-mix(in oklch, var(--accent) 55%, transparent)` | Highlighted pricing plan |
| `--shadow-float` | `0 24px 60px -18px rgba(0,0,0,.5)` | Floating stat cards beside the device |
| `--shadow-device` | `0 50px 110px -30px rgba(0,0,0,.7), 0 0 0 1px var(--line), 0 0 0 10px color-mix(in oklch, var(--surface) 70%, transparent)` | Device frame (drop + hairline + bezel ring) |

Both glows are `position:absolute` + `pointer-events:none` backgrounds, not `box-shadow`.
Their opacity is tuned for dark; **verify in light** (`accent` at 26%/40% over `--bg 0.975`).

### 8.6 Device frame (the phone mock is now product UI, not demo chrome)

§1.4 called the phone frame demo-only chrome. In the marketing hero it is a **shipped element**,
so its geometry becomes tokens:

| Token | Value | Note |
|---|---|---|
| `--device-w` | `330px` | `min(300px, 86vw)` below 520px |
| `--radius-device` | `40px` | Outer frame |
| `--radius-device-inner` | `31px` | Screen, with `10px` frame padding and a `1px --line` inner border |
| `--float-w` | `158px` | Floating stat card, overlapping the frame by `18px` |

> ## ✅ RESOLVED — the screen is a screenshot (#M6)
> The app fragments drawn inside the frame are scaled down from their real sizes (balance `38px`
> vs `52px`, In/Out `17px` vs `22px`, tx glyph `34px/r12` vs `42px/r14`, FAB `42px` vs `52px`,
> nav label `9.5px` vs `10.5px`). **Decision: do not rebuild them.** The screen is a static
> **image**, one per theme; only the frame, the status bar and the floating cards stay as DOM, so
> they keep using `--line`, `--surface` and `--shadow-*`. See `COMPONENT_INVENTORY.md §H9`.

### 8.7 Marketing spacing (extends §3 — still Tailwind steps where possible)

| Token | Value | Use |
|---|---|---|
| `--space-section` | `clamp(56px,7vw,104px)` | `.sec` vertical padding |
| `--space-hero-top` | `clamp(48px,7vw,92px)` | Hero top |
| `--space-hero-bottom` | `clamp(56px,7vw,96px)` | Hero bottom, and footer `margin-top` |
| `--space-band` | `clamp(32px,5vw,58px)` | CTA band padding |
| `--gap-grid` | `14px` (`gap-3.5`) | Steps, bento, quotes |
| `--gap-grid-lg` | `16px` (`gap-4`) | Pricing |

Fixed steps that map straight onto Tailwind: section-head `margin-bottom: 44px` (`mb-11`),
hero-grid gap `clamp(32px,5vw,72px)`, hero proof `gap:26px` / `margin-top:38px` /
`padding-top:26px`, footer `52px 0 40px` with a `32px` column gap and `36px`/`22px` rules.

### 8.8 Motion (extends §4.3)

| Token | Value | Use |
|---|---|---|
| `--transition-reveal` | `opacity .7s ease, transform .7s ease` | `.rv` scroll reveal: from `opacity:0; translateY(14px)` |
| `--reveal-stagger` | `70ms × (index mod 3)` | Per-element delay |
| `--transition-tile` | `border-color .25s, transform .25s` | Feature tile hover: `translateY(-2px)` + border → `color-mix(accent 45%)` |

The source also sets `html { scroll-behavior: smooth }` for its anchor nav.
**Required addition:** the source has no `prefers-reduced-motion` guard. The implementation must
render revealed content immediately and drop the smooth scroll under that query.

### 8.9 What the site does *not* introduce

No new hues, no new font family, no change to `--c1..--c6`, no new radius primitive apart from the
device frame, and no new categorical or status semantics. The `@theme inline` block gains only the
names in 8.1–8.8.

---

## Residual notes (not open questions — accepted work)

- **Marketing now has a design** — `Tallypal Site.html`, extracted in §8 and inventoried in
  `COMPONENT_INVENTORY.md §H`. **Auth and admin** still have no mock and are re-skinned onto
  these tokens in their own batches.
- **Stats** screen introduces new tokens usage but no new tokens.
