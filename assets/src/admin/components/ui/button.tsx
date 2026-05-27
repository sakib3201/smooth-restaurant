import { Button as ButtonPrimitive } from "@base-ui/react/button"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/admin/lib/utils"

const buttonVariants = cva(
  "group/button sr-inline-flex sr-shrink-0 sr-items-center sr-justify-center sr-rounded-lg sr-border sr-border-transparent sr-bg-clip-padding sr-text-sm sr-font-medium sr-whitespace-nowrap sr-transition-all sr-outline-none sr-select-none focus-visible:sr-border-ring focus-visible:sr-ring-3 focus-visible:sr-ring-ring/50 active:not-aria-[haspopup]:sr-translate-y-px disabled:sr-pointer-events-none disabled:sr-opacity-50 aria-invalid:sr-border-destructive aria-invalid:sr-ring-3 aria-invalid:sr-ring-destructive/20 dark:aria-invalid:sr-border-destructive/50 dark:aria-invalid:sr-ring-destructive/40 [&_svg]:sr-pointer-events-none [&_svg]:sr-shrink-0 [&_svg:not([class*='size-'])]:sr-size-4",
  {
    variants: {
      variant: {
        default: "sr-bg-primary sr-text-primary-foreground [a]:hover:sr-bg-primary/80",
        outline:
          "sr-border-border sr-bg-background hover:sr-bg-muted hover:sr-text-foreground aria-expanded:sr-bg-muted aria-expanded:sr-text-foreground dark:sr-border-input dark:sr-bg-input/30 dark:hover:sr-bg-input/50",
        secondary:
          "sr-bg-secondary sr-text-secondary-foreground hover:sr-bg-secondary/80 aria-expanded:sr-bg-secondary aria-expanded:sr-text-secondary-foreground",
        ghost:
          "hover:sr-bg-muted hover:sr-text-foreground aria-expanded:sr-bg-muted aria-expanded:sr-text-foreground dark:hover:sr-bg-muted/50",
        destructive:
          "sr-bg-destructive/10 sr-text-destructive hover:sr-bg-destructive/20 focus-visible:sr-border-destructive/40 focus-visible:sr-ring-destructive/20 dark:sr-bg-destructive/20 dark:hover:sr-bg-destructive/30 dark:focus-visible:sr-ring-destructive/40",
        link: "sr-text-primary sr-underline-offset-4 hover:sr-underline",
      },
      size: {
        default:
          "sr-h-8 sr-gap-1.5 sr-px-2.5 has-data-[icon=inline-end]:sr-pr-2 has-data-[icon=inline-start]:sr-pl-2",
        xs: "sr-h-6 sr-gap-1 sr-rounded-[min(var(--radius-md),10px)] sr-px-2 sr-text-xs in-data-[slot=button-group]:sr-rounded-lg has-data-[icon=inline-end]:sr-pr-1.5 has-data-[icon=inline-start]:sr-pl-1.5 [&_svg:not([class*='size-'])]:sr-size-3",
        sm: "sr-h-7 sr-gap-1 sr-rounded-[min(var(--radius-md),12px)] sr-px-2.5 sr-text-[0.8rem] in-data-[slot=button-group]:sr-rounded-lg has-data-[icon=inline-end]:sr-pr-1.5 has-data-[icon=inline-start]:sr-pl-1.5 [&_svg:not([class*='size-'])]:sr-size-3.5",
        lg: "sr-h-9 sr-gap-1.5 sr-px-2.5 has-data-[icon=inline-end]:sr-pr-2 has-data-[icon=inline-start]:sr-pl-2",
        icon: "sr-size-8",
        "icon-xs":
          "sr-size-6 sr-rounded-[min(var(--radius-md),10px)] in-data-[slot=button-group]:sr-rounded-lg [&_svg:not([class*='size-'])]:sr-size-3",
        "icon-sm":
          "sr-size-7 sr-rounded-[min(var(--radius-md),12px)] in-data-[slot=button-group]:sr-rounded-lg",
        "icon-lg": "sr-size-9",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

function Button({
  className,
  variant = "default",
  size = "default",
  ...props
}: ButtonPrimitive.Props & VariantProps<typeof buttonVariants>) {
  return (
    <ButtonPrimitive
      data-slot="button"
      className={cn(buttonVariants({ variant, size, className }))}
      {...props}
    />
  )
}

export { Button, buttonVariants }
