import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Badge } from "./Badge.vue"

export const badgeVariants = cva(
  "inline-flex items-center gap-1 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-ring/40 focus:ring-offset-1 focus:ring-offset-background",
  {
    variants: {
      variant: {
        default:
          "border border-transparent bg-primary text-primary-foreground px-2.5 py-0.5 text-[10px] font-mono font-medium uppercase tracking-[0.12em]",
        secondary:
          "border border-transparent bg-secondary text-secondary-foreground px-2.5 py-0.5 text-[10px] font-mono font-medium uppercase tracking-[0.12em]",
        destructive:
          "border border-transparent bg-destructive text-destructive-foreground px-2.5 py-0.5 text-[10px] font-mono font-medium uppercase tracking-[0.12em]",
        outline:
          "border border-line bg-transparent text-text-3 px-2.5 py-0.5 text-[10px] font-mono font-medium uppercase tracking-[0.12em]",
        brand:
          "border border-primary bg-transparent text-primary px-2.5 py-0.5 text-[10px] font-mono font-medium uppercase tracking-[0.12em]",
        // Mobile `.pill` — neutral chip.
        pill:
          "bg-surface-2 text-text-3 px-[7px] py-0.5 text-[10px] font-semibold tracking-[0.03em]",
        // Mobile `.pill.sh` — accent (shared) chip.
        accent:
          "bg-[color-mix(in_oklch,var(--accent)_20%,transparent)] text-accent px-[7px] py-0.5 text-[10px] font-semibold tracking-[0.03em]",
        // Desktop `.pill.ok` — positive chip (owed to you).
        ok:
          "bg-[color-mix(in_oklch,var(--pos)_16%,transparent)] text-pos px-[7px] py-0.5 text-[10px] font-semibold tracking-[0.03em]",
        // Mobile `.delta` — positive trend badge.
        pos:
          "bg-[color-mix(in_oklch,var(--pos)_16%,transparent)] text-pos gap-1.5 px-[11px] py-[5px] text-[13px] font-medium",
        // Negative trend badge.
        neg:
          "bg-[color-mix(in_oklch,var(--neg)_16%,transparent)] text-neg gap-1.5 px-[11px] py-[5px] text-[13px] font-medium",
        // Marketing `.badge` — hero status pill (leading dot supplied by the consumer, e.g. `Dot`).
        status:
          "border border-line bg-surface text-text-2 gap-[9px] py-1.5 pr-[14px] pl-2 text-badge font-medium",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
)

export type BadgeVariants = VariantProps<typeof badgeVariants>
