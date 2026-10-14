import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
  {
    variants: {
      variant: {
        default:
          "bg-primary text-primary-foreground hover:bg-primary/90",
        destructive:
          "bg-destructive text-white hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60",
        outline:
          "border bg-transparent shadow-xs hover:bg-surface-2 hover:text-text",
        secondary:
          "bg-secondary text-secondary-foreground hover:bg-secondary/80",
        ghost:
          "hover:bg-surface-2 hover:text-text",
        link: "text-primary underline-offset-4 hover:underline",
        // Marketing `.btn-g` — surface bg + line border (distinct from `outline`'s transparent fill).
        surface:
          "border border-line bg-surface text-text hover:bg-surface-2",
      },
      size: {
        "default": "h-9 px-4 py-2 has-[>svg]:px-3",
        "sm": "h-8 gap-1.5 px-3 has-[>svg]:px-2.5",
        "lg": "h-10 px-6 has-[>svg]:px-4",
        "touch": "h-12 px-6 text-[14.5px] font-semibold tracking-[-0.01em]",
        "icon": "size-9",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
        // Marketing `.btn-lg` — hero + CTA-band buttons.
        "pill-lg": "px-[26px] py-[15px] text-btn-lg font-semibold tracking-[-0.01em]",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
